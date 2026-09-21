<?php

// Published via: php artisan vendor:publish --tag=native-ui-config
//
// CORRECTED from the original version of this file, which invented
// Material-3-style tokens (primary-container, surface-container-low/high,
// outline-variant, etc.) that don't exist in native-ui's real theme
// system. The actual token set is flat and simple -- 8 color pairs, plus
// radius-*/font-*/font-family sitting alongside light/dark, confirmed
// against https://nativephp.com/docs/mobile/4/digging-deeper/theming.
//
// Active palette: "Me Time" (burgundy/rose), from DESIGN_variant_1.md.
// The second palette ("Marian Ivory & Ultramarine") lives in
// config/themes.php and is swapped in at runtime via Theme::merge() --
// see app/Support/AppTheme.php.

return [

    'theme' => [
        'light' => [
            'primary' => '#691426',
            'on-primary' => '#ffffff',
            'secondary' => '#7b5808',
            'on-secondary' => '#ffffff',
            'surface' => '#fbf1f2',
            'on-surface' => '#1e1b1c',
            'surface-variant' => '#efe6e7',
            'on-surface-variant' => '#554243',
            'background' => '#fff8f8',
            'on-background' => '#1e1b1c',
            'outline' => '#887273',
            'accent' => '#b88e3e',
            'on-accent' => '#ffffff',
            'destructive' => '#ba1a1a',
            'on-destructive' => '#ffffff',

            // Liturgical season/rank colors, chosen to sit harmoniously
            // against the warm burgundy/gold "Me Time" palette.
            'liturgical-green' => '#3f6b4a',
            'liturgical-violet' => '#5b3a6b',
            'liturgical-white' => '#c9a227',
            'liturgical-red' => '#a13d2e',
            'liturgical-rose' => '#c98ba0',
        ],

        // Left mostly empty -- native-ui auto-derives dark by luminance
        // inversion. Only override a token here once we've actually
        // eyeballed dark mode on a device and found one that needs tuning.
        'dark' => [
            'primary' => '#691426',
            'on-primary' => '#ffffff',
            'secondary' => '#7b5808',
            'on-secondary' => '#ffffff',
            'surface' => '#fbf1f2',
            'on-surface' => '#1e1b1c',
            'surface-variant' => '#efe6e7',
            'on-surface-variant' => '#554243',
            'background' => '#fff8f8',
            'on-background' => '#1e1b1c',
            'outline' => '#887273',
            'accent' => '#b88e3e',
            'on-accent' => '#ffffff',
            'destructive' => '#ba1a1a',
            'on-destructive' => '#ffffff',

            // Liturgical season/rank colors, chosen to sit harmoniously
            // against the warm burgundy/gold "Me Time" palette.
            'liturgical-green' => '#3f6b4a',
            'liturgical-violet' => '#5b3a6b',
            'liturgical-white' => '#c9a227',
            'liturgical-red' => '#a13d2e',
            'liturgical-rose' => '#c98ba0',
        ],

        'radius-sm' => 4,
        'radius-md' => 8,
        'radius-lg' => 16,
        'radius-full' => 999,

        'font-sm' => 14,
        'font-md' => 16,
        'font-lg' => 20,
        'font-xl' => 28,

        // 'System' resolves to San Francisco (iOS) / Roboto (Android).
        // To switch to EB Garamond: drop the font file at
        // resources/fonts/EBGaramond.ttf and set this to 'EBGaramond'
        // (confirmed convention -- the Text component's `font` prop docs
        // say "a resources/fonts/ file token or config alias").
        'font-family' => 'LibreBaskerville-Regular',
    ],

];
