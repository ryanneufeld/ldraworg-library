<x-layout.documentation>
    <x-slot:title>{{$document->title}}</x-slot>
    @push('css')
        @vite('resources/css/markdown.css')
    @endpush
    <x-slot:breadcrumbs>
        <x-breadcrumb-item class="active" item="{{$document->title}}" />
    </x-slot>
    <div>
        <x-messgae      
        <x-markdown class="markdown space-y-2">
            {!! $document->content !!}
        </x-markdown>
    </div>
</x-layout.documentation>
