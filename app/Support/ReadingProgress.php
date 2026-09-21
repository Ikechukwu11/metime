<?php

namespace App\Support;

class ReadingProgress
{
    public static function record(string $translation, string $bookSlug, string $bookName, int $chapter): void
    {
        AppSettings::set('last_read', json_encode([
            'translation' => strtoupper($translation),
            'book_slug' => $bookSlug,
            'book_name' => $bookName,
            'chapter' => $chapter,
        ]));
    }

    /** Returns null if the user has never opened a chapter yet. */
    public static function current(): ?array
    {
        $raw = AppSettings::get('last_read');

        if ($raw === null) {
            return null;
        }

        $data = json_decode($raw, true);

        return is_array($data) ? $data : null;
    }
}
