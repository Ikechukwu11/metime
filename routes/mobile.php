<?php

use App\NativeComponents\BibleNavigator;
use App\NativeComponents\BibleReader;
use App\NativeComponents\CalendarDay;
use App\NativeComponents\CalendarMonth;
use App\NativeComponents\Layouts\StackLayout;
use App\NativeComponents\Layouts\TabsLayout;
use App\NativeComponents\Search;
use App\NativeComponents\Settings;
use App\NativeComponents\Today;
use Illuminate\Support\Facades\Route;

// Onboarding removed -- all Bible content ships bundled, so there's
// nothing to select-and-download at first launch. App opens straight
// into the tab shell.
Route::nativeGroup(TabsLayout::class, function () {
    Route::native('/', Today::class);
    Route::native('/bible', BibleNavigator::class);
    Route::native('/calendar', CalendarMonth::class);
    Route::native('/search', Search::class);
});

Route::native('/bible/{book}/{chapter}', BibleReader::class)
    ->layout(StackLayout::class);

Route::native('/calendar/{date}', CalendarDay::class)
    ->layout(StackLayout::class);

Route::native('/settings', Settings::class)
    ->layout(StackLayout::class);
