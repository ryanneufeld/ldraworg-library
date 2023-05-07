<x-layout.tracker>
  <x-slot:title>LDraw.org Library Updates</x-slot>
  @if($latest)
    <x-release.table :release="$releases"/>   
  @else  
    @foreach($releases as $release)
      @if ($loop->first)
        <x-release.table :release="$release"/>   
      @else
        <x-release.table :release="$release" current="0"/>   
      @endif
      <br>
    @endforeach
  @endif  
</x-layout.tracker>