@props(['title' => '', 'ldbi' => false])
<x-layout.base title="{{$title}}" ldbi="{{$ldbi}}">
  <div class="ui container">
    <div class="basic segment">      
{{--
      <div class="ui center aligned icon warning message">
        <i class="exclamation triangle icon"></i>
        <div class="content">
          <div class="header">You are on the BETA Parts Tracker.</div> 
          For the live version go here: <a href="https://www.ldraw.org/library/tracker">http://www.ldraw.org/library/tracker</a>
        </div>
      </div>
--}}
    </div>
    
    <div class="ui clearing basic segment logos">
      <a href="https://www.ldraw.org"><img id="main-logo" class="ui left floated image" src="{{asset('/images/banners/main.png')}}"></a>
      <img class="ui right floated image" src="{{asset('/images/banners/tracker-trimmed.png')}}">
    </div>
    <x-menu.tracker />
    <div class="ui hidden fitted clearing divider"></div>
    <div class="ui right floated compact fitted basic segment">
      @auth
        Welcome {{Auth::user()->name}} :: <i class="id card outline icon"></i><a href="{{route('dashboard.index')}}">User Dashboard</a>  
      @endauth
    </div>
    
    <div class="ui basic segment breadcrumb">
      <div class="section"><a href="https://www.ldraw.org">LDraw.org</a></div>
      <div class="divider"><i class="angle double right icon"></i></div>
      <div class="active section">Parts Tracker</div>
    </div>
    

    <div class="ui segment main-content">
       {{ $slot ?? '' }}
    </div>
  

    <div class="ui basic segment footer">
      <p>
        Website copyright &copy;2003-{{date_format(now(),"Y")}} LDraw.org, see 
        <a href="/legal-info">Legal Info</a> for details.
      </p>
      <p>
        LDraw is a completely unofficial, community run free CAD system which 
        represents official parts produced by the LEGO company.
      </p>
      <p>
        LDraw&trade; is a trademark owned and licensed by the Estate of James Jessiman<br>
        LEGO&reg; is a registered trademark of the LEGO Group, which does not sponsor, 
        endorse, or authorize this site. Visit the official Lego website at 
        <a href="https://www.lego.com" target="_blank">http://www.lego.com</a>
      </p>
    </div>
  </div>
</x-layout.base>
