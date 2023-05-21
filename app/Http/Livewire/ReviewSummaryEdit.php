<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\ReviewSummary;
use App\Models\ReviewSummaryItem;

class ReviewSummaryEdit extends Component
{
    public ReviewSummary $summary;
    public $newPartId;
    public $newHeading;
    public $manualEntry;

    public function addItem() {
        if(!empty($this->newPartId)) {
            ReviewSummaryItem::create([
                'part_id' => $this->newPartId,
                'order' => $this->summary->items()->orderBy('order', 'DESC')->first()->order + 1 ?? 1,
                'review_summary_id' => $this->summary->id
            ]);
            $this->summary->refresh();
            $this->newPartId = null;    
        }
    }

    public function addHeading() {
        ReviewSummaryItem::create([
            'heading' => empty($this->newHeading) ? '' :  $this->newHeading,
            'order' => $this->summary->items()->orderBy('order', 'DESC')->first()->order + 1 ?? 1,
            'review_summary_id' => $this->summary->id
        ]);
        $this->summary->refresh();
        $this->newHeading = null;
    }

    public function processManualEntry() {
        if(!empty($this->manualEntry)) {
            $lines = explode("\n", $this->manualEntry);
            foreach($lines as $line) {
                $line = trim($line);
                if ($line[0] == '/') {
                    $heading = explode(" ", $line, 2)[1];
                    ReviewSummaryItem::create([
                        'heading' => empty($heading) ? '' :  $heading,
                        'order' => $this->summary->items()->orderBy('order', 'DESC')->first()->order + 1 ?? 1,
                        'review_summary_id' => $this->summary->id
                    ]);            
                } else {
                    $part = \App\Models\Part::findUnofficialByName($line) ?? \App\Models\Part::findOfficialByName($line);
                    if (!empty($part)) {
                        ReviewSummaryItem::create([
                            'part_id' => $part->id,
                            'order' => $this->summary->items()->orderBy('order', 'DESC')->first()->order + 1 ?? 1,
                            'review_summary_id' => $this->summary->id
                        ]);            
                    }
                }
            }
        }
        $this->summary->refresh();
        $this->manualEntry = null;
    }
    public function removeItem(ReviewSummaryItem $item) {
        $item->delete();
        //Rerack the order
        foreach($this->summary->items()->orderBy('order')->get() as $i => $item) {
            $item->order = $i + 1;
            $item->save();
        }
        $this->summary->refresh();
    }

    public function updateItemOrder($reorderedItems) {
        foreach($reorderedItems as $itm) {
            $item = ReviewSummaryItem::find($itm['value']);
            $item->order = $itm['order'];
            $item->save();
        }
        $this->summary->refresh();
    }

    public function render()
    {
        $this->dispatchBrowserEvent('jquery');
        return view('livewire.review-summary-edit');
    }
}
