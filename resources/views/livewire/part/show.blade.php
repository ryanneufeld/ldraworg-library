<div>
    @push('css')
    <link rel="stylesheet" type="text/css" href="/assets/css/ldbi.css">
    @endpush

    <x-slot name="title">File Detail {{ $part->filename }}</x-slot>
    <x-menu.part-detail :part="$part" />
    @if(session()->has('status'))
        <div class="ui message success">
            <div class="header">{{session('status')}}</div>
        </div>
    @endif
    <div class="ui segment main-content">
        <div class="ui large header">
            <span class="{{$lib}}">{{ucfirst($lib)}} File <span id="filename">{{ $part->filename }}</span></span>
        </div>
        <div>
        @if ($part->isUnofficial() && Auth::check())
            <button wire:click="toggleTracking" @class([
                'ui',
                'yellow' => Auth::user()->notification_parts->contains($part->id),
                'labeled icon button',
            ])>
                <i class="bell icon"></i>
                {{Auth::user()->notification_parts->contains($part->id) ? 'Tracking' : 'Track'}}
            </button>
            @can('part.flag.delete')
                <button wire:click="toggleDelete" @class([
                    'ui',
                    'red' => $part->delete_flag,
                    'labeled icon button',
                ])>
                    <i class="flag icon"></i>
                    {{$part->delete_flag ? 'Flagged' : 'Flag'}} for Deletion
                </button>    
            @else
                @if($part->delete_flag)
                    <div class="ui red labeled icon button">
                        <i class="flag icon"></i>
                        Flagged for Deletion
                    </div>
                @endif       
            @endcan    
        @endif
        </div>
        <div>
            @isset ($part->unofficial_part_id)
            <a class="ui button" href="{{ route('tracker.show', $part->unofficial_part_id) }}">View unofficial version of part</a>
            @endisset
            @isset ($part->official_part_id)
            <a class="ui button" href="{{ route('official.show', $part->official_part_id) }}">View official version of part</a>
            @endisset
        </div>      
        <div class="ui right floated center aligned compact {{$lib}} detail-img segment">
            @if ($part->isTexmap())
            <a class="ui image" href="{{route("$lib.download", $part->filename)}}">
            <img src="{{route("$lib.download", $part->filename)}}" alt='part image' title="{{ $part->description }}" >
            </a>
            @else
            <a class="ui part-img image" href="{{asset("images/library/$lib/" . substr($part->filename, 0, -4) . '.png')}}">
            <img src="{{asset("images/library/$lib/" . substr($part->filename, 0, -4) . '.png')}}" alt='part image' title="{{ $part->description }}" >
            </a>
            @endif  
        </div>
        <div class="ui medium header">File Header:</div>
<pre class="part-header"><code>{{ $part->header }}</code></pre>
        @if($part->isUnofficial())
            <div class="ui medium header">Status:</div>
            <x-part.status :$part show-status />
            <div class="ui medium header">Reviewers' certifications:</div>
            <x-vote.table :votes="$part->votes" />
        @endif
        <div class="ui clearing basic segment"></div>
        @if($part->isUnofficial())
            <x-part.table title="{{ucfirst($lib)}} parent parts" :parts="$part->parents()->unofficial()->get()" />
            <x-part.table title="{{ucfirst($lib)}} subparts" :parts="$part->subparts()->unofficial()->get()" :missing="$part->missing_parts" />
        @else
            <x-part.table title="{{ucfirst($lib)}} parent parts" :parts="$part->parents()->official()->get()" />
            <x-part.table title="{{ucfirst($lib)}} subparts" :parts="$part->subparts()->official()->get()" />
        @endif    
        @if($part->isUnofficial())
            <x-event.list title="File events" :events="$part->events" />
        @endif
        <x-menu.part-detail :part="$part" />
        <x-part.attribution :copyuser="$part->user" :editusers="$part->editHistoryUsers()" />
        <x-part.3dmodal id="{{$part->id}}" />
    </div>
   
@push('scripts')
    <x-layout.ldbi-scripts />
    <script>
        $( function() {
            $('.updatesubpart.item').click(function (e) {
                e.preventDefault();
                Livewire.emit('updateSubpart');
            });

            $('.updateimage.item').click(function (e) {
                e.preventDefault();
                Livewire.emit('updateImage');
                $.toast({
                    message: `Image regenerated`,
                    className: {
                        toast: 'ui message success'
                    }
                });
            });
        });

    </script>
@endpush
</div>
