<x-ui.field>
    <x-ui.field.label>Select Input</x-ui.field.label>
    <x-ui.select>
        <x-ui.select.trigger>
            <x-ui.select.value placeholder="Select Option" />
        </x-ui.select.trigger>
        <x-ui.select.content>
            @foreach (range(1, 5) as $n)
                <x-ui.select.item value="option-{{ $n }}">Option {{ $n }}</x-ui.select.item>
            @endforeach
        </x-ui.select.content>
    </x-ui.select>
</x-ui.field>
