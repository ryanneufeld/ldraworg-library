<x-layout.tracker>
  <x-slot:title>Create Release Step 1</x-slot>
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
  @forelse ($parts as ['part' => $part, 'release' => $releasable, 'errors' => $errors, 'warnings' => $warnings, 'uncertsubparts' => $uncertsubparts, 'certparents' => $certparents])
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
        @if($uncertsubparts || !$certparents)
          <div class="ui accordion">
            <div class="title">
              <i class="dropdown icon"></i>
              @if($uncertsubparts)
                Uncertified subparts
              @elseif(!$certparents)
                No certified parents
              @endif  
            </div>
            <div class="content">
              @if($uncertsubparts)
                @foreach($part->subparts()->unofficial()->get() as $p)
                  {{$p->name()}} <a href="{{route('tracker.show', $p)}}">{{$p->description}}</a> <x-part.status :part="$p" />
                  @if (!$loop->last)
                    <br>
                  @endif
                @endforeach
              @elseif(!$certparents)
                @forelse($part->parents()->unofficial()->get() as $p)
                  {{$p->name()}} <a href="{{route('tracker.show', $p)}}">{{$p->description}}</a> <x-part.status :part="$p" />
                  @if (!$loop->last)
                    <br>
                  @endif
                @empty
                  No parents
                @endforelse
              @endif  
            </div>
          </div>
          <br>
        @endif
        @isset($errors)
          @foreach($errors as $error)
            {{$error}}<br>
          @endforeach  
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