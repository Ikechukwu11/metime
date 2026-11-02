<?php

namespace App\Support;

use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Http;

class CatholicReadingsClient
{
    protected const BASE = 'https://cpbjr.github.io/catholic-readings-api';

    /**
     * Fetches both endpoint families for ONE date concurrently (via
     * Http::pool()) instead of two sequential round-trips -- roughly
     * halves the wait for a single-day lookup.
     */
    public function fetchDay(string $date): array
    {
        try {
            $responses = Http::pool(fn (Pool $pool) => [
                'readings' => $pool->as('readings')->timeout(8)->get($this->url('readings', $date)),
                'celebration' => $pool->as('celebration')->timeout(8)->get($this->url('liturgical-calendar', $date)),
            ]);
        } catch (\Throwable) {
            return ['readings' => null, 'celebration' => null];
        }

        return [
            'readings' => $this->jsonOrNull($responses['readings'] ?? null),
            'celebration' => $this->jsonOrNull($responses['celebration'] ?? null),
        ];
    }

    /**
     * Fetches both endpoint families for MANY dates concurrently, chunked
     * to avoid opening dozens of connections at once. This is the piece
     * that replaces the old "31 dates x 2 sequential requests" loop.
     */
    public function fetchMany(array $dates, int $chunkSize = 10): array
    {
        $results = [];

        foreach (array_chunk($dates, $chunkSize) as $chunk) {
            try {
                $responses = Http::pool(function (Pool $pool) use ($chunk) {
                    $requests = [];
                    foreach ($chunk as $date) {
                        $requests["r:{$date}"] = $pool->as("r:{$date}")->timeout(8)->get($this->url('readings', $date));
                        $requests["c:{$date}"] = $pool->as("c:{$date}")->timeout(8)->get($this->url('liturgical-calendar', $date));
                    }

                    return $requests;
                });
            } catch (\Throwable) {
                continue; // skip this chunk, keep going with the rest
            }

            foreach ($chunk as $date) {
                $results[$date] = [
                    'readings' => $this->jsonOrNull($responses["r:{$date}"] ?? null),
                    'celebration' => $this->jsonOrNull($responses["c:{$date}"] ?? null),
                ];
            }
        }

        return $results;
    }

    protected function jsonOrNull($response): ?array
    {
        if ($response === null) {
            return null;
        }

        try {
            return $response->ok() ? $response->json() : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public function url(string $family, string $date): string
    {
        [$year, $month, $day] = explode('-', $date);

        return sprintf('%s/%s/%s/%s-%s.json', static::BASE, $family, $year, $month, $day);
    }
}
