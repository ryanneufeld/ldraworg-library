<x-layout.documentation>
    <x-slot:title>Documentation Index</x-slot>
    @foreach(\App\Models\Document\Document::where('published', true)->get()->sortBy('category.order')->sortBy('order')->groupBy('category.category') as $category => $docs)
        <div class="font-bold text-xl">{{$category}}</div>
        @foreach ($docs as $doc)
            <a href="{{route('documentation.show', $doc)}}">{{$doc->title}}</a>
        @endforeach
    @endforeach
</x-layout.documentation>