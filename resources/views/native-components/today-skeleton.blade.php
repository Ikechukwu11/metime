{{-- Static skeleton (no shimmer animation -- animate-pulse support in
     native-ui's styling subset hasn't been confirmed, so this stays
     safe/static rather than assume it works). Shapes roughly mirror
     the real layout so the swap-in doesn't jump around. --}}
<native:column class="w-full h-full bg-theme-background p-4 gap-4">

    <native:row class="w-full items-center justify-between">
        <native:column class="w-32 h-3 rounded-full bg-theme-surface-variant" />
        <native:column class="w-20 h-5 rounded-full bg-theme-surface-variant" />
    </native:row>

    <native:column class="w-full h-40 rounded-lg bg-theme-surface-variant" />

    <native:column class="w-full h-28 rounded-lg bg-theme-surface-variant" />

    <native:column class="w-full h-24 rounded-lg bg-theme-surface-variant" />

</native:column>
