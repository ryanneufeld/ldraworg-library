<x-layout.base title="LDraw.org File Editor" :styles="['app', 'edit']" :scripts="['app', 'edit']">
<div class="ui fluid segment">
{{ $slot ?? '' }}
</div>
</x-layout.base>
