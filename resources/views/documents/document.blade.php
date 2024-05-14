<x-layout.documentation>
    <x-slot:title>{{$document->title}}</x-slot>
    @push('css')
        @vite('resources/css/documentation.css')
    @endpush
    <x-slot:breadcrumbs>
        <x-breadcrumb-item class="active" item="{{$document->title}}" />
    </x-slot>
    <div class="p-4 space-y-6">
        <div class="documentation">
            <h1>{{$document->title}}</h1>
        </div>
        <x-message compact type="info">
            <x-slot:header>
                Maintained By: {{$document->maintainer}}<br>
                Revision History:
            </x-slot:>
            <p>
                {!! nl2br(htmlspecialchars($document->revision_history))!!}
            </p>
            <p>
                This is an ratified, official LDraw.org document. 
                Non-adminstrative changes can only be made with the approval of the maintainer.
            </p>
        </x-message>     
        <div class="documentation">
            {!! Blade::render($document->content) !!}
        </div>
    </div>
</x-layout.documentation>
