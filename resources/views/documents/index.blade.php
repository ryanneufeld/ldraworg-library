<x-layout.documentation>
    <x-slot:title>Documentation Index</x-slot>
    @foreach(\App\Models\Document\Document::where('published', true)->get()->sortBy('category.order')->groupBy('category.category') as $category => $docs)
        <div class="font-bold text-xl">{{$category}}</div>
        @foreach ($docs->sortBy('order') as $doc)
            @if(!$doc->restricted || Auth::user()->can('documents.restricted.view'))
                <a href="{{route('documentation.show', $doc)}}">{{$doc->title}}</a>
            @endif
        @endforeach
    @endforeach
</x-layout.documentation>