<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

class CatholicReadingsClient
{
    protected const BASE = 'https://cpbjr.github.io/catholic-readings-api';

    /** GET /readings/{year}/{MM-DD}.json -- null on any failure (offline, 404, malformed). */
    public function fetchReadings(string $date): ?array
    {
        return $this->fetch('readings', $date);
    }

    /** GET /liturgical-calendar/{year}/{MM-DD}.json -- null on any failure. */
    public function fetchCelebration(string $date): ?array
    {
        return $this->fetch('liturgical-calendar', $date);
    }

    protected function fetch(string $family, string $date): ?array
    {
        [$year, $month, $day] = explode('-', $date);

        try {
            $response = Http::timeout(8)->get(
                sprintf('%s/%s/%s/%s-%s.json', static::BASE, $family, $year, $month, $day)
            );
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }
}
