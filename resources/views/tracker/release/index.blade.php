<x-layout.main>
  @if($latest)
    <x-release.table :release="$releases"/>   
  @else  
    @foreach($releases as $release)
      @if ($loop->first)
        <x-release.table :release="$release"/>   
      @else
        <x-release.table :release="$release" current="0"/>   
      @endif
    @endforeach
  @endif  
</x-layout.main>