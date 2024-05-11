<x-layout.documentation>
    <x-slot:title>{{$document->title}}</x-slot>
    @push('css')
        @vite('resources/css/markdown.css')
    @endpush
    <x-slot:breadcrumbs>
        <x-breadcrumb-item class="active" item="{{$document->title}}" />
    </x-slot>
    <div>
        <div class="markdown"><h1>{{$document->title}}</h1></div>
        <x-message compact type="info">
            <x-slot:header>
                Maintained By: {{$document->maintainer}}<br>
                Revision History:
            </x-slot:>
            {!! nl2br(htmlspecialchars($document->revision_history))!!}<br>
            This is an ratified, official LDraw.org document. 
            Non-adminstrative changes can only be made with the approval of the maintainer.
        </x-message>     
        <x-markdown class="markdown space-y-2">
            {!! $document->content !!}
        </x-markdown>
    </div>
</x-layout.documentation>
