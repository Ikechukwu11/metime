<?php

namespace App\NativeComponents;

use App\Support\AppTheme;
use App\Support\BibleConnection;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class Settings extends NativeComponent
{
    public string $theme = 'me_time';

    public function navTitle(): string
    {
        return 'Settings';
    }

    public function mount(): void
    {
        $this->theme = AppTheme::current();
    }

    public function switchTheme(string $key): void
    {
        AppTheme::set($key);
        $this->theme = $key;
    }

    public function render(): View
    {
        return view('native-components.settings', [
            'translations' => BibleConnection::bundledCodes(),
            'themeOptions' => array_keys(AppTheme::palettes()),
        ]);
    }
}
