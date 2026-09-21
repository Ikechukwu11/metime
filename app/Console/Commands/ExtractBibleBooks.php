<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ExtractBibleBooks extends Command
{
    protected $signature = 'bible:extract-books
        {code : Translation code (e.g. WEB), or "all" to extract every translation in the source file}
        {--source= : Path to the full Bolls translations_books.json. Defaults to resources/bible-books/_source/translations_books.json}';

    protected $description = 'Pull one (or every) translation\'s book list out of Bolls\' master translations_books.json';

    public function handle(): int
    {
        $sourcePath = $this->option('source')
            ?: resource_path('bible-books/_source/translations_books.json');

        if (! File::exists($sourcePath)) {
            $this->error("Source file not found: {$sourcePath}");
            $this->line('Save the full translations_books.json there, or pass --source=/path/to/it');
            return self::FAILURE;
        }

        $data = json_decode(File::get($sourcePath), true);
        $codes = $this->argument('code') === 'all'
            ? array_keys($data)
            : [strtoupper($this->argument('code'))];

        foreach ($codes as $code) {
            if (! isset($data[$code])) {
                $this->warn("{$code} not found in source file, skipping.");
                continue;
            }

            $books = collect($data[$code])
                ->map(fn($b) => [
                    'bookid' => $b['bookid'],
                    'name' => $b['name'],
                    'chapters' => $b['chapters'],
                ])
                ->sortBy('bookid')
                ->values();

            $target = resource_path('bible-books/' . strtolower($code) . '.json');
            File::put($target, $books->toJson());

            $this->info("{$code} -> {$books->count()} books -> {$target}");
        }

        return self::SUCCESS;
    }
}
