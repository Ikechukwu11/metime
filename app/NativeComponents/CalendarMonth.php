<?php

namespace App\NativeComponents;

use App\NativeComponents\Concerns\HandlesCalendar;
use App\NativeComponents\Concerns\HasTopNavActions;
use App\Support\LiturgicalCalendarService;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Native\Mobile\Attributes\Lazy;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Facades\Network;

#[Lazy]
class CalendarMonth extends NativeComponent
{
    use HasTopNavActions;
    use HandlesCalendar;

    public int $year = 0;

    public int $month = 0;

    public bool $online = true;

    protected Collection $days;

    public function mount(): void
    {
        $this->year = (int) now()->format('Y');
        $this->month = (int) now()->format('n');

        $this->days = collect();

        $this->online = $this->checkOnline();

        $this->days = (new LiturgicalCalendarService())
            ->getMonth($this->year, $this->month);
    }

    protected function checkOnline(): bool
    {
        try {
            return Network::status()->connected ?? true;
        } catch (\Throwable) {
            return true;
        }
    }

    protected function placeholder(): View
    {
        return view('native-components.calendar-month-skeleton');
    }

    public function navTitle(): string
    {
        return 'Calendar';
    }

    public function refresh(): void
    {
        $this->online = $this->checkOnline();

        $this->days = (new LiturgicalCalendarService())
            ->refreshMonth($this->year, $this->month);
    }

    public function render(): View
    {
        //$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $this->month, $this->year);
        $daysInMonth = self::daysInMonth($this->year, $this->month);
        $today = now()->toDateString();

        $grid = collect(range(1, $daysInMonth))->map(
            function ($d) use ($today) {
                $date = sprintf(
                    '%04d-%02d-%02d',
                    $this->year,
                    $this->month,
                    $d
                );

                $row = $this->days->get($date);

                return [
                    'day' => $d,
                    'date' => $date,
                    'is_today' => $date === $today,
                    'color' => LiturgicalCalendarService::colorToken($row),
                ];
            }
        );

        return view('native-components.calendar-month', [
            'grid' => $grid,
            'monthLabel' => \Carbon\Carbon::create(
                $this->year,
                $this->month,
                1
            )->format('F Y'),
            'online' => $this->online,
        ]);
    }

    public function previousMonth(): void
    {
        $date = \Carbon\Carbon::create(
            $this->year,
            $this->month,
            1
        )->subMonth();

        $this->year = $date->year;
        $this->month = $date->month;

        $this->days = (new LiturgicalCalendarService())
            ->getMonth($this->year, $this->month);
    }

    public function nextMonth(): void
    {
        $date = \Carbon\Carbon::create(
            $this->year,
            $this->month,
            1
        )->addMonth();

        $this->year = $date->year;
        $this->month = $date->month;

        $this->days = (new LiturgicalCalendarService())
            ->getMonth($this->year, $this->month);
    }

    public function openDay(string $date): void
    {
        $this->navigate("/calendar/{$date}");
    }
}
