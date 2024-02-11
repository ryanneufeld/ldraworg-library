<x-slot:title>
    Part Search
</x-slot>
<x-slot:breadcrumbs>
    <x-breadcrumb-item class="active" item="Part Search" />
</x-slot>    
<div>
    <div class="text-3xl font-bold">
        <span>Part Search</span>
    </div>
    <form wire:submit="doSearch">
        {{ $this->form }}

        <x-filament::button type="submit">
            Submit
        </x-filament::button>
    </form>
    <livewire:tables.search-parts 
        unofficial
        wire:model="data"
    />
    <livewire:tables.search-parts 
        wire:model="data"
    />
</div>
