<div class="ui stackable menu">
    <div class="ui dropdown item">
        Library
        <i class="dropdown icon"></i>
        <div class="menu">
            <a class="item" href="{{route('index')}}">Library Main</a>
            <a class="item" href="{{route('tracker.main')}}">Parts Tracker</a>
            <a class="item" href="{{route('part-update.index', ['latest'])}}">Latest Update</a>
            <a class="item" href="{{route('part-update.index')}}">Update Archive</a>
            <a class="item" href="{{route('omr.main')}}">OMR</a>
        </div>
    </div>    
    @can('create', App\Models\Omr\OmrModel::class)
        <a class="item" href="">Submit</a>
    @endcan
    <a class="item" href="">Model List</a> 
    <a class="item" href="">Statistics</a> 
    <div class="ui dropdown item">
        Documentation
        <i class="dropdown icon"></i>
        <div class="menu">
            <a class="item" href="https://www.ldraw.org/article/593.html">Official Model Repository (OMR) Specification</a>
            <a class="item" href="https://www.ldraw.org/docs-main/official-model-repository-omr/rules-and-procedures-for-the-official-model-repository.html">Rules and procedures for the Official Model Repository</a>
        </div>
    </div>    
</div>  