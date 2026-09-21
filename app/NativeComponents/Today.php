<?php

namespace App\NativeComponents;

use App\NativeComponents\Concerns\HasAppTheme;
use App\NativeComponents\Concerns\HasTopNavActions;
use App\Support\LiturgicalCalendarService;
use App\Support\ReadingProgress;
use Illuminate\View\View;
use Native\Mobile\Attributes\Lazy;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Facades\Network;

// #[Lazy]: the screen paints its chrome + placeholder() immediately,
// then mount() runs in the background and the real content swaps in
// once it resolves -- rather than the whole screen waiting on the
// network call before anything appears at all.
#[Lazy]
class Today extends NativeComponent
{
    use HasTopNavActions;
    use HasAppTheme;

    public ?string $date = '';
    public ?object $day = null;
    public bool $online = true;

    public function mount(): void
    {
        $this->mountHasAppTheme();
        $this->date = now()->toDateString();
        $this->online = $this->checkOnline();
        $this->day = (new LiturgicalCalendarService())->getDay($this->date);
    }

    protected function checkOnline(): bool
    {
        try {
            return Network::status()->connected ?? true;
        } catch (\Throwable) {
            // Network plugin not installed, or the call failed for some
            // other reason -- fail open rather than showing a false
            // "you're offline" warning.
            return true;
        }
    }

    protected function placeholder(): View
    {
        return view('native-components.today-skeleton');
    }

    public function navTitle(): string
    {
        return 'Today';
    }

    public function refresh(): void
    {
        $this->online = $this->checkOnline();
        $this->day = (new LiturgicalCalendarService())->refreshDay($this->date);
    }

    public function render(): View
    {
        return view('native-components.today', [
            'date' => $this->date,
            'day' => $this->day,
            'online' => $this->online,
            'color' => LiturgicalCalendarService::colorToken($this->day),
            'progress' => ReadingProgress::current(),
        ]);
    }

    public function openTodaysReadings(): void
    {
        $this->navigate('/calendar/' . $this->date);
    }

    public function resumeReading(): void
    {
        $progress = ReadingProgress::current();

        $book = $progress['book_slug'] ?? 'john';
        $chapter = $progress['chapter'] ?? 1;

        $this->navigate("/bible/{$book}/{$chapter}");
    }
}
