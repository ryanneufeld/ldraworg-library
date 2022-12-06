<x-layout.main>
  <x-slot name="title">
    Unofficial Part List
  </x-slot>
  <table class="ui striped celled sortable table">
    <thead>
     <tr>
       <th>Type</th>
       <th class="eight wide">Description</th>
       <th>Name</th>
       <th>Status</th>
     </tr>
    </thead> 
    <tbody>
      @foreach ($parts as $part)
      <tr>
        <td>{{ $part->type->name }}</td>
        <td><a href="{{route('tracker.show', $part->id)}}">{{$part->description}}</a></td>
        <td>{{ $part->filename }}</td>
        <td><x-part.status :part="$part" status_text="1" /><td>
        <td></td>
      </tr>
      @endforeach
    </tbody>  
  </table>
</x-layout.main>