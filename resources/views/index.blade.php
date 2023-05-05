<x-layout.base>
  <x-slot:title>
    LDraw.org Library Main
  </x-slot>
  <div class="ui segment">
    Welcome to the LDraw.org library. Here you will find the Parts Tracker, parts updates, 
    documentation for the LDraw file format and libary, and the Official Model Repository
  </div>
  
  <div class="ui two stackable cards">
    <x-layout.home-card image="{{asset('/images/cards/tracker.png')}}" link="{{route('tracker.main')}}">
      <x-slot:title>Parts Tracker</x-slot>
      The Parts Tracker is the system we use to submit files to the LDraw.org Part Library.
      The Parts Tracker allows users to download unofficial parts, submit new files, update existing unofficial files, and review unofficial parts.
    </x-layout.home-card>  
    <x-layout.home-card image="{{asset('/images/cards/updates.png')}}" link="{{route('part-update.index', ['latest'])}}">
      <x-slot:title>Parts Update 2023-02</x-slot>
      This update adds 279 new files to the core library, including 147 new parts and 2 new primitives.
    </x-layout.home-card>  
  </div>
  
  <div class="ui two stackable cards">
    <x-layout.home-card image="{{asset('/images/cards/doc.png')}}" link="https://www.ldraw.org/docs-main.html">
      <x-slot:title>Documentation</x-slot>
      The reference docmentation for the LDraw File Format and LDraw.org Official Parts Library.
    </x-layout.home-card>  
    <x-layout.home-card image="{{asset('/images/cards/omr.png')}}" link="https://omr.ldraw.org/">
      <x-slot:title>Official Model Repository</x-slot>
      The Official Model Repository or OMR is a library of official LEGO&reg; sets that have been
      created in LDRaw format.
    </x-layout.home-card>  
  </div>
</x-layout.base>
