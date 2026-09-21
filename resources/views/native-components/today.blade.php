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

    <native:row class="w-full items-center justify-between">
        <native:row class="items-center gap-2">
            @if ($color)
                <native:column class="w-2.5 h-2.5 rounded-full bg-theme-{{ $color }}" />
            @endif
            <native:text class="text-xs uppercase tracking-wide text-theme-outline">
                {{ \Carbon\Carbon::parse($date)->format('l, F j') }}
            </native:text>
        </native:row>
        @if ($day && $day->season)
            <native:chip label="{{ $day->season }}" class="bg-theme-surface text-theme-on-surface-variant" />
        @endif
    </native:row>

    @if (! $day)
        <native:column class="items-center justify-center p-8 gap-2">
            <native:icon name="cloud_off" :size="32" class="text-theme-outline" />
            <native:text class="text-sm text-theme-outline text-center">
                Couldn't load today's readings.
                {{ $online ? 'Pull down to try again.' : 'Check your connection, then pull down to retry.' }}
            </native:text>
        </native:column>
    @else
        @if ($day->celebration_name)
            <native:column class="w-full p-3 gap-2 rounded-lg bg-theme-surface border border-theme-outline">
                @if ($day->celebration_image)
                    <native:image src="{{ $day->celebration_image }}" class="w-full h-32 rounded-md" />
                @endif
                <native:text class="text-xs uppercase text-theme-secondary">{{ $day->celebration_type }}</native:text>
                <native:text class="text-base font-semibold text-theme-on-surface">{{ $day->celebration_name }}</native:text>
            </native:column>
        @endif

        <native:column class="w-full p-4 gap-3 rounded-lg bg-theme-surface border border-theme-outline">
            <native:text class="text-lg font-semibold text-theme-on-surface">Today's Readings</native:text>

            @if ($day->first_reading)
                <native:column class="gap-1">
                    <native:text class="text-xs uppercase text-theme-primary">First Reading</native:text>
                    <native:text class="text-sm text-theme-on-surface-variant">{{ $day->first_reading }}</native:text>
                </native:column>
            @endif

            @if ($day->gospel)
                <native:column class="gap-1">
                    <native:text class="text-xs uppercase text-theme-primary">Gospel</native:text>
                    <native:text class="text-sm text-theme-on-surface-variant">{{ $day->gospel }}</native:text>
                </native:column>
            @endif

            <native:button label="Begin Today's Readings" @press="openTodaysReadings" class="w-full" />
        </native:column>
    @endif

    <native:pressable :press-scale="0.97" @press="resumeReading">
        <native:column class="w-full p-4 gap-2 rounded-lg bg-theme-surface border border-theme-outline">
            <native:text class="text-xs uppercase tracking-wide text-theme-outline">
                {{ $progress ? 'Continue Reading' : 'Start Reading' }}
            </native:text>
            @if ($progress)
                <native:text class="text-base font-semibold text-theme-on-surface">
                    {{ $progress['book_name'] }} — Chapter {{ $progress['chapter'] }}
                </native:text>
                <native:text class="text-xs text-theme-outline">{{ $progress['translation'] }}</native:text>
            @else
                <native:text class="text-base font-semibold text-theme-on-surface">
                    Gospel of John — Chapter 1
                </native:text>
            @endif
            <native:text class="text-xs text-theme-primary">
                {{ $progress ? 'Resume Reading' : 'Start Reading' }} →
            </native:text>
        </native:column>
    </native:pressable>

</native:column>
</native:refreshable>
