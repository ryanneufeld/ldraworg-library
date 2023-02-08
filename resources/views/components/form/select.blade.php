@props(['options', 'selected' => null, 'placeholder' => null])
<select {{$attributes}}>
  @isset($placeholder)
  <option value="">{{$placeholder}}</option>
  @endisset
  @foreach($options as $value => $text)
    <option value="{{$value}}" @selected($value == $selected)>{{$text}}</option>
  @endforeach
</select>  