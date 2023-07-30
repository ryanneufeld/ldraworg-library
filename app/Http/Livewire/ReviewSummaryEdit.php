<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\ReviewSummary;
use App\Models\ReviewSummaryItem;

class ReviewSummaryEdit extends Component
{
    public ReviewSummary $summary;
    public $manualEntry;

    public function processManualEntry() {
        if(!empty($this->manualEntry)) {
            $this->summary->items()->delete();
            $lines = explode("\n", $this->manualEntry);
            foreach($lines as $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }
                $order = $this->summary->items()->orderBy('order', 'DESC')->first()->order ?? 0;
                if ($line[0] == '/') {
                    $heading = explode(" ", $line, 2)[1] ?? '';
                    ReviewSummaryItem::create([
                        'heading' => empty($heading) ? '' : $heading,
                        'order' => $order + 1,
                        'review_summary_id' => $this->summary->id
                    ]);            
                } else {
                    $part = \App\Models\Part::unofficial()->whereFirst('filename', $line) ?? \App\Models\Part::official()->whereFirst('filename', $line);
                    if (!empty($part)) {
                        ReviewSummaryItem::create([
                            'part_id' => $part->id,
                            'order' => $order + 1,
                            'review_summary_id' => $this->summary->id
                        ]);            
                    }
                }
            }
            $this->summary->refresh();
        }        
    }

    public function render()
    {
        $this->manualEntry = $this->summary->toString();
        $this->dispatchBrowserEvent('jquery');
        return view('livewire.review-summary-edit');
    }
}
