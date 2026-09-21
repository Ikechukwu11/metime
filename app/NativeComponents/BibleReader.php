<?php

namespace App\NativeComponents;

use App\NativeComponents\Concerns\HasActiveTranslation;
use App\Support\AppSettings;
use App\Support\BibleBooks;
use App\Support\BibleConnection;
use App\Support\ReadingProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class BibleReader extends NativeComponent
{
    use HasActiveTranslation {
        mount as syncTranslationOnMount;
        onResume as syncTranslationOnResume;
    }

    public bool $showBookSheet = false;
    public bool $showChapterSheet = false;
    public bool $compareMode = false;
    public string $compareTranslation = '';

    public function mount(): void
    {
        $this->syncTranslationOnMount();
        $this->syncCompareState();
    }

    public function onResume(): void
    {
        $this->syncTranslationOnResume();
        $this->syncCompareState();
    }

    protected function syncCompareState(): void
    {
        $this->compareMode = (bool) AppSettings::get('compare_mode', false);
        $this->compareTranslation = strtoupper((string) AppSettings::get('compare_translation', ''));
    }

    public function navTitle(): string
    {
        $bookId = BibleBooks::idForSlug($this->param('book'));
        $name = $bookId ? BibleBooks::nameForId($bookId) : ucfirst($this->param('book'));

        return "{$name} {$this->param('chapter')}";
    }

    public function render(): View
    {
        $bookSlug = $this->param('book');
        $chapter = (int) $this->param('chapter');
        $bookId = BibleBooks::idForSlug($bookSlug);
        $bookName = $bookId ? BibleBooks::nameForId($bookId) : ucfirst($bookSlug);

        $connection = $bookId ? BibleConnection::register(strtolower($this->activeTranslation)) : null;

        $verses = $connection
            ? DB::connection($connection)->table('verses')
                ->where('book_id', $bookId)
                ->where('chapter', $chapter)
                ->orderBy('verse')
                ->get()
            : collect();

        // Record progress only once we know the chapter actually resolved
        // to real content -- avoids overwriting a valid bookmark with a
        // dead reference if something failed to load.
        if (! $verses->isEmpty()) {
            ReadingProgress::record($this->activeTranslation, $bookSlug, $bookName, $chapter);
        }

        $compareCandidates = ($this->compareMode && $bookId)
            ? $this->compareCandidates($bookId, $chapter)
            : [];

        if ($this->compareMode && ! in_array($this->compareTranslation, $compareCandidates, true)) {
            $this->compareTranslation = $compareCandidates[0] ?? '';
            AppSettings::set('compare_translation', $this->compareTranslation);
        }

        $compareVerses = [];
        if ($this->compareMode && $this->compareTranslation !== '') {
            $compareConnection = BibleConnection::register(strtolower($this->compareTranslation));
            if ($compareConnection) {
                $compareVerses = DB::connection($compareConnection)->table('verses')
                    ->where('book_id', $bookId)
                    ->where('chapter', $chapter)
                    ->orderBy('verse')
                    ->pluck('text', 'verse')
                    ->all();
            }
        }

        $books = ($this->showBookSheet && $connection)
            ? DB::connection($connection)->table('books')->orderBy('id')->get()
                ->map(fn ($row) => [
                    'name' => $row->name,
                    'chapters' => $row->chapters,
                    'slug' => BibleBooks::slugForId($row->id) ?? (string) $row->id,
                ])
            : collect();

        $chapterCount = ($this->showChapterSheet && $connection && $bookId)
            ? (int) (DB::connection($connection)->table('books')->where('id', $bookId)->value('chapters') ?? 0)
            : 0;

        return view('native-components.bible-reader', [
            'book' => $bookSlug,
            'bookName' => $bookName,
            'chapter' => $chapter,
            'verses' => $verses,
            'translationOptions' => array_map('strtoupper', BibleConnection::bundledCodes()),
            'notFound' => $connection === null,
            'books' => $books,
            'chapterCount' => $chapterCount,
            'compareCandidates' => $compareCandidates,
            'compareVerses' => $compareVerses,
        ]);
    }

    public function toggleCompareMode(): void
    {
        $this->compareMode = ! $this->compareMode;
        AppSettings::set('compare_mode', $this->compareMode ? '1' : '');

        if ($this->compareMode && $this->compareTranslation === '') {
            $bookId = BibleBooks::idForSlug($this->param('book'));
            $chapter = (int) $this->param('chapter');
            $candidates = $bookId ? $this->compareCandidates($bookId, $chapter) : [];

            if (! empty($candidates)) {
                $this->switchCompareTranslation($candidates[0]);
            }
        }
    }

    public function switchCompareTranslation(string $code): void
    {
        $this->compareTranslation = strtoupper($code);
        AppSettings::set('compare_translation', $this->compareTranslation);
    }

    protected function compareCandidates(int $bookId, int $chapter): array
    {
        $candidates = [];

        foreach (BibleConnection::bundledCodes() as $code) {
            $code = strtoupper($code);
            if ($code === $this->activeTranslation) {
                continue;
            }

            $connection = BibleConnection::register($code);
            if ($connection === null) {
                continue;
            }

            $hasChapter = DB::connection($connection)->table('verses')
                ->where('book_id', $bookId)
                ->where('chapter', $chapter)
                ->exists();

            if ($hasChapter) {
                $candidates[] = $code;
            }
        }

        sort($candidates);

        return $candidates;
    }

    public function previousChapter(): void
    {
        $chapter = max(1, (int) $this->param('chapter') - 1);
        $this->replace("/bible/{$this->param('book')}/{$chapter}");
    }

    public function nextChapter(): void
    {
        $chapter = (int) $this->param('chapter') + 1;
        $this->replace("/bible/{$this->param('book')}/{$chapter}");
    }

    public function openBookSheet(): void
    {
        $this->showBookSheet = true;
    }

    public function closeBookSheet(): void
    {
        $this->showBookSheet = false;
    }

    public function selectBook(string $slug): void
    {
        $this->showBookSheet = false;
        $this->replace("/bible/{$slug}/1");
    }

    public function openChapterSheet(): void
    {
        $this->showChapterSheet = true;
    }

    public function closeChapterSheet(): void
    {
        $this->showChapterSheet = false;
    }

    public function selectChapter(int $chapter): void
    {
        $this->showChapterSheet = false;
        $this->replace("/bible/{$this->param('book')}/{$chapter}");
    }
}
