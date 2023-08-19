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
  @if($part->hasPatterns() || $part->hasComposites() || $part->hasStickerShortcuts())
  <a class="item" title="View Patterns/Shortcuts" href="{{route('search.suffix', ['s' => $part->basepart()])}}">View Patterns/Shortcuts</a>
  @endif
  @canany(['part.edit.header','part.own.edit.header','part.edit.number','part.delete'])
  <div class="ui dropdown item">
    Admin Actions<i class="dropdown icon"></i>
    <div class="menu">
      @if($part->isUnofficial() && $part->type->folder == 'parts/' && $part->descendantsAndSelf->where('vote_sort', '>', 2)->count() == 0)
      @can('vote.admincertify')
        <a class="item" title="Admin Certify All" href="{{route('tracker.vote.adminquickvote', $part)}}">Admin Certify All</a>
      @endcan
      @endif
      @if($part->isUnofficial() && $part->type->format == 'dat')
      @canany(['part.edit.header','part.own.edit.header'])
        <a class="item" title="Edit the file header" href="{{route('tracker.edit', $part)}}">Edit Header</a>
      @endcanany
      @endif
      @can('part.edit.header')
        <a class="updateimage item" title="Regenerate Part Image" href="{{route('tracker.updateimage', $part)}}">Regenerate Image</a>
        <a class="updatesubpart item" title="Update subparts" href="{{route('tracker.updatesubparts', $part)}}">Update Subparts</a>
      @endcan  
      @can('part.edit.number')
        <a class="item" title="Renumber part" href="{{route('tracker.move.edit', $part)}}">Renumber</a>
      @endcan
      @if($part->isUnofficial())
      @can('part.delete')
        <a class="item" title="Delete Part" href="{{route('tracker.delete', $part)}}">Delete</a>
      @endcan
      @endif
    </div>
  </div>
  @endcanany
  <a class="webglview item">3D View</a>
</div>
