<x-slot:title>
     Parts Tracker File Submit Form
</x-slot>
<x-slot:breadcrumbs>
    <x-breadcrumb-item class="active" item="Library General Settings" />
</x-slot>    
<div>
    <div class="text-2xl font-bold">
        Update Library General Settings
    </div>
    <form wire:submit="saveSettings">
        {{ $this->form }}

        <x-filament::button type="submit">
            <x-filament::loading-indicator wire:loading class="h-5 w-5" />
            Submit
        </x-filament::button>
    </form>
</div>