@props(['copyuser', 'editusers'])
<div class="ui accordion">
  <div class="title">
    <i class="dropdown icon"></i>
    Creative Commons Attribution License information
  </div>
  <div class="content">
    This part is copyright &copy; {{empty(trim($copyuser->realname)) ? 'LDraw.org' : $copyuser->realname}}<br/>
    Licensed under <x-part.license :license="$copyuser->license->name" /><br>
    <br>
    Edits:<br>
    LDraw.org Parts Tracker Admins/Software, Licensed under <x-part.license license="CC_BY_4" /><br><br>
    @forelse($editusers as $u)
    @if(!$u->hasRole('Synthetic User'))
    {{$u->realname}}, Licensed under <x-part.license :license="$u->license->name" /><br>
    @endif
    @empty
    @endforelse
  </div>
</div>  
