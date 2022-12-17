@aware(['vote'])
@if ($vote['H'] != 0)
Errors Found
@elseif ($vote['S'] != 0)
  @if ($vote['S'] == 1)
  {{$vote['S']}} Uncertified Subfile
  @else
  {{$vote['S']}} Uncertified Subfiles
  @endif
@elseif ((($vote['A'] > 0) && (($vote['C'] + $vote['A']) >= 2)) || ($vote['T'] > 0))
Certified!
@elseif (($vote['C'] + $vote['A']) >= 2)
Needs Admin Review
@else
  @if (($vote['C'] + $vote['A']) == 1)
  Needs 1 More Vote
  @else
  Needs More Votes
  @endif
@endif