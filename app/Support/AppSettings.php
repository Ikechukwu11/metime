<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class AppSettings
{
    public static function get(string $key, $default = null)
    {
        $value = DB::table('settings')->where('key', $key)->value('value');

        return $value ?? $default;
    }

    public static function set(string $key, $value): void
    {
        DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $value]);
    }
}
