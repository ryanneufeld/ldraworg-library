@props(['title', 'styles', 'scripts'])
<!DOCTYPE html>
<html lang="en">
  <head>
    <title>{{ $title }}</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="shortcut icon" href="{{asset('/images/logos/favicons/LDraw_Green_64x64.png')}}" type="image/x-icon">
    @foreach ($styles as $sheet)
      <link rel="stylesheet" href="{{ mix('css/' . $sheet . '.css') }}" type="text/css">
    @endforeach
  </head>
  <body>
    {{ $slot ?? '' }}
    @foreach ($scripts as $script)
      <script type="text/javascript" src="{{ mix('js/' . $script . '.js') }}" defer></script>
    @endforeach
  </body>
</html>
