<?php

// Both visual directions, real native-ui token names, now including
// liturgical-* colors alongside the core 8 pairs. Swapped in at runtime
// via Theme::merge() -- see app/Support/AppTheme.php.

return [

    'me_time' => [
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
        'liturgical-green' => '#3f6b4a',
        'liturgical-violet' => '#5b3a6b',
        'liturgical-white' => '#c9a227',
        'liturgical-red' => '#a13d2e',
        'liturgical-rose' => '#c98ba0',
    ],

    'marian' => [
        'primary' => '#0e2a47',
        'on-primary' => '#ffffff',
        'secondary' => '#7e5700',
        'on-secondary' => '#ffffff',
        'surface' => '#f0eee9',
        'on-surface' => '#1b1c19',
        'surface-variant' => '#eae8e3',
        'on-surface-variant' => '#43474d',
        'background' => '#fbf9f4',
        'on-background' => '#1b1c19',
        'outline' => '#74777e',
        'accent' => '#c8963e',
        'on-accent' => '#ffffff',
        'destructive' => '#ba1a1a',
        'on-destructive' => '#ffffff',
        // liturgical-red reuses DESIGN.md's own "Rubric Crimson" tertiary value.
        'liturgical-green' => '#3f6b4a',
        'liturgical-violet' => '#4a3a6b',
        'liturgical-white' => '#c8963e',
        'liturgical-red' => '#7a2021',
        'liturgical-rose' => '#b88ea0',
    ],

];
