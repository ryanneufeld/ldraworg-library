<div>
@if ($paginator->hasPages())   
<div class="ui tiny compact menu">
    @if(!$paginator->onFirstPage())
    <a class="item" wire:click="previousPage" wire:loading.attr="disabled">Prior</a>
    @endif
    @if($paginator->hasMorePages())
    <a class="item" wire:click="nextPage" wire:loading.attr="disabled">Next</a>
    @endif
    @if(!$paginator->onFirstPage())
    <a class="item" wire:click="gotoPage(1)" wire:loading.attr="disabled">Newest</a>
    @endif
</div>
@endif
</div>
