<x-layout.base>
  <x-slot name="title">
    Parts Tracker Main
  </x-slot>
  <div class="col bg-body rounded">
  <p>
    The Parts Tracker is the system we use to submit files to the LDraw.org Part Library.
    The Parts Tracker allows users to download unofficial parts, submit new files, update existing unofficial files, and review unofficial parts.
  </p>


  <div class="container">
    <div class="row">
      <div class="col">
        <P>
          <h5><A HREF="">Parts Tracker Policies</a></h5><BR>
        Policies for users of the Parts Tracker.
        </p>
  
        <p><b><a href="{{-- route('library.tracker.activity') --}}">Activity Log</a></b><br>
        View the recent submissions, reviews, and admin actions on the tracker.
        Very useful for keeping an eye on the Parts Tracker.
        </p>
  
        <P><b><A HREF="{{-- route('library.tracker.submit') --}}">Submit New Parts</A></b><br>
        To submit parts, you must be authorized as an author on the Parts Tracker and have read
        and affirmed the <a href="/article/349.html">Contributor Agreement</a>.<br/> 
        To become authorized as an author you must first register with the 
        <a href="https://forums.ldraw.org/member.php?action=register"> LDraw.org Forum</a> (it's free) and then 
        send an email to the <a href="mailto:parts@ldraw.org">Parts Library Admin</a> (Orion Pobursky)
        with your true first and last names and your LDraw.org username. You must also include
        the statement <I>"I accept the LDraw.org Contributor Agreement with regards to all past and
        future contributions I make to LDraw.org"</I>.</P>
        </div>
      <div class="col">
        <P><b><A HREF="{{-- route('library.tracker.list') --}}">Parts List</A></b><br>
        Look at the complete list of unofficial files.
        </p>
  
        <p><b><A HREF="">Download All Unofficial Files</a></b><br>
        Grab the current unofficial part files in one zip file. 
        Anyone can download unofficial parts for their own use.
        We also encourage you to let the part authors know about any defects you find.
        <BR><B>Please remember</B>: These are unofficial parts. They may be incomplete, or
        inaccurate, and it is possible that when they are officially released they
        may be changed in ways that could mess up any model you use them in.  This
        is far more likely for Held parts than Certified parts.
        </p>
  
        <p><b><A HREF="">Tools</a></b><br>
        A collection of online tools, some work with the files on the Tracker, 
        some work on files from your hard drive.
        </p>
  
        <p><b>Review Summaries</b><br>
        Here are links to groups of related files on the Parts Tracker to aid their review.<br><br>

        </p>
      </div>
    </div>
  </div>
  </div>
  
  <div class="ui vertical segment">
    <div class="ui two column grid">
      <div class="column">
        <h4 class="ui header">Stats for Unofficial Files:</h4>
{{--        <x-part.status.summary />  --}}
      </div>
      <div class="column">
        {{-- PT Status placeholder --}}
      </div>
    </div>
  </div>

</x-layout.base>
