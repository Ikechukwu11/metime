<?php

namespace App\NativeComponents;

use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class Onboarding extends NativeComponent
{
    // No layout on this route, so no chrome to title — full-bleed by design.

    public function render(): View
    {
        return view('native-components.onboarding');
    }

    public function finish(): void
    {
        // Screens-and-nav pass only: jumps straight to Today.
        // Actual translation-selection persistence comes later.
        $this->navigate('/today');
    }
}
