<x-layout.main>
  <x-slot name="title">
    Parts Tracker Main
  </x-slot>
    <p>
      The Parts Tracker is the system we use to submit files to the LDraw.org Part Library.
      The Parts Tracker allows users to download unofficial parts, submit new files, update existing unofficial files, and review unofficial parts.
    </p>
    <div class="ui two column grid">
      <div class="column">
        <h5 class="ui header"><A href="">Parts Tracker Policies</a></h5>
        <p>
          Policies for users of the Parts Tracker.
        </p>
  
        <h5 class="ui header"><a href="{{route('tracker.activity')}}">Activity Log</a></b></h5>
        <p>
          View the recent submissions, reviews, and admin actions on the tracker.
          Very useful for keeping an eye on the Parts Tracker.
        </p>
        @can('create',\App\Models\Part::class)
        <h5 class="ui header"><a href="{{route('tracker.submit')}}">Submit New Parts</a></h5>
        <p>
          To submit parts, visit the part submission page and input your parts. Your parts will
          be validated and, if they pass, be added to the Parts Tracker for review.
        </p>
        @else
        <h5 class="ui header">Submit New Parts</h5>
        <p>
          To submit parts, you must be authorized as an author on the Parts Tracker and have read
          and affirmed the <a href="https://www.ldraw.org/article/349.html">Contributor Agreement</a>.<br/> 
          To become authorized as an author you must first register with the 
          <a href="https://forums.ldraw.org/member.php?action=register"> LDraw.org Forum</a> (it's free) and then 
          send an email to the <a href="mailto:parts@ldraw.org">Parts Library Admin</a> (Orion Pobursky)
          with your true first and last names and your LDraw.org username. You must also include
          the statement <em>"I accept the LDraw.org Contributor Agreement with regards to all past and
          future contributions I make to LDraw.org"</em>.
        </p>
        @endcan
      </div>
      <div class="column">
        <h5 class="ui header"><A HREF="{{route('tracker.index')}}">Parts List</A></h5>
        <p>
          Look at the complete list of unofficial files.
        </p>
  
        <h5 class="ui header"><A HREF="{{asset('library/unofficial/ldrawunf.zip')}}">Download All Unofficial Files</a></h5>
        <p>
          Grab the current unofficial part files in one zip file. 
          Anyone can download unofficial parts for their own use.
          We also encourage you to let the part authors know about any defects you find.<br>
          <strong>Please remember</strong>: These are unofficial parts. They may be incomplete, or
          inaccurate, and it is possible that when they are officially released they
          may be changed in ways that could mess up any model you use them in.  This
          is far more likely for Held parts than Certified parts.
        </p>
        </div>
    </div>
  <div class="ui vertical segment">
    <div class="ui two column grid">
      <div class="column">
        <h4 class="ui header">Stats for Unofficial Files:</h4>
        <x-part.unofficial-count :summary="$summary" small="0"/>
      </div>
      <div class="column">
        {{-- PT Status placeholder --}}
      </div>
    </div>
  </div>

</x-layout.main>
