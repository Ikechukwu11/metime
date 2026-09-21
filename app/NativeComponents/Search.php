<?php

namespace App\NativeComponents;

use App\NativeComponents\Concerns\HasTopNavActions;
use App\Support\AppSettings;
use App\Support\BibleConnection;
use App\Support\BibleSearch;
use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class Search extends NativeComponent
{
    use HasTopNavActions;

    public string $query = '';
    public array $results = [];

    public function navTitle(): string
    {
        return 'Search';
    }

    // Runs whenever $query changes from the UI (native:model-bound input)
    // -- confirmed against the real updated{Property}() lifecycle hook,
    // replacing the earlier "recompute every render()" workaround.
    public function updatedQuery(string $value): void
    {
        $this->results = $value !== ''
            ? BibleSearch::across($value, BibleConnection::bundledCodes())
            : [];
    }

    public function render(): View
    {
        return view('native-components.search', [
            'query' => $this->query,
            'results' => $this->results,
        ]);
    }

    public function openResult(string $translation, string $bookSlug, int $chapter): void
    {
        // Open the result in the translation it was actually found in.
        AppSettings::set('active_translation', strtoupper($translation));
        $this->navigate("/bible/{$bookSlug}/{$chapter}");
    }
}
