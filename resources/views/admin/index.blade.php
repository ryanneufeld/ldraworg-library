<x-layout.admin>
    <x-slot name="title">Admin Dashboard</x-slot>
    <h3 class="text-2xl font-bold">Admin Dashboard</h3>
    <livewire:tables.part-ready-for-admin-table />
    <livewire:tables.parts-flagged-for-deletion-table />
    <livewire:tables.official-parts-with-errors-table />
</x-layout.admin>