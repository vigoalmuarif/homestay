@php $frameworks = ['Next.js', 'React', 'Vue.js', 'Svelte', 'Angular', 'Nuxt.js', 'Remix', 'Astro', 'Gatsby']; @endphp
<x-ui.field>
    <x-ui.field.label>Searchable Select (Combobox)</x-ui.field.label>
    <x-ui.combobox>
        <x-ui.combobox.input placeholder="Select framework..." show-trigger show-clear />
        <x-ui.combobox.content>
            <x-ui.combobox.empty>No framework found.</x-ui.combobox.empty>
            @foreach ($frameworks as $item)
                <x-ui.combobox.item value="{{ $item }}">{{ $item }}</x-ui.combobox.item>
            @endforeach
        </x-ui.combobox.content>
    </x-ui.combobox>
</x-ui.field>
