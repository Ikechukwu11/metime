<native:scroll-view class="w-full h-full bg-theme-background">
<native:column class="w-full p-4 gap-6">

    <native:column class="gap-2">
        <native:text class="text-xs uppercase tracking-wide text-theme-outline">Translations</native:text>

        @foreach ($translations as $code)
            <native:row class="w-full items-center justify-between p-3 rounded-lg bg-theme-surface border border-theme-outline">
                <native:column>
                    <native:text class="text-base text-theme-on-surface">{{ strtoupper($code) }}</native:text>
                    <native:text class="text-xs text-theme-outline">{{ \App\Support\BibleTranslations::fullName($code) }}</native:text>
                </native:column>
                <native:icon name="check_circle" class="text-theme-primary" />
            </native:row>
        @endforeach

        <native:text class="text-xs text-theme-outline">
            All translations above ship bundled with the app and work fully offline.
        </native:text>
    </native:column>

    <native:column class="gap-2">
        <native:text class="text-xs uppercase tracking-wide text-theme-outline">Calendar</native:text>
        <native:row class="w-full items-center justify-between p-3 rounded-lg bg-theme-surface border border-theme-outline">
            <native:text class="text-base text-theme-on-surface">Rite</native:text>
            <native:text class="text-sm text-theme-on-surface-variant">Roman Catholic</native:text>
        </native:row>
    </native:column>

    <native:column class="gap-2">
        <native:text class="text-xs uppercase tracking-wide text-theme-outline">Appearance</native:text>
        <native:row class="w-full items-center justify-between p-3 rounded-lg bg-theme-surface border border-theme-outline">
            <native:text class="text-base text-theme-on-surface">Theme</native:text>
            <native:select
                :options="$themeOptions"
                native:model="theme"
                @change="switchTheme"
            />
        </native:row>
    </native:column>

</native:column>
</native:scroll-view>
