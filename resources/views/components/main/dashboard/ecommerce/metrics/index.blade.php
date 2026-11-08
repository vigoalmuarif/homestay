<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:gap-6">
    <x-main.dashboard.ecommerce.metrics.metric-card :title="__('ecommerce.metrics.customers')" value="3,782" change="11.01%" direction="up">
        <x-slot:icon><x-svg.users /></x-slot:icon>
    </x-main.dashboard.ecommerce.metrics.metric-card>

    <x-main.dashboard.ecommerce.metrics.metric-card :title="__('ecommerce.metrics.orders')" value="5,359" change="9.05%" direction="down">
        <x-slot:icon><x-svg.box /></x-slot:icon>
    </x-main.dashboard.ecommerce.metrics.metric-card>
</div>
