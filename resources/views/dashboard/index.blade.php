<x-layout.main>
  <x-slot name="title">{{Auth::user()->name}}'s Dashboard</x-slot>
  <h3 class="ui header">{{Auth::user()->name}}'s Dashboard</h3>
  <div class="ui two column grid">
    <div class="column">
      <h4 class="ui header">My Submits</h4>
      <div class="ui scrolling segment">
      <x-part.table unofficial="1" :parts="$submits" none="None" />
      </div>      
    </div>
    <div class="column">
      <h4 class="ui header">My Votes</h4>    
      <div class="ui scrolling segment">
        @if (!empty($votes))
          <table class="ui striped celled sortable table">
            <thead>
              <tr>
                <th>Part</th>
                <th>Description</th>
                <th>Status</th>
                <th>My Vote</th>
              </tr>
            </thead>
            <tbody>
              @foreach($votes as $vote)
              <tr>
                <td>{{$vote->part->filename}}</td>
                <td><a href="{{ route('tracker.show',$vote->part->id) }}">{{$vote->part->description}}</a></td>
                <td><x-part.status :vote="$vote->part->vote_summary" /></td>
                <td>{{ $vote->type->name }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        @else
          No votes found
        @endif        
      </div>      
    </div>
  </div>
</x-layout.main>