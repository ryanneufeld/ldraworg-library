@aware(['part'])
@switch ($part->vote_sort)
@case (0)
Certified!
@break
@case (1)
Needs Admin Review
@break
@case (2)
@if ($part->vote_summary['C'] == 1)
Needs 1 More Vote
@else
Needs More Votes
@endif
@break
@case (3)
@if ($part->uncertified_subparts == 1)
{{$part->uncertified_subparts}} Uncertified Subfile
@else
{{$part->uncertified_subparts}} Uncertified Subfiles
@endif
@break
@case (4)
Errors Found
@break
@endswitch
