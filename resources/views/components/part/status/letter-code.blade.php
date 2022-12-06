@aware(['part'])
@isset($part->official_part_id)
({{str_repeat('T', $part->vote_summary['T']) . str_repeat('A', $part->vote_summary['A']) . str_repeat('C', $part->vote_summary['C']) . str_repeat('H', $part->vote_summary['H']) . str_repeat('S', $part->uncertified_subparts)}}F)
@else
({{str_repeat('T', $part->vote_summary['T']) . str_repeat('A', $part->vote_summary['A']) . str_repeat('C', $part->vote_summary['C']) . str_repeat('H', $part->vote_summary['H']) . str_repeat('S', $part->uncertified_subparts)}}N)
@endisset