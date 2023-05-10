<x-layout.tracker>
    <x-slot:title>Parts Tracker Review Summaries</x-slot>
    <x-slot:breadcrumbs>
        <x-breadcrumb-item class="active" item="Review Summaries" />
    </x-slot>    
    
    <h2 class="ui header">Parts Tracker Review Summaries</h2>
    <form class="ui form" action="{{route('admin.review-summaries.store')}}" method="POST">
        @csrf
        <div class="field">
            <label for="header">Create New Summary:</label>
            <div class="ui action input">
                <input type="text" name="header" placeholder="Summary Header">
                <button class="ui button" type="submit">Create</button>
            </div>
        </div> 
    </form>
      <table class="ui celled table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        @foreach($summaries as $summary)
          <tr>
            <td>{{$summary->header}}</td>
            <td>
              <form action={{route('admin.review-summaries.destroy', $summary)}} method="post">
                  @csrf
                  @method('delete')
                  <a class="ui button" href="{{route('admin.review-summaries.edit', $summary)}}">Edit</a>
                  <button class="ui button" type="submit" href="{{route('admin.review-summaries.destroy', $summary)}}">Delete</button>
              </form>  
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    
</x-layout.tracker>