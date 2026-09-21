<native:refreshable @refresh="refresh" class="w-full h-full bg-theme-background">
<native:column class="w-full p-4 gap-4">

    @if (! $online)
        <native:row class="w-full items-center gap-2 p-2 rounded-lg bg-theme-surface-variant">
            <native:icon name="cloud_off" :size="16" class="text-theme-on-surface-variant" />
            <native:text class="text-xs text-theme-on-surface-variant">
                Offline — showing the last saved data
            </native:text>
        </native:row>
    @endif

    <native:column class="gap-1">
        <native:row class="items-center gap-2">
            @if ($color)
                <native:column class="w-2.5 h-2.5 rounded-full bg-theme-{{ $color }}" />
            @endif
            @if ($day && $day->season)
                <native:chip label="{{ $day->season }}" class="bg-theme-surface" />
            @endif
        </native:row>
        <native:text class="text-xl font-semibold text-theme-on-surface">
            {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
        </native:text>
        @if ($day && $day->liturgical_note)
            <native:text class="text-xs text-theme-outline">{{ $day->liturgical_note }}</native:text>
        @endif
    </native:column>

    @if (! $day)
        <native:column class="items-center justify-center p-8 gap-2">
            <native:icon name="cloud_off" :size="32" class="text-theme-outline" />
            <native:text class="text-sm text-theme-outline text-center">
                Couldn't load this date.
                {{ $online ? 'Pull down to try again.' : 'Check your connection, then pull down to retry.' }}
            </native:text>
        </native:column>
    @else
        @if ($day->celebration_name)
            <native:column class="w-full p-4 gap-2 rounded-lg bg-theme-surface border border-theme-outline">
                @if ($day->celebration_image)
                    <native:image src="{{ $day->celebration_image }}" class="w-full h-40 rounded-md" />
                @endif
                <native:text class="text-xs uppercase text-theme-secondary">{{ $day->celebration_type }}</native:text>
                <native:text class="text-lg font-semibold text-theme-on-surface">{{ $day->celebration_name }}</native:text>
                @if ($day->celebration_quote)
                    <native:text class="text-sm italic text-theme-on-surface-variant">"{{ $day->celebration_quote }}"</native:text>
                @endif
                @if ($day->celebration_description)
                    <native:text class="text-sm text-theme-on-surface-variant">{{ $day->celebration_description }}</native:text>
                @endif
            </native:column>
        @endif

        @if (! empty($readings))
            <native:column class="gap-2">
                <native:text class="text-xs uppercase tracking-wide text-theme-primary">Readings</native:text>

                @foreach ($readings as $reading)
                    @if ($reading['book_slug'])
                        <native:pressable :press-scale="0.97" @press="openReading('{{ $reading['book_slug'] }}', {{ $reading['chapter'] }})">
                            <native:row class="w-full items-center justify-between p-3 rounded-lg bg-theme-surface border border-theme-outline">
                                <native:column>
                                    <native:text class="text-xs uppercase text-theme-primary">{{ $reading['label'] }}</native:text>
                                    <native:text class="text-sm text-theme-on-surface-variant">{{ $reading['ref'] }}</native:text>
                                </native:column>
                                <native:icon name="chevron_right" class="text-theme-outline" />
                            </native:row>
                        </native:pressable>
                    @else
                        <native:row class="w-full items-center justify-between p-3 rounded-lg bg-theme-surface border border-theme-outline">
                            <native:column>
                                <native:text class="text-xs uppercase text-theme-primary">{{ $reading['label'] }}</native:text>
                                <native:text class="text-sm text-theme-on-surface-variant">{{ $reading['ref'] }}</native:text>
                            </native:column>
                        </native:row>
                    @endif
                @endforeach
            </native:column>
        @endif
    @endif

</native:column>
</native:refreshable>
