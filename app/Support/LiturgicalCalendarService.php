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

    /** One date, cache-first. Fetches (both endpoints concurrently) and caches on a miss. */
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
     * Cached days only, synchronous, no network -- instant. This is what
     * a screen should call for its FIRST paint; missing days are then
     * backfilled separately (via an async task, typically) rather than
     * blocking on the network here.
     */
    public function getCachedMonth(int $year, int $month): Collection
    {
        $dates = static::datesInMonth($year, $month);

        return DB::table('daily_calendar')
            ->whereIn('date', $dates)
            ->get()
            ->keyBy('date');
    }

    public function missingDatesInMonth(int $year, int $month): array
    {
        $dates = static::datesInMonth($year, $month);
        $cached = $this->getCachedMonth($year, $month)->keys();

        return $dates->diff($cached)->values()->all();
    }

    /**
     * The whole visible month, batch-fetched and blocking -- kept as a
     * synchronous fallback (e.g. if the async pool is unavailable), not
     * the primary path anymore. Prefer cache-first + async backfill from
     * the calling screen instead of calling this directly on mount().
     */
    public function getMonth(int $year, int $month): Collection
    {
        $cached = $this->getCachedMonth($year, $month);
        $missing = $this->missingDatesInMonth($year, $month);

        if (empty($missing)) {
            return $cached;
        }

        $fetched = static::fetchMany($missing);
        static::persist($fetched);

        return $cached->merge(
            collect($fetched)->map(fn($row) => (object) $row)
        );
    }

    /** Force a fresh fetch for every day in the month, bypassing the cache. */
    public function refreshMonth(int $year, int $month): Collection
    {
        $dates = static::datesInMonth($year, $month)->all();
        $fetched = static::fetchMany($dates);
        static::persist($fetched);

        return collect($fetched)->map(fn($row) => (object) $row);
    }

    protected function fetchAndCache(string $date): ?object
    {
        $raw = $this->client->fetchDay($date);

        if ($raw['readings'] === null && $raw['celebration'] === null) {
            return null;
        }

        $row = static::buildRow($date, $raw['readings'], $raw['celebration']);

        DB::table('daily_calendar')->updateOrInsert(['date' => $date], $row);

        return (object) $row;
    }

    /**
     * Pure fetch, no DB access -- safe to call from inside a static
     * async-task closure, which can't touch $this or any bound state.
     * Returns [date => row-array] for dates that resolved; a date that
     * failed entirely (both endpoints down) is simply absent.
     */
    public static function fetchMany(array $dates): array
    {
        $client = new CatholicReadingsClient();
        $raw = $client->fetchMany($dates);

        $rows = [];
        foreach ($raw as $date => $pair) {
            if ($pair['readings'] === null && $pair['celebration'] === null) {
                continue;
            }

            $rows[$date] = static::buildRow($date, $pair['readings'], $pair['celebration']);
        }

        return $rows;
    }

    /** Writes already-fetched rows to the cache. Call from the UI thread (e.g. an async task's finished() callback). */
    public static function persist(array $rows): void
    {
        foreach ($rows as $date => $row) {
            DB::table('daily_calendar')->updateOrInsert(['date' => $date], $row);
        }
    }

    protected static function buildRow(string $date, ?array $readings, ?array $celebration): array
    {
        return [
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
    }

    public static function datesInMonth(int $year, int $month): Collection
    {
        $daysInMonth = self::daysInMonth($year, $month);

        return collect(range(1, $daysInMonth))
            ->map(fn($d) => sprintf('%04d-%02d-%02d', $year, $month, $d));
    }

    /**
     * Shared season/rank -> liturgical theme token. The martyr-red
     * heuristic is a best-effort text match, not authoritative rank data.
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
