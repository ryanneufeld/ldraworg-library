<div class="ui compact stackable menu">
  <a class="item" title="Get a copy of the file" href="/library/official/{{$part->filename}}">Download</a>
  @canany(['part.edit.number'])
  <div class="ui dropdown item">
    Admin Actions<i class="dropdown icon"></i>
    <div class="menu">
     @can('part.edit.number')
        <a class="item" title="Renumber part" href="{{route('tracker.move', $part->id)}}">Renumber</a>
      @endcan
    </div>
  </div>
  @endcanany
  <a class="webglview item">3D View</a>
</div>