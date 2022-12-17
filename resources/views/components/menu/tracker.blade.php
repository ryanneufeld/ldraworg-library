<div class="ui menu">
  <a class="item" href="{{route('tracker.main')}}">Parts Tracker</a> 
  @can('create', App\Models\Part::class)
  <a class="item" href="{{route('tracker.submit')}}">Submit</a>
  @endcan
  <a class="item" href="{{route('tracker.index')}}">Parts List</a> 
  <a class="item" href="{{route('tracker.activity')}}">Activity</a> 
  <a class="item" href="{{route('tracker.weekly')}}">Weekly New Parts</a> 
  <div class="ui dropdown item">
    Review Summaries<i class="dropdown icon"></i>
    <div class="menu">
      <a class="item" href="">Placeholder</a> 
      <a class="item" href="">Placeholder</a>
    </div>
  </div>
  <div class="ui dropdown item">
    <a href="https://www.ldraw.org/docs-main.html">Documentation</a> <i class="dropdown icon"></i>
    <div class="menu">
      <div class="ui dropdown item">
        LDraw File Format <i class="dropdown icon"></i>
        <div class="menu">
          <a class="item" href="https://www.ldraw.org/article/218.html">LDraw File Format Specification</a>
          <a class="item" href="https://www.ldraw.org/article/299.html">Colour Definition (!COLOUR) Language Extension</a>
          <a class="item" href="https://www.ldraw.org/article/415.html">Back Face Culling (BFC) Language Extension</a>
          <a class="item" href="https://www.ldraw.org/texmap-spec.html">Texture Mapping (!TEXMAP) Language Extension</a>
          <a class="item" href="https://www.ldraw.org/article/340.html">!CATEGORY and !KEYWORDS Language Extension</a>
          <a class="item" href="https://www.ldraw.org/article/47.html">Multi-Part Document (MPD) and Image Embedding (!DATA) Language Extension</a>
          <a class="item" href="https://www.ldraw.org/article/559.html">Localisation Guideline</a>
        </div>
      </div>
      <div class="ui dropdown item">
        LDraw.org Official Parts Library Standards <i class="dropdown icon"></i>
        <div class="menu">
          <a class="item" href="https://www.ldraw.org/article/512.html">LDraw.org Official Parts Library Specifications</a>
          <a class="item" href="https://www.ldraw.org/part-number-spec.html">Official Library Part Number Specification</a>
          <a class="item" href="https://www.ldraw.org/article/398.html">Official Library Header Specification</a>
        </div>
      </div>
      <div class="ui dropdown item">
        Official Model Repository (OMR) Standards <i class="dropdown icon"></i>
        <div class="menu">
          <a class="item" href="https://www.ldraw.org/article/593.html">Official Model Repository (OMR) Specification</a>
          <a class="item" href="https://www.ldraw.org/docs-main/official-model-repository-omr/rules-and-procedures-for-the-official-model-repository.html">Rules and procedures for the Official Model Repository</a>
        </div>
      </div>
      <div class="ui dropdown item">
        FAQs <i class="dropdown icon"></i>
        <div class="menu">
          <a class="item" href="https://www.ldraw.org/ptfaq.html">Parts Tracker FAQ</a>
          <a class="item" href="https://www.ldraw.org/authorfaq.html">Parts Authoring FAQ</a>
          <a class="item" href="https://www.ldraw.org/reviewfaq.html">Parts Reviewing FAQ</a>
        </div>
      </div>
      <div class="ui dropdown item">
        Quick Reference Guides <i class="dropdown icon"></i>
        <div class="menu">
          <a class="item" href="http://www.ldraw.org/library/primref/">Primitive Reference</a>
          <a class="item" href="https://www.ldraw.org/article/547.html">Colour Definition Reference</a>
          <a class="item" href="https://www.ldraw.org/docs-main/ldraw-org-quick-reference-guides/common-error-check-messages.html">Common Error Check Messages</a>
        </div>
      </div>
      <div class="ui dropdown item">
        Licenses <i class="dropdown icon"></i>
        <div class="menu">
          <a class="item" href="https://www.ldraw.org/docs-main/licenses/ldraw-org-contributor-agreement.html">LDraw.org Contributor Agreement</a>
          <a class="item" href="https://www.ldraw.org/docs-main/licenses/legal-info.html">Legal Info</a>
        </div>
      </div>
    </div>
  </div>
  <div class="ui dropdown item">
    Tools<i class="dropdown icon"></i>
    <div class="menu">
      <a class="item" href="{{route('dashboard.index')}}">User Dashboard</a> 
      <a class="item" href="{{route('tracker.search')}}">Part Search</a>
      <a class="item" href="{{route('tracker.suffixsearch')}}">Pattern/Shortcut Part Summary</a> 
      <a class="item" href="{{asset('library/unofficial/ldrawunf.zip')}}">Download All Unofficial Files</a>
    </div>
  </div>
  <div class="right menu">
    <div class="item">
      <form id="pt_search_comp" action="{{route('tracker.search')}}" method="get" name="pt_search_comp">
      <div class="ui scrolling category search ptsearch">
      <div class="ui transparent icon input">
        <input class="prompt" name="s" type="text" placeholder="Quick Search">
        <i class="search link icon" onclick=""></i>
      </div>
      <div class="results"></div>
      </div>
      </form>
    </div>
  </div>
</div>