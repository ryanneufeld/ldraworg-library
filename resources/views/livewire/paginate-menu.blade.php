<div>
@if ($paginator->hasPages())   
@php(isset($this->numberOfPaginatorsRendered[$paginator->getPageName()]) ? $this->numberOfPaginatorsRendered[$paginator->getPageName()]++ : $this->numberOfPaginatorsRendered[$paginator->getPageName()] = 1)
<div class="ui tiny compact menu">
    @if(!$paginator->onFirstPage())
    <a class="item" wire:click="previousPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled">Prior</a>
    @endif
    @if($paginator->hasMorePages())
    <a class="item" wire:click="nextPage('{{ $paginator->getPageName() }}')" wire:loading.attr="disabled">Next</a>
    @endif
    @if(!$paginator->onFirstPage())
    <a class="item" wire:click="gotoPage(1, '{{ $paginator->getPageName() }}')" wire:loading.attr="disabled">Newest</a>
    @endif
</div>
@endif
</div>
