<x-layout.main>
  <form class="ui form" action="{{route('tracker.doupdatemissing', $part)}}" method="post">
    @csrf
    @method('PUT')
    <input type="hidden" name="part_id" value="{{$part->id}}">
    <x-form.dropdown name="new_part_id" class="search" :model="[\App\Models\Part::class, 'filename']" selected="{{old('new_part_id')}}" />
    <div class="field">
      <button class="ui button" type="submit">Submit</button>
    </div>
  </form>
</x-layout.main>