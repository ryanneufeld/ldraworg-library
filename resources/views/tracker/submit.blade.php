<x-layout.main>
  <div class="ui large header">Parts Tracker File Submit Form</div>

  <p>
  Use this form to upload <b>new</b> files to the Parts Tracker and to update already-submitted <b>unofficial</b> files.
  </p>
  @if($errors->hasAny(['comment','user_id','partfile'])) 
    <div class="ui error message">
      @foreach(['comment','user_id','partfile'] as $errorfield)
        @error($errorfield)
        {{implode("<br/>", $errors->get($errorfield))}}@if(!$loop->last)<br/>@endif
        @enderror
      @endforeach  
    </div>
  @endif
  @error('partfile.*')
    @foreach($errors->get('partfile.*') as $index => $parterr)
      <div class="ui error message">
        <div class="header">
          {{old('partnames')[$index]}}
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
    <x-type.radio label="Upload destination" />

    <div class="eight wide field">
      <div class="ui file action input">
        <input id="partfile" type="file" name="partfile[]" tabindex="18" multiple="multiple">
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
    <div class="eight wide field">
      <label for="user_id">Author of file(s)</label>
      <select class="ui clearable dropdown" name="user_id">
      @foreach(\App\Models\User::orderBy('realname')->get() as $user)
        <option value="{{$user->id}}" @selected(Auth::user()->id == $user->id)>{{$user->realname}} [{{$user->name}}]</option>
      @endforeach
      </select>
    </div>
    @else
    <div class="field">
      <input type="hidden" name="user_id" value="{{Auth::user()->id}}">
    </div>
    @endcan
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

