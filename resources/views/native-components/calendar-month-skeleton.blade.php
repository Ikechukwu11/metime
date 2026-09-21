<native:column class="w-full h-full bg-theme-background p-4 gap-4">
    <native:row class="w-full items-center justify-center">
        <native:column class="w-32 h-5 rounded-full bg-theme-surface-variant" />
    </native:row>
    <native:lazy-grid :columns="7" class="w-full gap-1">
        @for ($i = 0; $i < 35; $i++)
            <native:column class="rounded-full aspect-square bg-theme-surface-variant" />
        @endfor
    </native:lazy-grid>
</native:column>
