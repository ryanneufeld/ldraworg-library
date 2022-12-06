<div class="ui accordion">
  <div class="title">
    <i class="dropdown icon"></i>
    Creative Commons Attribution License information
  </div>
  <div class="content">
    This part is copyright &copy; {{$part->user->realname}}<br/>
    Licensed under <x-part.license :license="$part->user->license" /><br>
    <br>
    Edits:<br>
    PTadmin (Chris Dee, Orion Pobursky, and/or Steve Bliss), Licensed under <x-part.license license="CC_BY_4" /><br><br>
    @foreach($part->history as $history)
    @if(!$history->user->hasRole('Synthetic User'))
    {{$history->user->realname}}, Licensed under <x-part.license :license="$history->user->license->name" /><br>
    @endif
    @endforeach
  </div>
</div>  
