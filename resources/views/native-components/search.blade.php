<native:column class="w-full h-full bg-theme-background p-4 gap-4">

    <native:outlined-text-input
        placeholder="Search scripture..."
        native:model.debounce.300ms="query"
        leading-icon="search"
        a11y-label="Search scripture"
    />

    @if ($query === '')
        <native:column class="flex-1 items-center justify-center gap-2">
            <native:icon name="auto_stories" :size="32" class="text-theme-outline" />
            <native:text class="text-sm text-theme-outline text-center">
                Search across your downloaded translations
            </native:text>
        </native:column>
    @elseif (empty($results))
        <native:column class="flex-1 items-center justify-center gap-2">
            <native:text class="text-sm text-theme-outline">No results for "{{ $query }}"</native:text>
        </native:column>
    @else
        <native:scroll-view class="flex-1">
        <native:column class="gap-2">
            @foreach ($results as $result)
                <native:pressable :press-scale="0.97" @press="openResult('{{ $result['translation'] }}', '{{ $result['book_slug'] }}', {{ $result['chapter'] }})">
                    <native:column class="p-3 rounded-lg bg-theme-surface border border-theme-outline gap-1">
                        <native:row class="items-center justify-between">
                            <native:text class="text-xs uppercase text-theme-primary">
                                {{ $result['book_name'] }} {{ $result['chapter'] }}:{{ $result['verse'] }}
                            </native:text>
                            <native:chip label="{{ $result['translation'] }}" class="bg-theme-surface" />
                        </native:row>
                        <native:text class="text-sm text-theme-on-surface-variant">{{ $result['snippet'] }}</native:text>
                    </native:column>
                </native:pressable>
            @endforeach
        </native:column>
        </native:scroll-view>
    @endif

</native:column>
