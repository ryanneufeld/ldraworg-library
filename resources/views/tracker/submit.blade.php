<x-layout.tracker>
  <x-slot:title>Parts Tracker File Submit Form</x-slot>
  <x-slot:breadcrumbs>
    <x-breadcrumb-item class="active" item="Submit" />
  </x-slot>    
  <div class="ui large header">Parts Tracker File Submit Form</div>

  <p>
  Use this form to upload <b>new</b> files to the Parts Tracker and to update already-submitted <b>unofficial</b> files.
  </p>
  <livewire:part.submit />
  <p>To submit a fix for an <b>existing file</b>,  email the file to 
  <a href="mailto:parts@ldraw.org">parts@ldraw.org</a>, and it will be manually posted to the tracker.
  </p>
  <p>
  You must be registered as an LDraw.org user and a member of the Submitter group to use this form.  
  To register as an LDraw user go to the <A HREF="http://www.ldraw.org/user.php?op=check_age&module=NS-NewUser">
  LDraw.org registration area</A>. 
  To become a member of the Submitter group please email 
  <A HREF="mailto:parts@ldraw.org">parts@ldraw.org</A>, including your LDraw username.
  </p>
  <p>
  Or you can submit your files to
  <A HREF="mailto:parts@ldraw.org">parts@ldraw.org</A>, and they will be manually posted.
  </p>
  <p>
  Uploaded files should appear almost immediately in the Parts Tracker list.
  </p>
</x-layout.tracker>

