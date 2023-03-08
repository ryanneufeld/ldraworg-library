@props(['part'])
<div class="ui compact stackable menu">
  @if($part->isUnofficial())
  <a class="item" title="Get a copy of the file" href="{{route('unofficial.download', $part->filename)}}">
  @else
  <a class="item" title="Get a copy of the file" href="{{route('official.download', $part->filename)}}">
  @endif
    Download
  </a>  
  @auth
  @if ($part->isUnofficial() && (Auth::user()->can('create', [\App\Models\Vote::class, $part]) || Auth::user()->can('update', [$part->votes()->firstWhere('user_id', Auth::user()->id)])))
  <a class="item" title="Review/Comment" href="{{ $part->votes()->firstWhere('user_id', Auth::user()->id) ? route('tracker.vote.edit', $part->votes()->firstWhere('user_id', Auth::user()->id)) : route('tracker.vote.create',$part->id) }}">Review/Comment</a>
  @endif
  @endauth
  @canany(['part.edit.header','part.own.edit.header','part.edit.number','part.delete'])
  <div class="ui dropdown item">
    Admin Actions<i class="dropdown icon"></i>
    <div class="menu">
      @if($part->isUnofficial() && $part->type->format == 'dat')
      @canany(['part.edit.header','part.own.edit.header'])
        <a class="item" title="Edit the file header" href="{{route('tracker.editheader', $part->id)}}">Edit Header</a>
      @endcanany
      @endif
      @can('part.edit.header')
        <a class="item" title="Regenerate Part Image" href="{{route('tracker.updateimage', $part->id)}}">Regenerate Image</a>
      @endcan  
      @can('part.edit.number')
        <a class="item" title="Renumber part" href="{{route('tracker.move', $part->id)}}">Renumber</a>
      @endcan
      @if($part->isUnofficial())
      @can('part.delete')
        <a class="item" title="Delete Part" href="" onclick="">Delete</a>
      @endcan
      @endif
    </div>
  </div>
  @endcanany
  <a class="webglview item">3D View</a>
</div>
