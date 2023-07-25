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
    LDraw.org Parts Tracker,
    @foreach($editusers as $u)
        {{$u->realname}},
    @endforeach
  </div>
</div>  
