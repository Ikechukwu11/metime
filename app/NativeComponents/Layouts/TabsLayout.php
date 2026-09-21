<?php

namespace App\NativeComponents\Layouts;

use Native\Mobile\Edge\Layouts\Builders\NavAction;
use Native\Mobile\Edge\Layouts\Builders\NavBar;
use Native\Mobile\Edge\Layouts\Builders\Tab;
use Native\Mobile\Edge\Layouts\Builders\TabBar;
use Native\Mobile\Edge\Layouts\NativeLayout;
use Native\Mobile\Edge\NativeComponent;

class TabsLayout extends NativeLayout
{
    public function navBar(NativeComponent $screen): ?NavBar
    {
        $bg = theme('primary') ?? $screen->param('navBarBackgroundColor') ?? '#ffffff';
        return NavBar::make()
            ->title($screen->navTitle())
            ->backgroundColor($bg)
            ->textColor('#ffffff')
            ->action(
                NavAction::make('search')
                    ->icon('search')
                    ->a11yLabel('Search Scripture')
                    ->press('openSearch')
            )
            ->action(
                NavAction::make('settings')
                    ->icon('settings')
                    ->a11yLabel('Settings')
                    ->press('openSettings')
            );
    }

    public function tabBar(NativeComponent $screen): ?TabBar
    {
        return TabBar::make()
            ->add(Tab::link('Today', '/', icon: 'wb_sunny'))
            ->add(Tab::link('Bible', '/bible', icon: 'menu_book'))
            ->add(Tab::link('Calendar', '/calendar', icon: 'calendar_month'));
    }
}
