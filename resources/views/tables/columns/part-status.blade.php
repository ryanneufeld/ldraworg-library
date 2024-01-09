<div>
    @isset($getRecord()->part)
      @if($getRecord()->part->isUnofficial())
        <x-part.status :part="$getRecord()->part"/>
      @else
        {{$getRecord()->release->name}} Release
      @endif
    @else
      Removed  
    @endisset  
</div>
