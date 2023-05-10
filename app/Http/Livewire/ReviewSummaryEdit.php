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

    public function addItem() {
        if(!empty($this->newPartId)) {
            ReviewSummaryItem::create([
                'part_id' => $this->newPartId,
                'order' => $this->summary->items()->orderBy('order', 'DESC')->first()->order ?? 1,
                'review_summary_id' => $this->summary->id
            ]);
            $this->summary->refresh();
            $this->newPartId = null;    
        }
    }

    public function addHeading() {
        ReviewSummaryItem::create([
            'heading' => empty($this->newHeading) ? '' :  $this->newHeading,
            'order' => $this->summary->items()->orderBy('order', 'DESC')->first()->order ?? 1,
            'review_summary_id' => $this->summary->id
        ]);
        $this->summary->refresh();
        $this->newHeading = null;
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
        return view('livewire.review-summary-edit');
    }
}
