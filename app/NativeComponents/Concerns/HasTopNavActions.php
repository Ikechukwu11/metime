<?php

namespace App\NativeComponents\Concerns;

// TabsLayout's NavBar wires its search/settings icons via
// NavAction::press('openSearch' / 'openSettings') — press() dispatches
// against the current screen, not the layout, so every screen that
// uses TabsLayout needs these two methods. Pull in via `use`.
trait HasTopNavActions
{
  public function openSearch(): void
  {
    $this->navigate('/search');
  }

  public function openSettings(): void
  {
    $this->navigate('/settings');
  }
}
