@props(['title', 'ldbi' => false])
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>{{ $title }}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="shortcut icon" href="{{asset('/images/LDraw_Green_64x64.png')}}" type="image/x-icon">
    <link rel="preload" href="/assets/fomantic/themes/default/assets/fonts/icons.woff2" as="font" type="font/woff2" crossorigin="">
    <link rel="stylesheet" type="text/css" href="/assets/fomantic/semantic.min.css">
    <link rel="stylesheet" type="text/css" href="/assets/css/app.css">
    @if ($ldbi)
    <link rel="stylesheet" type="text/css" href="/assets/css/ldbi.css">
    @endif    
  </head>
  <body>
    {{ $slot ?? '' }}
  </body>
  <script src="/assets/js/jquery-3.5.1.min.js" type="text/javascript"></script> 
  <script src="/assets/fomantic/semantic.min.js" type="text/javascript"></script> 
  <script src="/assets/js/tablesort.js" type="text/javascript"></script> 
  <script src="/assets/js/app.js" type="text/javascript"></script> 
  @if ($ldbi)
    <x-layout.ldbi-scripts />
  @endif    
</html>
