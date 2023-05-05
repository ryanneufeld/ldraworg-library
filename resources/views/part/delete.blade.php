<x-layout.tracker>
  <x-slot name="title">Delete {{$part->filename}}</x-slot>
  <x-slot:breadcrumbs>
    <x-breadcrumb-item class="active" item="Delete Part" />
  </x-slot>    
  <h3 class="ui header">Delete {{$part->filename}}</h3>
  <form class="ui form" action="{{route('tracker.destroy', $part)}}" method="post">
    @csrf
    @method('DELETE')
    <p>Are you sure you want to delete {{$part->filename}}? This action cannot be easily undone.</p>
    <div class="field">
      <button class="ui button" type="submit">Yes</button><button class="ui button" onclick="window.history.go(-1); return false;">Back</button>
    </div>
  </form>
</x-layout.tracker>