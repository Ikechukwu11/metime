<native:column class="w-full h-full bg-theme-background">

    <native:row class="w-full items-center justify-between p-3 border-b border-theme-outline">
        <native:select
            :options="$translationOptions"
            native:model="activeTranslation"
            @change="switchTranslation"
            placeholder="Translation"
        />
        <native:pressable :press-scale="0.97" @press="toggleCompareMode">
            <native:icon
                name="{{ $compareMode ? 'layers' : 'compare_arrows' }}"
                class="{{ $compareMode ? 'text-theme-primary' : 'text-theme-on-surface-variant' }}"
            />
        </native:pressable>
    </native:row>

    @if ($compareMode)
        <native:row class="w-full items-center justify-between p-3 border-b border-theme-outline bg-theme-surface-variant">
            <native:text class="text-xs uppercase text-theme-on-surface-variant">Compare with</native:text>
            @if (empty($compareCandidates))
                <native:text class="text-xs text-theme-outline">No other translation has this chapter</native:text>
            @else
                <native:select
                    :options="$compareCandidates"
                    native:model="compareTranslation"
                    @change="switchCompareTranslation"
                    placeholder="Translation"
                />
            @endif
        </native:row>
    @endif

    <native:scroll-view class="w-full flex-1">
    <native:column class="w-full p-4 gap-4">

        {{-- Book and chapter selectors side by side, replacing the old
             single "Book N" heading + separate bottom "Chapter N" label. --}}
        <native:column class="gap-1">
            <native:row class="items-center gap-4">
                <native:pressable :press-scale="0.97" @press="openBookSheet">
                    <native:row class="items-center gap-1">
                        <native:text class="text-xl font-semibold text-theme-on-surface">{{ $bookName }}</native:text>
                        <native:icon name="expand_more" class="text-theme-outline" />
                    </native:row>
                </native:pressable>
                <native:pressable :press-scale="0.97" @press="openChapterSheet">
                    <native:row class="items-center gap-1">
                        <native:text class="text-xl font-semibold text-theme-on-surface">{{ $chapter }}</native:text>
                        <native:icon name="expand_more" class="text-theme-outline" />
                    </native:row>
                </native:pressable>
            </native:row>
            <native:text class="text-xs text-theme-outline">
                {{ \App\Support\BibleTranslations::fullName($activeTranslation) }}
            </native:text>
        </native:column>

        @if ($notFound)
            <native:text class="text-sm text-theme-outline">
                That passage isn't available in {{ $activeTranslation }} right now.
            </native:text>
        @elseif ($verses->isEmpty())
            <native:text class="text-sm text-theme-outline">
                No verses found for this chapter.
            </native:text>
        @else
            <native:column class="gap-4">
                @foreach ($verses as $verse)
                    <native:column class="gap-1">
                        <native:row class="items-start gap-3">
                            <native:text class="text-xs text-theme-outline w-6 pt-1">{{ $verse->verse }}</native:text>
                            <native:text class="flex-1 text-base leading-relaxed text-theme-on-surface">{{ $verse->text }}</native:text>
                        </native:row>

                        {{-- Comparison translation layered directly beneath its
                             matching verse, tinted like a highlight/annotation
                             rather than styled as a separate block. --}}
                        @if ($compareMode && isset($compareVerses[$verse->verse]))
                            <native:row class="items-start gap-3 p-2 rounded-lg bg-theme-accent/10">
                                <native:text class="text-xs text-theme-accent w-6 pt-1">{{ $verse->verse }}</native:text>
                                <native:column class="flex-1 gap-0.5">
                                    <native:text class="text-xs uppercase text-theme-accent">{{ $compareTranslation }}</native:text>
                                    <native:text class="text-base leading-relaxed text-theme-on-surface">{{ $compareVerses[$verse->verse] }}</native:text>
                                </native:column>
                            </native:row>
                        @endif
                    </native:column>
                @endforeach
            </native:column>
        @endif
    </native:column>
    </native:scroll-view>

    <native:row class="w-full items-center justify-center gap-8 p-4 border-t border-theme-outline">
        <native:pressable :press-scale="0.97" @press="previousChapter">
            <native:icon name="chevron_left" class="text-theme-primary" />
        </native:pressable>
        <native:text class="text-sm text-theme-outline">Chapter {{ $chapter }}</native:text>
        <native:pressable :press-scale="0.97" @press="nextChapter">
            <native:icon name="chevron_right" class="text-theme-primary" />
        </native:pressable>
    </native:row>

    {{-- Book picker --}}
    <native:bottom-sheet :visible="$showBookSheet" @dismiss="closeBookSheet" detents="medium">
        <native:column class="w-full p-4 gap-3">
            <native:text class="text-lg font-semibold text-theme-on-surface">Choose a Book</native:text>
            <native:scroll-view class="w-full">
            <native:column class="gap-1">
                @foreach ($books as $b)
                    <native:pressable :press-scale="0.97" @press="selectBook('{{ $b['slug'] }}')">
                        <native:row class="w-full items-center justify-between p-3 rounded-lg {{ $b['slug'] === $book ? 'bg-theme-primary' : '' }}">
                            <native:text class="text-base {{ $b['slug'] === $book ? 'text-theme-on-primary font-semibold' : 'text-theme-on-surface' }}">
                                {{ $b['name'] }}
                            </native:text>
                            <native:text class="text-xs text-theme-outline">{{ $b['chapters'] }} ch.</native:text>
                        </native:row>
                    </native:pressable>
                @endforeach
            </native:column>
            </native:scroll-view>
        </native:column>
    </native:bottom-sheet>

    {{-- Chapter picker --}}
    <native:bottom-sheet :visible="$showChapterSheet" @dismiss="closeChapterSheet" detents="medium">
        <native:column class="w-full p-4 gap-3">
            <native:text class="text-lg font-semibold text-theme-on-surface">{{ $bookName }} — Choose a Chapter</native:text>
            <native:scroll-view class="w-full">
            <native:lazy-grid :columns="5" class="w-full gap-2">
                @for ($i = 1; $i <= $chapterCount; $i++)
                    <native:pressable :press-scale="0.97" @press="selectChapter({{ $i }})">
                        <native:column class="items-center justify-center p-3 rounded-lg aspect-square {{ $i === $chapter ? 'bg-theme-primary' : 'bg-theme-surface' }}">
                            <native:text class="text-sm {{ $i === $chapter ? 'text-theme-on-primary font-semibold' : 'text-theme-on-surface' }}">
                                {{ $i }}
                            </native:text>
                        </native:column>
                    </native:pressable>
                @endfor
            </native:lazy-grid>
            </native:scroll-view>
        </native:column>
    </native:bottom-sheet>

</native:column>
