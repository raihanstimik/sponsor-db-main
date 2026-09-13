@props([
    'columns' => null,
    'hasReorderableColumns',
    'hasToggleableColumns',
    'reorderAnimationDuration' => 300,
])

<div
    @if ($hasToggleableColumns)
        x-id="['fi-ta-col-manager-group-checkbox', 'fi-ta-col-manager-column-checkbox']"
    @endif
    @if ($hasReorderableColumns)
        x-sortable
        x-on:end.stop="reorderColumns($event.target.sortable.toArray())"
        data-sortable-animation-duration="{{ $reorderAnimationDuration }}"
    @endif
    class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 fi-ta-col-manager-items"
>
    <template
        x-for="(column, index) in columns.filter((column) => ! column.isHidden && column.label)"
        x-bind:key="(column.type === 'group' ? 'group::' : 'column::') + column.name + '_' + index"
    >
        <div
            @if ($hasReorderableColumns)
                x-bind:x-sortable-item="column.type === 'group' ? 'group::' + column.name : 'column::' + column.name"
            @endif
            class="h-full"
        >
            <template x-if="column.type === 'group'">
                <div class="fi-ta-col-manager-group flex flex-col gap-2 p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/40">
                    <div class="fi-ta-col-manager-item flex items-center justify-between gap-3 p-2 rounded-md bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/60 shadow-xs">
                        <label
                            @if ($hasToggleableColumns) x-bind:for="$id('fi-ta-col-manager-group-checkbox', column.name)" @endif
                            class="fi-ta-col-manager-label flex flex-1 items-center gap-2.5 cursor-pointer min-w-0"
                        >
                            @if ($hasToggleableColumns)
                                <input
                                    type="checkbox"
                                    class="fi-checkbox-input fi-valid rounded text-primary-600 focus:ring-primary-500 shrink-0"
                                    x-bind:id="$id('fi-ta-col-manager-group-checkbox', column.name)"
                                    x-bind:checked="(groupedColumns[column.name] || {}).checked || false"
                                    x-bind:disabled="(groupedColumns[column.name] || {}).disabled || false"
                                    x-effect="$el.indeterminate = (groupedColumns[column.name] || {}).indeterminate || false"
                                    x-on:change="toggleGroup(column.name)"
                                />
                            @endif

                            <span class="text-sm font-semibold text-gray-950 dark:text-white truncate" x-html="column.label"></span>
                        </label>

                        @if ($hasReorderableColumns)
                            <button
                                x-sortable-handle
                                x-bind:aria-label="@js(__('filament-tables::table.column_manager.actions.reorder.label')) + (column.name ? ' ' + column.name : '')"
                                x-on:click.stop
                                class="fi-ta-col-manager-reorder-handle p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-grab active:cursor-grabbing rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors shrink-0"
                                type="button"
                                title="Seret untuk memindahkan posisi"
                            >
                                <x-filament::icon icon="heroicon-m-bars-2" class="h-4 w-4" />
                            </button>
                        @endif
                    </div>
                    <div
                        @if ($hasReorderableColumns)
                            x-sortable
                            x-on:end.stop="reorderGroupColumns($event.target.sortable.toArray(), column.name)"
                            data-sortable-animation-duration="{{ $reorderAnimationDuration }}"
                        @endif
                        class="fi-ta-col-manager-group-items flex flex-col gap-1.5 ps-4"
                    >
                        <template
                            x-for="
                                (groupColumn, index) in
                                    column.columns.filter((column) => ! column.isHidden && column.label)
                            "
                            x-bind:key="'column::' + groupColumn.name + '_' + index"
                        >
                            <div
                                @if ($hasReorderableColumns)
                                    x-bind:x-sortable-item="'column::' + groupColumn.name"
                                @endif
                            >
                                <div class="fi-ta-col-manager-item flex items-center justify-between gap-3 p-2 rounded-md bg-white dark:bg-gray-800 border border-gray-200/80 dark:border-gray-700/60 shadow-xs">
                                    <label
                                        @if ($hasToggleableColumns) x-bind:for="$id('fi-ta-col-manager-column-checkbox', groupColumn.name)" @endif
                                        class="fi-ta-col-manager-label flex flex-1 items-center gap-2.5 cursor-pointer min-w-0"
                                    >
                                        @if ($hasToggleableColumns)
                                            <input
                                                type="checkbox"
                                                class="fi-checkbox-input fi-valid rounded text-primary-600 focus:ring-primary-500 shrink-0"
                                                x-bind:id="$id('fi-ta-col-manager-column-checkbox', groupColumn.name)"
                                                x-bind:checked="(getColumn(groupColumn.name, column.name) || {}).isToggled || false"
                                                x-bind:disabled="(getColumn(groupColumn.name, column.name) || {}).isToggleable === false"
                                                x-on:change="toggleColumn(groupColumn.name, column.name)"
                                            />
                                        @endif

                                        <span class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate" x-html="groupColumn.label"></span>
                                    </label>

                                    @if ($hasReorderableColumns)
                                        <button
                                            x-sortable-handle
                                            x-bind:aria-label="@js(__('filament-tables::table.column_manager.actions.reorder.label')) + (groupColumn.name ? ' ' + groupColumn.name : '')"
                                            x-on:click.stop
                                            class="fi-ta-col-manager-reorder-handle p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-grab active:cursor-grabbing rounded hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors shrink-0"
                                            type="button"
                                            title="Seret untuk memindahkan posisi"
                                        >
                                            <x-filament::icon icon="heroicon-m-bars-2" class="h-4 w-4" />
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
            <template x-if="column.type !== 'group'">
                <div
                    class="fi-ta-col-manager-item group flex items-center justify-between gap-3 px-3 py-2.5 rounded-lg border border-gray-200/90 dark:border-gray-700/80 bg-white dark:bg-gray-800/90 hover:border-gray-300 dark:hover:border-gray-600 hover:shadow-xs transition-all select-none"
                    x-bind:class="{ 'border-primary-500/50 bg-primary-50/25 dark:bg-primary-950/25 dark:border-primary-500/40 shadow-xs': (getColumn(column.name, null) || {}).isToggled }"
                >
                    <label
                        @if ($hasToggleableColumns) x-bind:for="$id('fi-ta-col-manager-column-checkbox', column.name)" @endif
                        class="fi-ta-col-manager-label flex flex-1 items-center gap-3 cursor-pointer min-w-0"
                    >
                        @if ($hasToggleableColumns)
                            <input
                                type="checkbox"
                                class="fi-checkbox-input fi-valid rounded text-primary-600 focus:ring-primary-500 shrink-0"
                                x-bind:id="$id('fi-ta-col-manager-column-checkbox', column.name)"
                                x-bind:checked="(getColumn(column.name, null) || {}).isToggled || false"
                                x-bind:disabled="(getColumn(column.name, null) || {}).isToggleable === false"
                                x-on:change="toggleColumn(column.name)"
                            />
                        @endif

                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate" x-html="column.label"></span>
                    </label>

                    @if ($hasReorderableColumns)
                        <button
                            x-sortable-handle
                            x-bind:aria-label="@js(__('filament-tables::table.column_manager.actions.reorder.label')) + (column.name ? ' ' + column.name : '')"
                            x-on:click.stop
                            class="fi-ta-col-manager-reorder-handle p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 cursor-grab active:cursor-grabbing rounded hover:bg-gray-100 dark:hover:bg-gray-700/60 transition-colors shrink-0"
                            type="button"
                            title="Seret untuk mengatur urutan"
                        >
                            <x-filament::icon icon="heroicon-m-bars-2" class="h-4 w-4" />
                        </button>
                    @endif
                </div>
            </template>
        </div>
    </template>
</div>

