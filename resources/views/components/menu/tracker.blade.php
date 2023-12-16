<x-menu class="stackable">
    <x-menu.item dropdown label="Library">
            <x-menu.item label="Library Main" link="{{route('index')}}" />
            <x-menu.item label="Parts Tracker" link="{{route('tracker.main')}}" />
            <x-menu.item label="Latest Update" link="{{route('part-update.index', ['latest'])}}" />
            <x-menu.item label="Update Archive" link="{{route('part-update.index')}}" />
            <x-menu.item label="OMR" link="{{route('omr.main')}}" />
    </x-menu.item>    
    @can('create', App\Models\Part::class)
        <x-menu.item label="Submit" link="{{route('tracker.submit')}}" />
    @endcan
    <x-menu.item label="Parts List" link="{{route('tracker.index')}}" /> 
    <x-menu.item label="Activity" link="{{route('tracker.activity')}}" /> 
    <x-menu.item label="Weekly New Parts" link="{{route('tracker.weekly')}}" />
    <x-menu.item dropdown label="Documentation">
            <x-menu.item dropdown label="LDraw File Format">
                    <x-menu.item label="LDraw File Format Specification" link="https://www.ldraw.org/article/218.html" />
                    <x-menu.item label="Colour Definition (!COLOUR) Language Extension" link="https://www.ldraw.org/article/299.html" />
                    <x-menu.item label="Back Face Culling (BFC) Language Extension" link="https://www.ldraw.org/article/415.html" />
                    <x-menu.item label="Texture Mapping (!TEXMAP) Language Extension" link="https://www.ldraw.org/texmap-spec.html" />
                    <x-menu.item label="!CATEGORY and !KEYWORDS Language Extension" link="https://www.ldraw.org/article/340.html" />
                    <x-menu.item label="Multi-Part Document (MPD) and Image Embedding (!DATA) Language Extension" link="https://www.ldraw.org/article/47.html" />
                    <x-menu.item label="Localisation Guideline" link="https://www.ldraw.org/article/559.html" />
            </x-menu.item>
            <x-menu.item dropdown label="LDraw.org Official Parts Library Standards">
                    <x-menu.item label="LDraw.org Official Parts Library Specifications" link="https://www.ldraw.org/article/512.html" />
                    <x-menu.item label="Official Library Part Number Specification" link="https://www.ldraw.org/part-number-spec.html" />
                    <x-menu.item label="Official Library Header Specification" link="https://www.ldraw.org/article/398.html" />
            </x-menu.item>
            <x-menu.item dropdown label="Official Model Repository (OMR) Standards ">
                    <x-menu.item label="Official Model Repository (OMR) Specification" link="https://www.ldraw.org/article/593.html" />
                    <x-menu.item label="Rules and procedures for the Official Model Repository" link="https://www.ldraw.org/docs-main/official-model-repository-omr/rules-and-procedures-for-the-official-model-repository.html" />
            </x-menu.item>
            <x-menu.item dropdown label="FAQs">
                    <x-menu.item label="Parts Tracker FAQ" link="https://www.ldraw.org/ptfaq.html" />
                    <x-menu.item label="Parts Authoring FAQ" link="https://www.ldraw.org/authorfaq.html" />
                    <x-menu.item label="Parts Reviewing FAQ" link="https://www.ldraw.org/reviewfaq.html" />
            </x-menu.item>
            <x-menu.item dropdown label="Quick Reference Guides">
                    <x-menu.item label="Primitive Reference" link="http://www.ldraw.org/library/primref/" />
                    <x-menu.item label="Colour Definition Reference" link="https://www.ldraw.org/article/547.html" />
                    <x-menu.item label="Common Error Check Messages" link="https://www.ldraw.org/docs-main/ldraw-org-quick-reference-guides/common-error-check-messages.html" />
            </x-menu.item>
            <x-menu.item dropdown label="Licenses">
                    <x-menu.item label="LDraw.org Contributor Agreement" link="https://www.ldraw.org/docs-main/licenses/ldraw-org-contributor-agreement.html" />
                    <x-menu.item label="Legal Info" link="https://www.ldraw.org/docs-main/licenses/legal-info.html" />
            </x-menu.item>
    </x-menu.item>
    <x-menu.item dropdown label="Tools">
        <x-menu.item label="User Dashboard" link="{{route('dashboard.index')}}" /> 
        <x-menu.item label="Part Search" link="{{route('search.part')}}" />
        <x-menu.item label="Pattern/Shortcut Part Summary" link="{{route('search.suffix')}}" /> 
        @if(!empty($summaries))
            <x-menu.item dropdown label="Review Summaries">
                @foreach($summaries as $summary)
                    <x-menu.item label="{{$summary->header}}" link="{{route('tracker.summary', $summary)}}" /> 
                @endforeach
            </x-menu.item>
        @endif
        <x-menu.item label="Download All Unofficial Files" link="{{asset('library/unofficial/ldrawunf.zip')}}" />
        <x-menu.item label="Parts in Next Update" link="{{route('tracker.next-release')}}" />
        <x-menu.item label="Parts Tracker History" link="{{route('tracker.history')}}" />
    </x-menu.item>
    <div class="right menu">
        <div class="item">
            <form id="pt_search_comp" action="{{route('search.part')}}" method="get" name="pt_search_comp">
                <div class="ui right aligned scrolling category search ptsearch">
                    <div class="ui transparent icon input">
                        <input class="prompt" name="s" type="text" placeholder="Quick Search">
                        <i class="search link icon" onclick=""></i>
                    </div>
                    <div class="results"></div>
                </div>
            </form>
        </div>
    </div>
</x-menu>
