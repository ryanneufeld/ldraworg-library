@props(['parts', 'show_obsolete' => 0])
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-2 items-stretch">
    @forelse($parts as $part)
        <x-part.grid.item :$part show_obsolete="{{$show_obsolete}}" />
    @empty
        <p>
            None
        </p>
    @endforelse
</div>