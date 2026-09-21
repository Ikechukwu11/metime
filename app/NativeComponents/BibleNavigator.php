<?php

namespace App\NativeComponents;

use App\NativeComponents\Concerns\HasActiveTranslation;
use App\NativeComponents\Concerns\HasTopNavActions;
use App\Support\BibleBooks;
use App\Support\BibleConnection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class BibleNavigator extends NativeComponent
{
    use HasActiveTranslation;
    use HasTopNavActions;

    public function navTitle(): string
    {
        return 'Bible';
    }

    public function render(): View
    {
        $connection = BibleConnection::register(strtolower($this->activeTranslation));

        $books = $connection
            ? DB::connection($connection)->table('books')->orderBy('id')->get()
                ->map(fn ($row) => [
                    'id' => $row->id,
                    'name' => $row->name,
                    'chapters' => $row->chapters,
                    'slug' => BibleBooks::slugForId($row->id) ?? (string) $row->id,
                ])
            : collect();

        return view('native-components.bible-navigator', [
            'books' => $books,
            'translationOptions' => array_map('strtoupper', BibleConnection::bundledCodes()),
        ]);
    }

    public function openChapter(string $book, int $chapter): void
    {
        $this->navigate("/bible/{$book}/{$chapter}");
    }
}
