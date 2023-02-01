@props(['part'])
<div class="ui compact stackable menu">
  <a class="item" title="Get a copy of the file" href="/library/unofficial/{{$part->filename}}">Download</a>
  @auth
  @if (Auth::user()->can('create', [\App\Models\Vote::class, $part]) || Auth::user()->can('update', [$part->votes()->firstWhere('user_id', Auth::user()->id)]))
  <a class="item" title="Review/Comment" href="{{ $part->votes()->firstWhere('user_id', Auth::user()->id) ? route('tracker.vote.edit', $part->votes()->firstWhere('user_id', Auth::user()->id)) : route('tracker.vote.create',$part->id) }}">Review/Comment</a>
  @endif
  @endauth
  @canany(['part.header.edit','part.own.header.edit','part.edit.number','part.delete'])
  <div class="ui dropdown item">
    Admin Actions<i class="dropdown icon"></i>
    <div class="menu">
      @if($part->type->format == 'dat')
      @canany(['part.header.edit','part.own.header.edit'])
        <a class="item" title="Edit the file header" href="{{route('tracker.editheader', $part->id)}}">Edit Header</a>
      @endcanany
      @endif
      @can('part.edit.number')
        <a class="item" title="Renumber part" href="{{route('tracker.move', $part->id)}}">Renumber</a>
      @endcan
      @can('part.delete')
        <a class="item" title="Delete Part" href="" onclick="">Delete</a>
      @endcan
    </div>
  </div>
  @endcanany
  <a class="webglview item">3D View</a>
</div>
