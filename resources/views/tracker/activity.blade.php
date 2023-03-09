<x-layout.main>
  <x-slot name="title">Recent Activity</x-slot>
  <div class="ui right floated right aligned basic segment">
    Server Time: {{date('Y-m-d H:i:s')}}<br/>
    <x-part.unofficial-part-count />
  </div>

  <h3 class="ui header">Parts Tracker Activity Log</h3>
  <div class="ui tiny compact menu">
    @if(!$events->onFirstPage())
    <a class="item" HREF="{{$events->previousPageUrl()}}">Prior</a>
    @endif
    @if($events->hasMorePages())
    <a class="item" HREF="{{$events->nextPageUrl()}}">Next</a>
    @endif
    @if(!$events->onFirstPage())
    <a class="item" HREF="{{route('tracker.activity')}}">Newest</a>
    @endif
  </div>
  {{--
  <div>
    <form class="ui form" action="" method="GET">
      <x-form.select :options="[20, 40, 80, 100]" />
    </form>
  </div>
  --}}
  <x-event.table :events="$events" />   
  <div class="ui tiny compact menu">
    @if(!$events->onFirstPage())
    <a class="item" HREF="{{$events->previousPageUrl()}}">Prior</a>
    @endif
    @if($events->hasMorePages())
    <a class="item" HREF="{{$events->nextPageUrl()}}">Next</a>
    @endif
    @if(!$events->onFirstPage())
    <a class="item" HREF="{{route('tracker.activity')}}">Newest</a>
    @endif
  </div>  
</x-layout.main>