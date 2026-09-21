<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ImportBibleTranslation extends Command
{
    protected $signature = 'bible:import
        {code : Translation code, e.g. KJV}
        {json : Path to the bulk verses JSON downloaded from Bolls}
        {--books= : Path to a books-reference JSON. Defaults to resources/bible-books/{code}.json}
        {--force : Overwrite an existing .sqlite for this translation}';

    protected $description = 'Build a standalone .sqlite (books + verses + FTS5) for one Bible translation from a Bolls bulk JSON export';

    public function handle(): int
    {
        $code = strtoupper($this->argument('code'));
        $jsonPath = $this->argument('json');
        $booksPath = $this->option('books') ?: resource_path('bible-books/' . strtolower($code) . '.json');

        if (! File::exists($jsonPath)) {
            $this->error("Verses JSON not found: {$jsonPath}");
            return self::FAILURE;
        }

        if (! File::exists($booksPath)) {
            $this->error("Books reference not found: {$booksPath}");
            $this->line('Pass --books=/path/to/books.json, or add resources/bible-books/' . strtolower($code) . '.json');
            return self::FAILURE;
        }

        $targetDir = storage_path('bibles');
        File::ensureDirectoryExists($targetDir);
        $target = $targetDir . '/' . strtolower($code) . '.sqlite';

        if (File::exists($target)) {
            if (! $this->option('force')) {
                $this->error("{$target} already exists. Use --force to rebuild it.");
                return self::FAILURE;
            }
            File::delete($target);
        }

        File::put($target, '');

        $connection = 'bible_import_' . strtolower($code);
        config(["database.connections.{$connection}" => [
            'driver' => 'sqlite',
            'database' => $target,
            'foreign_key_constraints' => false,
        ]]);

        $this->info("Building schema for {$code}...");
        $this->buildSchema($connection);

        $this->info('Loading books reference...');
        $books = json_decode(File::get($booksPath), true);
        DB::connection($connection)->table('books')->insert(
            collect($books)->map(fn($b) => [
                'id' => $b['bookid'],
                'name' => $b['name'],
                'chapters' => $b['chapters'],
            ])->all()
        );
        $this->info(count($books) . ' books loaded.');

        $this->info('Loading verses (this may take a moment)...');
        $verses = json_decode(File::get($jsonPath), true);

        $bar = $this->output->createProgressBar(count($verses));
        $bar->start();

        collect($verses)->chunk(500)->each(function ($chunk) use ($connection, $bar) {
            DB::connection($connection)->table('verses')->insert(
                $chunk->map(fn($v) => [
                    'book_id' => $v['book'],
                    'chapter' => $v['chapter'],
                    'verse' => $v['verse'],
                    'text' => $this->cleanText($v['text']),
                ])->all()
            );
            $bar->advance($chunk->count());
        });

        $bar->finish();
        $this->newLine();

        $this->info('Populating search index...');
        DB::connection($connection)->statement(
            "INSERT INTO verses_fts(rowid, text) SELECT id, text FROM verses"
        );

        $this->info("Done. {$code} written to {$target}");
        $this->line('Move it into resources/bibles/ once verified, so it ships bundled with the app.');

        return self::SUCCESS;
    }

    protected function buildSchema(string $connection): void
    {
        Schema::connection($connection)->create('books', function (Blueprint $table) {
            $table->unsignedInteger('id')->primary(); // Bolls bookid
            $table->string('name');
            $table->unsignedInteger('chapters');
        });

        Schema::connection($connection)->create('verses', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('book_id');
            $table->unsignedInteger('chapter');
            $table->unsignedInteger('verse');
            $table->text('text');
            $table->index(['book_id', 'chapter']);
        });

        DB::connection($connection)->statement(
            "CREATE VIRTUAL TABLE verses_fts USING fts5(text, content='verses', content_rowid='id')"
        );
    }

    /** Strip Strong's-number tags (<S>1234</S>) and any stray HTML the source may include. */
    protected function cleanText(string $text): string
    {
        $text = preg_replace('/<S>\d+<\/S>/', '', $text);
        $text = strip_tags($text);
        return trim(preg_replace('/\s+/', ' ', $text));
    }
}
