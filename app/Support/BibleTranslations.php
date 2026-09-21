<?php

namespace App\Support;

use Illuminate\Support\Facades\File;

class BibleTranslations
{
    protected static ?array $index = null;

    protected static function index(): array
    {
        if (static::$index === null) {
            $rows = json_decode(File::get(resource_path('bible-translations.json')), true) ?? [];
            static::$index = collect($rows)->keyBy('code')->all();
        }

        return static::$index;
    }

    public static function fullName(string $code): string
    {
        $code = strtoupper($code);

        return static::index()[$code]['full_name'] ?? $code;
    }
}
