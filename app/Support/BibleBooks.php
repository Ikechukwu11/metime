<?php

namespace App\Support;

class BibleBooks
{
    public static function idForSlug(string $slug): ?int
    {
        foreach (config('bible-books') as $id => $entry) {
            if ($entry['slug'] === $slug) {
                return $id;
            }
        }

        return null;
    }

    public static function slugForId(int $id): ?string
    {
        return config("bible-books.{$id}.slug");
    }

    public static function nameForId(int $id): ?string
    {
        return config("bible-books.{$id}.name");
    }
}
