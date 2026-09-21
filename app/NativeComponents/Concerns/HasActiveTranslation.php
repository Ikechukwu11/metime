<?php

namespace App\NativeComponents\Concerns;

use App\Support\AppSettings;

// Both BibleNavigator and BibleReader need the same "which translation
// is active" state and the same switch behavior, backed by the same
// settings-table key -- so they stay in sync with each other (switching
// translation in one is reflected in the other on next visit).
//
// Uses the real mount()/onResume() lifecycle hooks (confirmed against
// https://nativephp.com/docs/mobile/4/digging-deeper/lifecycle-hooks)
// instead of resyncing on every render(): mount() picks up the
// persisted choice once when the screen is first pushed, onResume()
// re-checks it when the user navigates back here after changing it
// elsewhere (e.g. switched in Navigator, then returned via back-nav).
trait HasActiveTranslation
{
    public string $activeTranslation = 'WEB';

    public function mount(): void
    {
        $this->syncActiveTranslation();
    }

    public function onResume(): void
    {
        $this->syncActiveTranslation();
    }

    protected function syncActiveTranslation(): void
    {
        $this->activeTranslation = strtoupper(
            AppSettings::get('active_translation', $this->activeTranslation)
        );
    }

    public function switchTranslation(string $code): void
    {
        $code = strtoupper($code);
        AppSettings::set('active_translation', $code);
        $this->activeTranslation = $code;
    }
}
