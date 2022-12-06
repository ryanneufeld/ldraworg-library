<x-layout.main>
  <div class="ui large header">Parts Tracker File Submit Form</div>

  <p>
  Use this form to upload <b>new</b> files to the Parts Tracker and to update already-submitted <b>unofficial</b> files.
  </p>
  @error('partfile.*')
    @foreach($errors->get('partfile.*') as $parterr)
      <div class="ui error message">
        <div class="header">
          {{old('partnames')[$loop->index]}}
        </div>
        <ul class="list">
          @foreach($parterr as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endforeach      
  @enderror
  <form class="ui form" method="post" enctype="multipart/form-data" ACTION="{{route('tracker.store')}}" NAME="submitform">
    @csrf
    @isset ($dev)
     <input type="hidden" name="dev" value="1">
    @endisset    
    <div class="grouped fields">
      @foreach(\App\Models\PartType::pluck('format')->unique()->values()->all() as $format)
        <label for="part_type_id">Upload destination (.{{$format}} files)</label>
        @foreach (\App\Models\PartType::where('format', $format)->get() as $part_type)
          @if ($part_type->type == "Shortcut")
            @continue
          @endif
          <div class="field">
            <div class="ui radio checkbox">
              @if ($loop->index == 0 && $loop->parent->index == 0)
                <input type="radio" name="part_type_id" value="{{$part_type->id}}" checked>
              @else
                <input type="radio" name="part_type_id" value="{{$part_type->id}}">
              @endif
              <label>{{$part_type->folder}} ({{$part_type->name}})</label>
            </div>
          </div>
        @endforeach
      @endforeach
    </div>

    <div class="eight wide field">
      <div class="ui file action input">
        <input id="partfile" type="file" name="partfile[]" tabindex="18" accept="application/x-ldraw,image/png,text/plain,.dat,.png" multiple="multiple">
        <label for="partfile" class="ui button">
          <i class="upload icon"></i>
        </label>
      </div>
    </div>

    <div class="inline field">
      <div class="ui checkbox">
        <input type="checkbox" name="replace"></TD>
        <label>Replace existing file(s)</label>
      </div>
    </div>

   @can('part.submit.fix')
    <div class="inline field">
      <div class="ui checkbox">
        <input type="checkbox" name="partfix"></TD>
        <label>New version of official file(s)</label>
      </div>
    </div>
    @endcan

    @can('part.submit.proxy')
    <div class="six wide field">
      <label for="user_id">Author of file(s)</label>
      <select name="user_id">
      @foreach(\App\Models\User::pluck('name', 'id') as $id => $name)
        @if (Auth::user()->id == $id)
          <option value="{{$id}}" selected>{{$name}}</option>
        @else
          <option value="{{$id}}">{{$name}}</option>
        @endif
      @endforeach
      </select>
    </div>
    @else
    <div class="field">
      <input type="hidden" name="user_id" value="{{Auth::user()->id}}">
    </div>
    @endcan
{{--
 
     <input type="hidden" name="user_id" value="1">
--}}
    <div class="field">
      <label for="comment">Comments</label>
      <textarea name="comment" rows="8"></textarea>
    </div>

    <div class="field">
      <button class="ui button" type="submit" tabindex=20>Submit</button>
      <button class="ui button" type="reset"  tabindex=21>Reset</button>
    </div>
  </form>

  <p>To submit a fix for an <b>existing file</b>,  email the file to 
  <a href="mailto:parts@ldraw.org">parts@ldraw.org</a>, and it will be manually posted to the tracker.
  </p>
  <p>
  You must be registered as an LDraw.org user and a member of the Submitter group to use this form.  
  To register as an LDraw user go to the <A HREF="http://www.ldraw.org/user.php?op=check_age&module=NS-NewUser">
  LDraw.org registration area</A>. 
  To become a member of the Submitter group please email 
  <A HREF="mailto:parts@ldraw.org">parts@ldraw.org</A>, including your LDraw username.
  </p>
  <p>
  Or you can submit your files to
  <A HREF="mailto:parts@ldraw.org">parts@ldraw.org</A>, and they will be manually posted.
  </p>
  <p>
  Uploaded files should appear almost immediately in the Parts Tracker list.
  </p>
</x-layout.main>

