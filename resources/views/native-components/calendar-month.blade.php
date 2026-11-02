<native:refreshable @refresh="refresh" class="w-full h-full bg-theme-background">
<native:column class="w-full p-4 gap-4">

    @if (! $online)
        <native:row class="w-full items-center gap-2 p-2 rounded-lg bg-theme-surface-variant">
            <native:icon name="cloud_off" :size="16" class="text-theme-on-surface-variant" />
            <native:text class="text-xs text-theme-on-surface-variant">
                Offline — showing saved days only
            </native:text>
        </native:row>
    @elseif ($loadingMore)
        <native:row class="w-full items-center gap-2 p-2 rounded-lg bg-theme-surface-variant">
            <native:activity-indicator :size="14" />
            <native:text class="text-xs text-theme-on-surface-variant">
                Loading the rest of this month…
            </native:text>
        </native:row>
    @endif

    <native:row class="w-full items-center justify-between">
        <native:pressable :press-scale="0.97" @press="previousMonth">
            <native:icon name="chevron_left" class="text-theme-on-surface-variant" />
        </native:pressable>
        <native:text class="text-lg font-semibold text-theme-on-surface">{{ $monthLabel }}</native:text>
        <native:pressable :press-scale="0.97" @press="nextMonth">
            <native:icon name="chevron_right" class="text-theme-on-surface-variant" />
        </native:pressable>
    </native:row>

    <native:lazy-grid :columns="7" class="w-full gap-1">
        @foreach ($grid as $cell)
            <native:pressable :press-scale="0.97" @press="openDay('{{ $cell['date'] }}')">
                <native:column class="items-center justify-center p-2 rounded-full aspect-square {{ $cell['is_today'] ? 'bg-theme-primary' : 'bg-theme-surface' }}">
                    <native:text class="text-sm {{ $cell['is_today'] ? 'text-theme-on-primary font-semibold' : 'text-theme-on-surface' }}">
                        {{ $cell['day'] }}
                    </native:text>
                    @if ($cell['color'] && ! $cell['is_today'])
                        <native:column class="w-1.5 h-1.5 rounded-full mt-0.5 bg-theme-{{ $cell['color'] }}" />
                    @endif
                </native:column>
            </native:pressable>
        @endforeach
    </native:lazy-grid>

    <native:column class="gap-2 pt-2">
        <native:row class="items-center gap-4 flex-wrap">
            <native:row class="items-center gap-1">
                <native:column class="w-2 h-2 rounded-full bg-theme-liturgical-green" />
                <native:text class="text-xs text-theme-outline">Ordinary Time</native:text>
            </native:row>
            <native:row class="items-center gap-1">
                <native:column class="w-2 h-2 rounded-full bg-theme-liturgical-violet" />
                <native:text class="text-xs text-theme-outline">Advent / Lent</native:text>
            </native:row>
            <native:row class="items-center gap-1">
                <native:column class="w-2 h-2 rounded-full bg-theme-liturgical-white" />
                <native:text class="text-xs text-theme-outline">Solemnity / Christmas / Easter</native:text>
            </native:row>
        </native:row>
        <native:row class="items-center gap-4 flex-wrap">
            <native:row class="items-center gap-1">
                <native:column class="w-2 h-2 rounded-full bg-theme-liturgical-red" />
                <native:text class="text-xs text-theme-outline">Martyrs</native:text>
            </native:row>
            <native:row class="items-center gap-1">
                <native:column class="w-2 h-2 rounded-full bg-theme-liturgical-rose" />
                <native:text class="text-xs text-theme-outline">Gaudete / Laetare</native:text>
            </native:row>
        </native:row>
    </native:column>

</native:column>
</native:refreshable>
