<x-layout.tracker>
  @if ($errors->any())
  <div class="ui error message">
    <ul class="ui list">
    @foreach($errors->all() as $errorfield)
      <li>{{$errorfield}}</li>
    @endforeach
    </ul>    
  </div>
  @endif
  <form class="ui form" enctype="multipart/form-data" action="{{route('tracker.release.create2')}}" method="POST">
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
        <div class="field">
          <div class="ui toggle checkbox">
            <input type="checkbox" name="ids[]" value="{{$part->id}}" class="hidden" @checked($releasable)>
          </div>
        </div>  
      </td>
      <td><img class="ui image" src="{{asset('images/library/unofficial/' . substr($part->filename, 0, -4) . '_thumb.png')}}" alt='part thumb image' title="part_thumb" ></td>
      <td>{{$part->name()}}</td>
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
              {{$p->name()}} <a href="{{route('tracker.show', $p)}}">{{$p->description}}</a> <x-part.status :vote="$p->vote_summary" />
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
  <div class="field">
    <label for="ldrawfiles">Files for the base (ldraw) folder (Note: No validation will be done these files)</label>
    <div class="ui file input">
      <input name="ldrawfiles[]" type="file" multiple="multiple">
    </div>
  </div>
  <button class="ui button" type="submit">Submit</button>
  @endif
  @empty
  None
  @endforelse
  </form>
</x-layout.tracker>