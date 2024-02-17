<x-layout.tracker>
  <x-slot:title>
    Parts Tracker Main
  </x-slot>
  <div class="space-y-2">
        <p class="p-2">
            The Parts Tracker is the system we use to submit files to the LDraw.org Part Library.
            The Parts Tracker allows users to download unofficial parts, submit new files, update existing unofficial files, and review unofficial parts.
        </p>
        <div class="grid grid-cols-2 p-2 gap-2">
            <div class="flex flex-col">
                <div class="text-sm font-bold">
                    <a href="https://www.ldraw.org/pt-policies.html">Parts Tracker Policies</a>
                </div>
                <p>
                    Policies for users of the Parts Tracker.
                </p>
  
                <div class="text-sm font-bold">
                    <a href="{{route('tracker.activity')}}">Activity Log</a>
                </div>
                <p>
                    View the recent submissions, reviews, and admin actions on the tracker.
                    Very useful for keeping an eye on the Parts Tracker.
                </p>
                @can('create',\App\Models\Part::class)
                    <div class="text-sm font-bold">
                        <a href="{{route('tracker.submit')}}">Submit New Parts</a>
                    </div>
                    <p>
                        To submit parts, visit the part submission page and input your parts. Your parts will
                        be validated and, if they pass, be added to the Parts Tracker for review.
                    </p>
                @else
                    <div class="text-sm font-bold">
                        Submit New Parts
                    </div>
                    <p>
                        To submit parts, you must be authorized as an author on the Parts Tracker and have read
                        and affirmed the <a href="https://www.ldraw.org/article/349.html">Contributor Agreement</a>.<br/> 
                        To become authorized as an author you must first register with the 
                        <a href="https://forums.ldraw.org/member.php?action=register"> LDraw.org Forum</a> (it's free) and then 
                        send an email to the <a href="mailto:parts@ldraw.org">Parts Library Admin</a> (Orion Pobursky)
                        with your true first and last names and your LDraw.org username. You must also include
                        the statement <span class="font-italic">"I accept the LDraw.org Contributor Agreement with regards to all past and
                        future contributions I make to LDraw.org"</span>.
                    </p>
                @endcan
            </div>
            <div class="flex flex-col">
                <div class="text-sm font-bold">
                    <a href="{{route('tracker.index')}}">Parts List</a>
                </div>
                <p>
                    Look at the complete list of unofficial files.
                </p>
  
                <div class="text-sm font-bold">
                    <a href="{{asset('library/unofficial/ldrawunf.zip')}}">Download All Unofficial Files</a>
                </div>
                <p>
                    Grab the current unofficial part files in one zip file. 
                    Anyone can download unofficial parts for their own use.
                    We also encourage you to let the part authors know about any defects you find.<br>
                    <span class="font-bold">Please remember</span>: These are unofficial parts. They may be incomplete, or
                    inaccurate, and it is possible that when they are officially released they
                    may be changed in ways that could mess up any model you use them in.  This
                    is far more likely for Held parts than Certified parts.
                </p>
            </div>
        </div>
        <div class="flex flex-col p-2">
            <div class="text-md font-bold">
                Stats for Unofficial Files:
            </div>
            <x-part.unofficial-part-count small="0"/>
        </div>
    </div>
</x-layout.tracker>
