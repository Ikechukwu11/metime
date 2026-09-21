<column class="w-full h-full bg-theme-background p-6 gap-8 items-center justify-center safe-area">

    <column class="items-center gap-3">
        <icon name="church" :size="48" class="text-theme-primary" />
        <text class="text-2xl font-semibold text-theme-on-background">
            {{ config('app.name') }}
        </text>
        <text class="text-base text-theme-on-surface-variant text-center">
            Scripture and the liturgical calendar, together, offline.
        </text>
    </column>

    <column class="w-full gap-3">
        <text class="text-xs uppercase tracking-wide text-theme-outline">
            Choose your translations
        </text>

        {{--
        Static placeholder only — no model binding yet, since
        that needs a named public boolean property per translation
        (or a confirmed array/dot-notation binding, unverified against
        the docs). Wire this up properly once this screen gets real
        functionality.
        --}}
        @foreach (['KJV', 'DRB', 'NABRE', 'RSV-CE'] as $translation)
        <row class="w-full items-center p-4 rounded-lg bg-theme-surface-container border border-theme-outline-variant">
            <checkbox :label="$translation" />
        </row>
        @endforeach

        <text class="text-xs text-theme-outline">
            Selected translations are bundled for offline use.
        </text>
    </column>

    <button label="Continue" @press="finish" class="w-full" />

</column>