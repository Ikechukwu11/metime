<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class BibleSearch
{
    /**
     * Search every given translation's FTS5 index and return a flat,
     * merged result list. Each translation is queried on its own
     * connection/file — there's no cross-file JOIN in SQLite, so this
     * is N small queries rather than one big one.
     */
    public static function across(string $query, array $codes, int $perTranslation = 5): array
    {
        $query = trim($query);

        if (mb_strlen($query) < 3) {
            return [];
        }

        // Treat the whole input as one phrase-ish literal, rather than
        // passing it raw into FTS5's own query syntax, so stray
        // punctuation in what someone types can't throw a syntax error.
        $match = '"'.str_replace('"', '""', $query).'"';

        $results = [];

        foreach ($codes as $code) {
            $connection = BibleConnection::register($code);
            if ($connection === null) {
                continue;
            }

            try {
                $rows = DB::connection($connection)->select(
                    "SELECT verses.book_id, verses.chapter, verses.verse,
                            snippet(verses_fts, 0, '', '', '…', 10) AS snippet
                     FROM verses_fts
                     JOIN verses ON verses.id = verses_fts.rowid
                     WHERE verses_fts MATCH ?
                     LIMIT ?",
                    [$match, $perTranslation]
                );
            } catch (\Throwable) {
                // Malformed FTS query for this input -- skip this
                // translation rather than failing the whole search.
                continue;
            }

            foreach ($rows as $row) {
                $results[] = [
                    'translation' => strtoupper($code),
                    'book_id' => $row->book_id,
                    'book_name' => BibleBooks::nameForId($row->book_id) ?? "Book {$row->book_id}",
                    'book_slug' => BibleBooks::slugForId($row->book_id) ?? (string) $row->book_id,
                    'chapter' => $row->chapter,
                    'verse' => $row->verse,
                    'snippet' => $row->snippet,
                ];
            }
        }

        return $results;
    }
}
