@props(['title' => ''])
<x-layout.base title="{{$title}}" :styles="['app']" :scripts="['app']">
   <div class="ui container">
    <div class="ui clearing basic segment logos">
      <img id="main-logo" class="ui left floated image" src="{{asset('/images/banners/default/main.png')}}">
      <img class="ui right floated image" src="{{asset('/images/banners/default/tracker-trimmed.png')}}">
    </div>
    <div class="ui menu">
      <div class="item">Placeholder 1</div>
      <div class="item">Placeholder 2</div>
      <div class="item">Placeholder 3</div>
      <div class="item">Placeholder 4</div>
      <div class="right menu">
        <div class="ui right aligned category search item">
         <div class="ui transparent icon input">
           <input class="prompt" type="text" placeholder="Search library...">
           <i class="search link icon"></i>
         </div>
         <div class="results"></div>
         </div>
       </div>
    </div>
    <div class="ui basic segment breadcrumb">
      <a class="section">Home</a>
      <div class="divider"> / </div>
      <a class="section">Store</a>
      <div class="divider"> / </div>
      <div class="active section">T-Shirt</div>
    </div>
    <div class="ui segment main-content">
       {{ $slot ?? '' }}
    </div>
    <div class="ui basic segment footer">
      <p>
        Website copyright &copy;2003-{{date_format(now(),"Y")}} LDraw.org, see 
        <a href="/legal-info">Legal Info</a> for details.
      </p>
      <p>
        LDraw is a completely unofficial, community run free CAD system which 
        represents official parts produced by the LEGO company.
      </p>
      <p>
        LDraw&trade; is a trademark owned and licensed by the Estate of James Jessiman<br>
        LEGO&reg; is a registered trademark of the LEGO Group, which does not sponsor, 
        endorse, or authorize this site. Visit the official Lego website at 
        <a href="https://www.lego.com" target="_blank">http://www.lego.com</a>
      </p>
    </div>
    </div>
</x-layout.base>
