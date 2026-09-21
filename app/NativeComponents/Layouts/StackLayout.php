<?php

namespace App\NativeComponents\Layouts;

use Native\Mobile\Edge\Layouts\Builders\NavBar;
use Native\Mobile\Edge\Layouts\NativeLayout;
use Native\Mobile\Edge\NativeComponent;

class StackLayout extends NativeLayout
{
    public function navBar(NativeComponent $screen): ?NavBar
    {
        $bg = theme('primary') ?? $screen->param('navBarBackgroundColor') ?? '#ffffff';

        return NavBar::make()
            ->title($screen->navTitle())
            ->backgroundColor($bg)
            ->textColor('#ffffff')
            ->back();
    }

    // No tabBar() override — returning null (the default) means this
    // layout renders no bottom nav, which is exactly what a pushed
    // detail screen (Bible Reader, Calendar Day, Settings) wants.
}
