<x-layout.main>
  <h3 class="ui header">New Parts Tracker Submissions by Week</h3>
  <form class="ui form" name="weekly" action="{{route('tracker.weekly')}}" metohd="POST">
    <div class="ui inline field">
      <label for="order">Display Order:</label>
      <div class="ui action input">
        <select name="order">
          <option value="desc" @selected(!request()->has('order') || request()->input('order') == 'desc')>Newest First</option>
          <option value="asc" @selected(request()->has('order') && request()->input('order') == 'asc')>Oldest First</option>
        </select>
        <button class="ui button" type="submit">Go</button>
      </div>
    </div>
  </form>
  @php($yearweek = 0)
  @foreach ($parts as $part)
    @if (date("oW", strtotime($part->created_at)) != $yearweek)
      @php($yearweek = date("oW", strtotime($part->created_at)))
      @if (!$loop->first)
        </tbody>
      </table>
      @endif
      <h3 class="ui block header">New parts of the week of {{date("F j, Y", strtotime('monday this week', strtotime($part->created_at)))}}</h3>
      <table class="ui celled striped table">
        <thead>
          <tr>
            <th>Image</th>
            <th>Part</th>
            <th>Description</th>
            <th>DAT</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
    @endif
          <x-part.table.row :part="$part" />
    @if ($loop->last)
        </tbody>
      </table>
    @endif
  @endforeach  
</x-layout.main>