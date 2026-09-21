<?php

namespace App\NativeComponents;

use App\Support\BibleReference;
use App\Support\LiturgicalCalendarService;
use Illuminate\View\View;
use Native\Mobile\Attributes\Lazy;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Facades\Network;

#[Lazy]
class CalendarDay extends NativeComponent
{
    public ?object $day = null;
    public array $readings = [];
    public bool $online = true;

    public function mount(): void
    {
        $this->online = $this->checkOnline();
        $this->load();
    }

    protected function checkOnline(): bool
    {
        try {
            return Network::status()->connected ?? true;
        } catch (\Throwable) {
            return true;
        }
    }

    protected function load(): void
    {
        $this->day = (new LiturgicalCalendarService())->getDay($this->param('date'));
        $this->readings = $this->parseReadings($this->day);
    }

    protected function parseReadings(?object $day): array
    {
        if (! $day) {
            return [];
        }

        $readings = [];

        foreach ([
            'First Reading' => $day->first_reading,
            'Psalm' => $day->psalm,
            'Second Reading' => $day->second_reading,
            'Gospel' => $day->gospel,
        ] as $label => $ref) {
            if (! $ref) {
                continue;
            }

            $parsed = BibleReference::parse($ref);

            $readings[] = [
                'label' => $label,
                'ref' => $ref,
                'book_slug' => $parsed['book_slug'] ?? null,
                'chapter' => $parsed['chapter'] ?? null,
            ];
        }

        return $readings;
    }

    protected function placeholder(): View
    {
        return view('native-components.calendar-day-skeleton');
    }

    public function navTitle(): string
    {
        return $this->param('date');
    }

    public function refresh(): void
    {
        $this->online = $this->checkOnline();
        $this->day = (new LiturgicalCalendarService())->refreshDay($this->param('date'));
        $this->readings = $this->parseReadings($this->day);
    }

    public function render(): View
    {
        return view('native-components.calendar-day', [
            'date' => $this->param('date'),
            'day' => $this->day,
            'readings' => $this->readings,
            'online' => $this->online,
            'color' => LiturgicalCalendarService::colorToken($this->day),
        ]);
    }

    public function openReading(string $book, int $chapter): void
    {
        $this->navigate("/bible/{$book}/{$chapter}");
    }
}
