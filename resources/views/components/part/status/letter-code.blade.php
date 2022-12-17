@aware(['vote'])
@if($vote['F'])
({{str_repeat('T', $vote['T']) . str_repeat('A', $vote['A']) . str_repeat('C', $vote['C']) . str_repeat('H', $vote['H']) . str_repeat('S', $vote['S'])}}F)
@else
({{str_repeat('T', $vote['T']) . str_repeat('A', $vote['A']) . str_repeat('C', $vote['C']) . str_repeat('H', $vote['H']) . str_repeat('S', $vote['S'])}}N)
@endisset