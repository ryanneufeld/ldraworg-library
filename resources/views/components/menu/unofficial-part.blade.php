@props(['part'])
<div class="ui compact stackable menu">
  <a class="item" title='' href="">Next File</a>
  <a class="item" title='' href="">Prev File</a>
  <a class="item" title="Get a copy of the file" href="/library/unofficial/{{$part->filename}}">Download</a>
  @auth
  @if (Auth::user()->can('create', [\App\Models\Vote::class, $part]) || Auth::user()->can('update', [$part->votes()->firstWhere('user_id', Auth::user()->id)]))
  <a class="item" title="Review/Comment" href="{{ $part->votes()->firstWhere('user_id', Auth::user()->id) ? route('tracker.vote.edit', $part->votes()->firstWhere('user_id', Auth::user()->id)) : route('tracker.vote.create',$part->id) }}">Review/Comment</a>
  @endif
  @endauth
  @if($part->type->format == 'dat')
  @canany(['part.header.edit','part.own.header.edit'])
  <a class="item" title="Edit the file header" href="{{route('tracker.edit', $part->id)}}">Edit</a>
  @endcanany
  @endif
  <a class="item" id="webglview">3D View</a>
</div>
