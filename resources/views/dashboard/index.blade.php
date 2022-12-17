<x-layout.main>
  <x-slot name="title">{{Auth::user()->name}}'s Dashboard</x-slot>
  <a href="{{route('dashboard.submits')}}">My Submits</a> - A listing of all unofficial parts currently on the Parts Tracker that you have submitted<br>
  <a href="{{ route('dashboard.votes') }}">My Votes</a> - A listing of all unofficial parts currently on the Parts Tracker where you have an active vote<br>
  <a href="{{-- route('dashboard.notifications') --}}">My Notifications</a> - A listing of all unofficial parts currently on the Parts Tracker where you have posted any event (vote, submit, comment)<br>
</x-layout.main>