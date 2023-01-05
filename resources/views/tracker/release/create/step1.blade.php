<x-layout.main>
  <form class="ui form" action="{{route('tracker.release.create', ['step' => 2])}}" method="post">
  @csrf
  @forelse ($parts as ['part' => $part, 'release' => $releasable, 'errors' => $errors, 'warnings' => $warnings])
  @if($loop->first)
  <table class="ui celled table">
  <thead>
    <tr>
      <th>Release</th>
      <th>Image</th>
      <th>Name</th>
      <th>Description</th>
      <th>Notes</th>
      <th>Edit</th>
    </tr>
  </thead>
  <tbody>
  @endif  
    <tr @class([
      'red' => !$releasable,
      'green' => $releasable,
    ])>
      <td>
        <div @class(['disabled' => !$releasable, 'field'])>
          <div class="ui toggle checkbox">
            <input type="checkbox" name="ids[]" value="{{$part->id}}" class="hidden" @checked($releasable)>
          </div>
        </div>  
      </td>
      <td><img class="ui image" src="{{asset('images/library/unofficial/' . substr($part->filename, 0, -4) . '_thumb.png')}}" alt='part thumb image' title="part_thumb" ></td>
      <td>{{$part->nameString()}}</td>
      <td><a href="{{route('tracker.show', $part)}}">{{$part->description}}</a></td>
      <td>
        @if(!$part->releasable())
          <div class="ui accordion">
            <div class="title">
              <i class="dropdown icon"></i>
              No certified parents in the parent chain
            </div>
            <div class="content">
              @forelse($part->parents as $p)
              {{$p->nameString()}} <a href="{{route('tracker.show', $p)}}">{{$p->description}}</a> <x-part.status :vote="unserialize($p->vote_summary)" />
              @if (!$loop->last)
              <br>
              @endif
              @empty
              No parents
              @endforelse
            </div>
          </div>
          <br>
        @endif
        @if(!empty($errors))
         {!! nl2br(htmlspecialchars(implode("\n", $errors))) !!}<br>
        @endif
        @if (!empty($warnings))
          {!! nl2br(htmlspecialchars(implode("\n", $warnings))) !!}
        @endif    
      </td>
      <td><a href="{{route('tracker.edit', $part)}}">Edit</a></td>
    </tr>      
  @if($loop->last)
  </tbody>  
  </table>
  <button class="ui button" type="submit">Submit</button>
  @endif
  @empty
  None
  @endforelse
  </form>
</x-layout.main>