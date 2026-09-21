<?php

namespace App\Support;

use Native\Mobile\UI\Theme;
//use Nativephp\NativeUi\Themed;

class AppTheme
{
    public static function palettes(): array
    {
        return config('themes');
    }

    public static function current(): string
    {
        return AppSettings::get('theme', 'me_time');
    }

    public static function apply(string $key): void
    {
        $palette = config("themes.{$key}");

        if ($palette === null) {
            return;
        }

        // Deep-merges over config/native-ui.php -- swapping the whole
        // 'light' block is how we repurpose a light/dark-oriented API to
        // do full named-palette switching instead.
        Theme::merge([
            'light' => $palette,
            'dark' => $palette,
        ]);
    }

    public static function set(string $key): void
    {
        if (!array_key_exists($key, static::palettes())) {
            return;
        }

        AppSettings::set('theme', $key);

        static::apply($key);
    }

    /** Call once at boot (see AppServiceProvider) to restore the user's choice. */
    public static function bootApply(): void
    {
        static::apply(static::current());
    }
}
