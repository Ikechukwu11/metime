<?php

namespace App\NativeComponents;

use App\NativeComponents\Concerns\HandlesCalendar;
use App\NativeComponents\Concerns\HasTopNavActions;
use App\Support\LiturgicalCalendarService;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Native\Mobile\Attributes\Lazy;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\AsyncTask;
use Native\Mobile\Facades\Network;

#[Lazy]
class CalendarMonth extends NativeComponent
{
    use HasTopNavActions;
    use HandlesCalendar;

    public int $year;
    public int $month;
    public bool $online = true;

    // True while a background backfill for the current month is in
    // flight -- lets the view show a subtle "still loading some days"
    // hint instead of silently popping dots in with no explanation.
    public bool $loadingMore = false;

    protected Collection $days;

    public function mount(): void
    {
        $this->year = (int) now()->format('Y');
        $this->month = (int) now()->format('n');
        $this->online = $this->checkOnline();
        $this->loadMonth();
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

    /**
     * Cache-first, instant: paints whatever's already cached immediately
     * (no network, no blocking), then backfills any missing days for the
     * visible month via a background async task so the UI thread is
     * never waiting on the ~62-request worst case a cold month used to
     * mean. Falls back to a synchronous pooled fetch only if the async
     * pool itself is unavailable.
     */
    protected function loadMonth(): void
    {
        $service = new LiturgicalCalendarService();

        $this->days = $service->getCachedMonth($this->year, $this->month);
        $missing = $service->missingDatesInMonth($this->year, $this->month);

        if (empty($missing)) {
            return;
        }

        $this->loadingMore = true;

        $task = AsyncTask::dispatch(static function () use ($missing) {
            return LiturgicalCalendarService::fetchMany($missing);
        });

        /*
        if ($task === false) {
            // Background pool unavailable -- fall back rather than leave
            // the screen permanently missing data for this month.
            $fetched = LiturgicalCalendarService::fetchMany($missing);
            LiturgicalCalendarService::persist($fetched);
            $this->mergeFetched($fetched);
            $this->loadingMore = false;

            return;
        }*/

        $task->finished(function ($result) {
            LiturgicalCalendarService::persist($result);
            $this->mergeFetched($result);
            $this->loadingMore = false;
        })
            ->failed(function () {
                $this->loadingMore = false;
            });
    }

    protected function mergeFetched(array $rows): void
    {
        foreach ($rows as $date => $row) {
            $this->days->put($date, (object) $row);
        }
    }

    public function render(): View
    {
        $daysInMonth = self::daysInMonth($this->year, $this->month);
        $today = now()->toDateString();

        $grid = collect(range(1, $daysInMonth))->map(function ($d) use ($today) {
            $date = sprintf('%04d-%02d-%02d', $this->year, $this->month, $d);
            $row = $this->days->get($date);

            return [
                'day' => $d,
                'date' => $date,
                'is_today' => $date === $today,
                'color' => LiturgicalCalendarService::colorToken($row),
            ];
        });

        return view('native-components.calendar-month', [
            'grid' => $grid,
            'monthLabel' => \Carbon\Carbon::create($this->year, $this->month, 1)->format('F Y'),
            'online' => $this->online,
            'loadingMore' => $this->loadingMore,
        ]);
    }

    public function refresh(): void
    {
        $this->online = $this->checkOnline();
        $this->loadingMore = true;

        // Manual pull-to-refresh: force a full re-fetch of the visible
        // month. Still dispatched async so pulling to refresh doesn't
        // freeze the screen either.
        $dates = LiturgicalCalendarService::datesInMonth($this->year, $this->month)->all();

        $task = AsyncTask::dispatch(static function () use ($dates) {
            return LiturgicalCalendarService::fetchMany($dates);
        });

        /*
        if ($task === false) {
            $fetched = LiturgicalCalendarService::fetchMany($dates);
            LiturgicalCalendarService::persist($fetched);
            $this->mergeFetched($fetched);
            $this->loadingMore = false;

            return;
        }
            */

        $task->finished(function ($result) {
            LiturgicalCalendarService::persist($result);
            $this->mergeFetched($result);
            $this->loadingMore = false;
        })
            ->failed(function () {
                $this->loadingMore = false;
            });
    }

    public function previousMonth(): void
    {
        $date = \Carbon\Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->year = $date->year;
        $this->month = $date->month;
        $this->loadMonth();
    }

    public function nextMonth(): void
    {
        $date = \Carbon\Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->year = $date->year;
        $this->month = $date->month;
        $this->loadMonth();
    }

    public function openDay(string $date): void
    {
        $this->navigate("/calendar/{$date}");
    }
}
