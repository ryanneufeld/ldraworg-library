<div>
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
          {{$partfiles[$index]->getClientOriginalName()}}
        </div>
        <ul class="list">
          @foreach($parterr as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endforeach      
  @enderror

  <form class="ui form" wire:submit.prevent="submit" name="submitform">
    @csrf
    <div class="field">
      <div class="ui file action input">
        <input id="partfiles" type="file" wire:model="partfiles" multiple>
        <label for="partfiles" class="ui button">
          <i class="upload icon"></i>
        </label>
      </div>
    </div>
    <div class="inline fields">
        <div class="inline field">
          <div class="ui checkbox">
            <input type="checkbox" name="replace" wire:model="replace"></TD>
            <label>Replace existing file(s)</label>
          </div>
        </div>
    
        @can('part.submit.fix')
        <div class="inline field">
          <div class="ui checkbox">
            <input type="checkbox" name="officialfix" wire:model="officialfix"></TD>
            <label>New version of official file(s)</label>
          </div>
        </div>
        @endcan
    </div>

    @can('part.submit.proxy')
    <x-form.select-user name="proxy_user_id" wire:model="proxy_user_id" label="Proxy User:" selected="{{$proxy_user_id}}" />
    @endcan
    
    <div class="field">
      <label for="comment">Comments</label>
      <textarea name="comments" wire:model="comments"></textarea>
    </div>

    <div class="field">
      <button class="ui button" type="submit">Submit</button>
      <button class="ui button" type="reset">Reset</button>
    </div>
  </form>    
</div>
