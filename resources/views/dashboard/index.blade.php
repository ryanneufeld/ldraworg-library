<x-layout.base>
  <x-slot name="title">{{Auth::user()->name}}'s Dashboard</x-slot>
  <x-slot:menu>
    <x-menu.library />
  </x-slot>
<h3 class="ui header">{{Auth::user()->name}}'s Dashboard</h3>
  <div class="ui top attached tabular dashboardmenu menu">
    <a class="item active" data-tab="submits">My Submits</a>
    <a class="item" data-tab="votes">My Votes</a>
    <a class="item" data-tab="tracked">My Tracked Files</a>
  </div>
  <div class="ui bottom attached tab segment active" data-tab="submits">
    <x-part.table :parts="$submits" />
  </div>
  <div class="ui bottom attached tab segment" data-tab="votes">
    <table class="ui celled table">
      <thead>
        <tr>
          <th class="one wide">Image</th>
          <th class="three wide">Part</th>
          <th class="nine wide">Description</th>
          <th class="one wide">DAT</th>
          <th class="two wide">My Vote</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($votes as $vote)
        <tr>
          <td class="center aligned">
            <img class="ui centered image" src="{{asset('images/library/unofficial/' . substr($vote->part->filename, 0, -4) . '_thumb.png')}}" alt='part thumb image' title="part_thumb">
          </td>
          <td>{{ $vote->part->filename }}</td>
          <td>
            <a href="{{ route('tracker.show',$vote->part->id) }}">{{ $vote->part->description }}</a>
          </td>
          <td class="center aligned">
            <a href="{{route('unofficial.download', $vote->part->filename)}}">[DAT]</a>
          </td>
          <td>
            {{$vote->type->name}}
          </td>  
        </tr>
        @endforeach 
      </tbody>
    </table>
  </div>
  <div class="ui bottom attached tab segment" data-tab="tracked">
    <x-part.table :parts="$tracked" />
  </div>
</x-layout.base>