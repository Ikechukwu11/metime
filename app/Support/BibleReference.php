<?php

namespace App\Support;

// Parses references like the Catholic Readings API gives us -- "Luke
// 14:25-33", "Wisdom 9:13-18b", "Philemon 9-10, 12-17" -- into a book
// slug + chapter number for linking into the Bible reader.
//
// Deliberately book+chapter only for this pass, not exact verse ranges
// (the reader has no verse-highlighting UI yet to make that worthwhile).
// The raw reference string should always be displayed regardless of
// whether this succeeds -- treat a null return as "show the text, don't
// make it tappable" rather than an error.
class BibleReference
{
    protected const ONE_CHAPTER_BOOKS = [
        'obadiah', 'philemon', '2-john', '3-john', 'jude',
    ];

    protected const ALIASES = [
        'psalm' => 'psalms',
        'song of songs' => 'song of solomon',
        'canticle of canticles' => 'song of solomon',
        'apocalypse' => 'revelation',
    ];

    public static function parse(string $reference): ?array
    {
        $ref = trim($reference);

        // "1 Corinthians 12:31" / "Wisdom 9:13" / "Philemon 9" (no colon
        // for one-chapter books, where the number is a verse not a chapter).
        if (! preg_match('/^((?:[1-3]\s)?[A-Za-z ]+?)\s+(\d+)(?::(\d+))?/', $ref, $m)) {
            return null;
        }

        $bookKey = strtolower(trim($m[1]));
        $bookKey = self::ALIASES[$bookKey] ?? $bookKey;
        $slug = \Illuminate\Support\Str::slug($bookKey);

        $bookId = BibleBooks::idForSlug($slug);
        if ($bookId === null) {
            return null;
        }

        $hasColon = isset($m[3]);
        $chapter = $hasColon
            ? (int) $m[2]
            : (in_array($slug, self::ONE_CHAPTER_BOOKS, true) ? 1 : (int) $m[2]);

        return [
            'book_slug' => $slug,
            'book_id' => $bookId,
            'chapter' => $chapter,
        ];
    }
}
