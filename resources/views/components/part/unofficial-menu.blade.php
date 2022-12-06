<div class="ui compact stackable menu">
  <a class="item" title='' href="">Next File</a>
  <a class="item" title='' href="">Prev File</a>
  <a class="item" title="Get a copy of the file" href="/library/unofficial/{{$part->filename}}">Download</a>
  @if (Auth::check())
  @if (Auth::user()->can('create', [\App\Models\Vote::class, $part]) || Auth::user()->can('update', Auth::user()->votes()->firstWhere('user_id', Auth::user()->id)))
  <a class="item" title="Review/Comment" href="{{ Auth::user()->votes()->firstWhere('part_id',$part->id) ? route('tracker.vote.edit', Auth::user()->votes()->firstWhere('part_id',$part->id)->id) : route('tracker.vote.create',$part->id) }}">Review/Comment</a>
  @endif
  @canany(['part.header.edit','part.own.header.edit'])
  <a class="item" title="Edit the file header.  Admins only." href="">Edit</a>
  @endcanany
  @endif
  <a class="item" id="webglview">3D View</a>
</div>
