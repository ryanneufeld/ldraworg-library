<div>
    {{ $sets->onEachSide(1)->links('livewire.paginate-menu') }}
    <table class="ui striped stackable table">
        <thead>
            <tr>
                <th></th>
                <th>Set Number</th>
                <th>Set Name</th>
                <th>Year</th>
                <th>Number of Models</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sets as $set)
                <tr>
                    <td><img class="ui mini image" src="{{$set->rb_url}}"</td>
                    <td><a href="{{route('omr.sets.show', $set)}}">{{$set->number}}</a></td>
                    <td>{{$set->name}}</td>
                    <td>{{$set->year}}</td>
                    <td>{{$set->models->count()}}</td>
                </tr>    
            @endforeach
        </tbody>
    </table>    
    {{ $sets->onEachSide(1)->links('livewire.paginate-menu') }}
  </div>
