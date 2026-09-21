<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BibleConnection
{
    /**
     * Bundled translations ship inside the app binary (resources/bibles/)
     * and survive a "Clear Data" wipe. Downloaded ones live in storage
     * and don't. Bundled wins when a translation exists in both.
     */
    public static function path(string $code): ?string
    {
        $code = strtolower($code);

        $bundled = resource_path("bibles/{$code}.sqlite");
        if (File::exists($bundled)) {
            return $bundled;
        }

        $downloaded = storage_path("bibles/{$code}.sqlite");

        return File::exists($downloaded) ? $downloaded : null;
    }

    /**
     * Register (idempotently) a connection for a translation and return
     * its connection name, or null when that translation isn't on disk.
     */
    public static function register(string $code): ?string
    {
        $code = strtolower($code);
        $name = "bible_{$code}";

        if (config("database.connections.{$name}")) {
            return $name;
        }

        $path = static::path($code);
        if ($path === null) {
            return null;
        }

        config(["database.connections.{$name}" => [
            'driver' => 'sqlite',
            'database' => $path,
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]]);

        return $name;
    }

    /** Query builder against a translation's verses table. */
    public static function verses(string $code)
    {
        $name = static::register($code);

        if ($name === null) {
            throw new \RuntimeException("Translation [{$code}] is not installed.");
        }

        return DB::connection($name)->table('verses');
    }

    /** Which translations are actually usable right now. */
    public static function available(array $codes): array
    {
        return array_values(array_filter($codes, fn ($c) => static::path($c) !== null));
    }

    /**
     * Every translation code with a bundled .sqlite in resources/bibles/,
     * lowercase, sorted. This is what powers the translation switcher and
     * the "search all translations" list — it reflects whatever is
     * actually shipped, rather than a hardcoded list that can drift out
     * of sync with the real bundle.
     */
    public static function bundledCodes(): array
    {
        if (! File::exists(resource_path('bibles'))) {
            return [];
        }

        return collect(File::files(resource_path('bibles')))
            ->filter(fn ($f) => strtolower($f->getExtension()) === 'sqlite')
            ->map(fn ($f) => strtolower($f->getFilenameWithoutExtension()))
            ->sort()
            ->values()
            ->all();
    }
}
