<?php

namespace App\Support;

use App\NativeComponents\Concerns\HandlesCalendar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class LiturgicalCalendarService
{
    use HandlesCalendar;
    public function __construct(
        protected CatholicReadingsClient $client = new CatholicReadingsClient()
    ) {}

    /** One date, cache-first. Fetches and caches on a miss. */
    public function getDay(string $date): ?object
    {
        $cached = DB::table('daily_calendar')->where('date', $date)->first();
        if ($cached !== null) {
            return $cached;
        }

        return $this->fetchAndCache($date);
    }

    /** Force a fresh fetch for one date, bypassing the cache. Used by pull-to-refresh. */
    public function refreshDay(string $date): ?object
    {
        return $this->fetchAndCache($date);
    }

    /**
     * The whole visible month, batch-fetched -- only days not already
     * cached actually hit the network.
     */
    public function getMonth(int $year, int $month): Collection
    {
        $dates = $this->datesInMonth($year, $month);

        $cached = DB::table('daily_calendar')
            ->whereIn('date', $dates)
            ->get()
            ->keyBy('date');

        $result = collect();

        foreach ($dates as $date) {
            if ($cached->has($date)) {
                $result->put($date, $cached->get($date));
                continue;
            }

            $fetched = $this->fetchAndCache($date);
            if ($fetched !== null) {
                $result->put($date, $fetched);
            }
        }

        return $result;
    }

    /** Force a fresh fetch for every day in the month, bypassing the cache. */
    public function refreshMonth(int $year, int $month): Collection
    {
        $result = collect();

        foreach ($this->datesInMonth($year, $month) as $date) {
            $fetched = $this->fetchAndCache($date);
            if ($fetched !== null) {
                $result->put($date, $fetched);
            }
        }

        return $result;
    }

    protected function datesInMonth(int $year, int $month): Collection
    {
        //$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $daysInMonth = LiturgicalCalendarService::daysInMonth($year, $month);

        return collect(range(1, $daysInMonth))
            ->map(fn($d) => sprintf('%04d-%02d-%02d', $year, $month, $d));
    }

    protected function fetchAndCache(string $date): ?object
    {
        $readings = $this->client->fetchReadings($date);
        $celebration = $this->client->fetchCelebration($date);

        if ($readings === null && $celebration === null) {
            return null;
        }

        $row = [
            'date' => $date,
            'season' => $readings['season'] ?? $celebration['season'] ?? null,
            'sub_season' => $readings['subSeason'] ?? $celebration['subSeason'] ?? null,
            'liturgical_note' => $readings['liturgicalNote'] ?? $celebration['liturgicalNote'] ?? null,
            'historical_note' => $readings['historicalNote'] ?? $celebration['historicalNote'] ?? null,
            'first_reading' => $readings['readings']['firstReading'] ?? null,
            'psalm' => $readings['readings']['psalm'] ?? null,
            'second_reading' => $readings['readings']['secondReading'] ?? null,
            'gospel' => $readings['readings']['gospel'] ?? null,
            'celebration_name' => $celebration['celebration']['name'] ?? null,
            'celebration_type' => $celebration['celebration']['type'] ?? null,
            'celebration_quote' => $celebration['celebration']['quote'] ?? null,
            'celebration_description' => $celebration['celebration']['description'] ?? null,
            'celebration_image' => $celebration['celebration']['image'] ?? null,
            'fetched_at' => now(),
        ];

        DB::table('daily_calendar')->updateOrInsert(['date' => $date], $row);

        return (object) $row;
    }

    /**
     * Shared season/rank -> liturgical theme token, used by both Today
     * and Calendar Month so the color logic lives in one place.
     * The martyr-red heuristic is a best-effort text match against the
     * description, since the API doesn't give us authoritative rank
     * data for "red" days -- flagged as approximate, not authoritative.
     */
    public static function colorToken(?object $day): ?string
    {
        if (! $day || ! $day->season) {
            return null;
        }

        $text = strtolower(($day->celebration_name ?? '') . ' ' . ($day->celebration_description ?? ''));
        $isSolemnityOrFeast = in_array($day->celebration_type ?? null, ['SOLEMNITY', 'FEAST'], true);

        if ($isSolemnityOrFeast && str_contains($text, 'martyr')) {
            return 'liturgical-red';
        }

        if (str_contains($day->season, 'Gaudete') || str_contains($day->season, 'Laetare')) {
            return 'liturgical-rose';
        }

        if ($day->celebration_type === 'SOLEMNITY') {
            return 'liturgical-white';
        }

        return match (true) {
            str_contains($day->season, 'Advent') => 'liturgical-violet',
            str_contains($day->season, 'Lent') => 'liturgical-violet',
            str_contains($day->season, 'Christmas') => 'liturgical-white',
            str_contains($day->season, 'Easter') => 'liturgical-white',
            default => 'liturgical-green',
        };
    }
}
