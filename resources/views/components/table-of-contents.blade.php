@if (count($toc) > 0)
    <h3>Table of Contents</h3>
    <ul>
        @foreach($toc as $item)
            @if($loop->first)
                @php
                    $base = $item['level'];
                    $current = $base;    
                @endphp
            @endif
            @if ($item['level'] > $current)
                <ul>
                @php
                    $current = $item['level'];
                @endphp
            @elseif ($item['level'] < $current)
                @for($i = $item['level']; $i <= $current - 1; $i++)
                    </ul></li>
                @endfor
                @php
                    $current = $item['level'];
                @endphp
            @elseif(!$loop->first)
                </li>
            @endif
            <li><a href="#{{$item['anchor']}}">{{$item['heading']}}</a>
            @if($loop->last && $item['level'] > $base)
                </li>
                @for($i = $base; $i <= $item['level'] - 1; $i++)
                    </ul></li>
                @endfor
            @endif
        @endforeach
    </ul>
@endif