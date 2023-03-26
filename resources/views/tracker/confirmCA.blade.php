<x-layout.main>
  <p>
    Our records indicate that you have not confirmed the current Contributer's Agreement.
    Prior to allowing you to submit or edit any parts, you must read and affirm the current
    Contributer's Agreement.
  </p>
  <p>Please read the following and click agree to continue:</p>
  <h4 class="ui block header">The Contributor Agreement</h4>
  <div class="ui segment">
  Contrbuter Agreement Dated 2022-02-23
  </div>
  <p>
  By submitting work ("the Work") to The LDraw Organization ("LDraw.org"), the 
  submitter ("the Author"), agrees to release the Work under the Creative Commons 
  Attribution License 4.0 International License ("CC BY 4.0").
  </p>
  <p>
  The human readable and legal text of the CC BY 4.0 license can be found at this link:<br/>
  <a href="https://creativecommons.org/licenses/by/4.0/">https://creativecommons.org/licenses/by/4.0/</a>
  </p>
  <form class="ui form" action="{{route('tracker.confirmCA.store')}}" method="post">
    @csrf
    @method('put')
    <input type="hidden" value="{{Auth::user()->id}}">
    <div class="field">
      <button class="ui button">I agree</button>
    </div>  
  </form>    
</x-layout.main>