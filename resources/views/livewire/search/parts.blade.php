<div>
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
