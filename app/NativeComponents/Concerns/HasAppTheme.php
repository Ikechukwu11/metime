<?php

namespace App\NativeComponents\Concerns;

use App\Support\AppTheme;

trait HasAppTheme
{
  public function mountHasAppTheme(): void
  {
    AppTheme::bootApply();
  }
}
