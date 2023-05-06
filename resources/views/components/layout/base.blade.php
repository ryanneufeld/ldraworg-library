@props(['title'])
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>{{ $title }}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @stack('meta')
    <link rel="shortcut icon" href="{{asset('/images/LDraw_Green_64x64.png')}}" type="image/x-icon">
    <link rel="preload" href="/assets/fomantic/themes/default/assets/fonts/icons.woff2" as="font" type="font/woff2" crossorigin="">
    <livewire:styles />
    <link rel="stylesheet" type="text/css" href="/assets/fomantic/semantic.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/app.css">
    @stack('css')
  </head>
  <body>
    <div class="ui container">
      <div class="basic segment">      
        @env('local')
        <div class="ui center aligned icon warning message">
          <i class="exclamation triangle icon"></i>
          <div class="content">
            <div class="header">You are on the BETA LDraw.org Library Site.</div> 
            For the live version go here: <a href="https://library.ldraw.org">http://library.ldraw.org</a>
          </div>
        </div>
        @endenv
      </div>
      
      <div class="ui clearing basic segment logos">
        <a href="https://www.ldraw.org"><img id="main-logo" class="ui left floated image" src="{{asset('/images/banners/main.png')}}"></a>
        @isset($rightlogo)
        <img class="ui right floated image" src="{{$rightlogo}}">
        @endisset
      </div>
      {{$menu ?? ''}}
      <div class="ui hidden fitted clearing divider"></div>
      <div class="ui right floated compact fitted basic segment">
        @auth
          Welcome {{Auth::user()->name}} :: <i class="id card outline icon"></i><a href="{{route('dashboard.index')}}">User Dashboard</a>  
        @endauth
      </div>
      
      <div class="ui basic segment breadcrumb">
        <div class="section"><a href="https://www.ldraw.org">LDraw.org</a></div>
        @isset($breadcrumbs)
          <x-breadcrumb-item item="Library" />
          {{$breadcrumbs}}
        @else   
          <x-breadcrumb-item class="active" item="Library" />
        @endisset
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
    <script src="/assets/js/jquery-3.5.1.min.js" type="text/javascript"></script> 
    <script src="/assets/fomantic/semantic.min.js" type="text/javascript"></script> 
    <script src="/assets/js/tablesort.js" type="text/javascript"></script> 
    <script src="/assets/js/app.js" type="text/javascript"></script>
    @stack('scripts')
    <livewire:scripts />
  </body>
</html>
