<div>
    @if ($paginator->hasPages())   
        <div class="ui one column grid">
            <div class="computer only tablet only column">
                <div class="ui compact pagination menu">
                    <a @class(['disabled' => $paginator->onFirstPage(), 'item']) wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"><i class="ui chevron left icon"></i></a>
                    @foreach ($elements as $element)
                        @if (is_string($element))
                            <div class="disabled item"><i class="ui ellipsis horizontal icon"></i></div>
                        @endif
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <div class="active item" wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}" >{{ $page }}</div>
                                @else
                                    <a class="item" wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}" wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')">{{ $page }}</a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                    <a @class(['disabled' => !$paginator->hasMorePages(), 'item']) wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"><i class="ui chevron right icon"></i></a>
                </div>
            </div>
            <div class="mobile only column">
                <div class="ui compact pagination menu">
                    <a @class(['disabled' => $paginator->onFirstPage(), 'item']) wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled"><i class="ui chevron left icon"></i>Prev</a>
                    <div class="active item" wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}" >{{ $paginator->currentPage() }}</div>
                    <a @class(['disabled' => !$paginator->hasMorePages(), 'item']) wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled">Next<i class="ui chevron right icon"></i></a>
                </div>
            </div>    
        </div>
    @endif
</div>