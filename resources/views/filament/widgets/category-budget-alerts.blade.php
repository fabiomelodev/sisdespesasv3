<x-filament-widgets::widget>
    <x-filament::section heading="Atenção ao Orçamento" icon="heroicon-o-exclamation-triangle" icon-color="warning">
        @php $alerts = $this->getAlerts(); @endphp

        @if (empty($alerts))
            <p class="fi-fo-field-wrp-helper-text text-sm text-gray-500 dark:text-gray-400">
                Nenhuma categoria perto do limite neste período.
            </p>
        @else
            <ul class="space-y-3">
                @foreach ($alerts as $alert)
                    <li class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-gray-950 dark:text-white">
                                {{ $alert['name'] }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ \App\Helpers\FormatCurrency::getFormatCurrency($alert['spent']) }}
                                /
                                {{ \App\Helpers\FormatCurrency::getFormatCurrency($alert['limit']) }}
                            </p>
                        </div>

                        <x-filament::badge :color="$alert['percentage'] >= 100 ? 'danger' : 'warning'">
                            {{ number_format($alert['percentage'], 0) }}%
                        </x-filament::badge>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>