@props(['data' => null])
@php
    $orders = $data ?? (include resource_path('views/components/main/tables/basic-tables/table-one/data.php'))['tableData'];
    $colorMap = ['Active' => 'success', 'Pending' => 'warning', 'Cancel' => 'error'];
    $headClass = 'px-5 py-3 text-start text-xs font-medium text-gray-500 sm:px-6 dark:text-gray-400';
@endphp
<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-white/5 dark:bg-white/3']) }}>
    <div class="max-w-full overflow-x-auto">
        <table class="min-w-full">
            <thead class="border-b border-gray-100 dark:border-white/5">
                <tr>
                    @foreach (['User', 'Project Name', 'Team', 'Status', 'Budget'] as $heading)
                        <th class="{{ $headClass }}">{{ $heading }}</th>
                    @endforeach
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                @foreach ($orders as $order)
                    @php
                        $initials = mb_substr(implode('', array_map(fn ($n) => mb_substr($n, 0, 1), explode(' ', $order['user']['name']))), 0, 2);
                    @endphp
                    <tr class="transition hover:bg-gray-50/50 dark:hover:bg-white/2">
                        <td class="px-5 py-4 text-start sm:px-6">
                            <div class="flex items-center gap-3">
                                <x-ui.avatar size="default">
                                    <x-ui.avatar.image :src="$order['user']['image']" :alt="$order['user']['name']" />
                                    <x-ui.avatar.fallback>{{ $initials }}</x-ui.avatar.fallback>
                                </x-ui.avatar>
                                <div>
                                    <span class="block text-sm font-medium text-gray-800 dark:text-white/90">{{ $order['user']['name'] }}</span>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $order['user']['role'] }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-start text-sm text-gray-500 sm:px-6 dark:text-gray-400">{{ $order['projectName'] }}</td>
                        <td class="px-5 py-4 text-start text-sm text-gray-500 sm:px-6 dark:text-gray-400">
                            <x-ui.avatar.group class="-space-x-2">
                                @foreach ($order['team']['images'] as $index => $image)
                                    <x-ui.avatar size="xs">
                                        <x-ui.avatar.image :src="$image" :alt="'Team member ' . ($index + 1)" />
                                        <x-ui.avatar.fallback>{{ $index + 1 }}</x-ui.avatar.fallback>
                                    </x-ui.avatar>
                                @endforeach
                            </x-ui.avatar.group>
                        </td>
                        <td class="px-5 py-4 text-start text-sm text-gray-500 sm:px-6 dark:text-gray-400">
                            <x-ui.badge size="sm" :color="$colorMap[$order['status']] ?? 'gray'">{{ $order['status'] }}</x-ui.badge>
                        </td>
                        <td class="px-5 py-4 text-start text-sm text-gray-500 sm:px-6 dark:text-gray-400">{{ $order['budget'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
