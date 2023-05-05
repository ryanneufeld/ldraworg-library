<div class="ui fluid card">
    @isset($image)
      @isset ($link)
        <a class="image" href="{{$link}}"><img src="{{$image}}" /></a>
      @else
        <div class="image"><img src="{{$image}}" /></div>
      @endisset  
    @endisset
    <div class="content">       
      @isset($title)
        <a class="header" href="{{$link}}">{{$title}}</a>
      @endisset
      <div class="description">
        {{$slot}}
      </div>
    </div>
  </div>