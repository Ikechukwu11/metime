<native:scroll-view class="w-full h-full bg-theme-background">
<native:column class="w-full p-4 gap-4">

    <native:select
        :options="$translationOptions"
        native:model="activeTranslation"
        @change="switchTranslation"
        placeholder="Translation"
    />
    <native:text class="text-xs text-theme-outline">
        {{ \App\Support\BibleTranslations::fullName($activeTranslation) }}
    </native:text>

    @if ($books->isEmpty())
        <native:column class="items-center justify-center p-8 gap-2">
            <native:icon name="cloud_download" :size="32" class="text-theme-outline" />
            <native:text class="text-sm text-theme-outline text-center">
                {{ $activeTranslation }} isn't installed yet.
            </native:text>
        </native:column>
    @else
        <native:column class="gap-2">
            @foreach ($books as $book)
                <native:pressable :press-scale="0.97" @press="openChapter('{{ $book['slug'] }}', 1)">
                    <native:row class="w-full items-center justify-between p-3 rounded-lg bg-theme-surface border border-theme-outline">
                        <native:column>
                            <native:text class="text-base text-theme-on-surface">{{ $book['name'] }}</native:text>
                            <native:text class="text-xs text-theme-outline">{{ $book['chapters'] }} chapters</native:text>
                        </native:column>
                        <native:icon name="chevron_right" class="text-theme-outline" />
                    </native:row>
                </native:pressable>
            @endforeach
        </native:column>
    @endif

</native:column>
</native:scroll-view>
