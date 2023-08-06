<x-layout.tracker>
  <x-slot:title>Parts Tracker File Submit Form</x-slot>
  <x-slot:breadcrumbs>
    <x-breadcrumb-item class="active" item="Submit" />
  </x-slot>    
  <div class="ui large header">Parts Tracker File Submit Form</div>

  <p>
  Use this form to upload <b>new</b> files to the Parts Tracker and to update already-submitted <b>unofficial</b> files.
  </p>
  @if($errors->hasAny(['comments','proxy_user_id','partfiles'])) 
    <div class="ui error message">
      @foreach(['comments','proxy_user_id','partfiles'] as $errorfield)
        @error($errorfield)
        {{implode("<br/>", $errors->get($errorfield))}}@if(!$loop->last)<br/>@endif
        @enderror
      @endforeach  
    </div>
  @endif
  @error('partfiles.*')
    @foreach(Arr::undot($errors->get('partfiles.*'))['partfiles'] as $index => $parterr)
      <div class="ui error message">
        <div class="header">
          {{$errors->get('partnames')[0][$index]}}
        </div>
        <ul class="list">
          @foreach($parterr as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endforeach      
  @enderror

  <form class="ui form" method="post" action="{{route('tracker.store')}}" enctype="multipart/form-data" name="submitform">
    @csrf
    <div class="field">
      <div class="ui file action input">
        <input id="partfiles" type="file" name="partfiles[]" multiple>
        <label for="partfiles" class="ui button">
          <i class="upload icon"></i>
        </label>
      </div>
    </div>
    <div class="inline fields">
        <div class="inline field">
          <div class="ui checkbox">
            <input type="checkbox" name="replace">
            <label>Replace existing file(s)</label>
          </div>
        </div>
    
        @can('part.submit.fix')
        <div class="inline field">
          <div class="ui checkbox">
            <input type="checkbox" name="officialfix">
            <label>New version of official file(s)</label>
          </div>
        </div>
        @endcan
    </div>

    @can('part.submit.proxy')
    <x-form.select-user name="proxy_user_id" label="Proxy User:" selected="{{old('proxy_user_id') ?? Auth::user()->id}}" />
    @else
    <input type="hidden" name="proxy_user_id" value="{{Auth::user()->id}}">    
    @endcan
    
    <div class="field">
      <label for="comment">Comments</label>
      <textarea name="comments">{{old('comments')}}</textarea>
    </div>

    <div class="field">
      <button class="ui button" type="submit">Submit</button>
      <button class="ui button" type="reset">Reset</button>
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
</x-layout.tracker>

