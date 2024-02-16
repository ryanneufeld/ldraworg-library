<?php

namespace App\Filament\Resources\ReviewSummaryResource\Pages;

use App\Filament\Resources\ReviewSummaryResource;
use App\Models\Part;
use App\Models\ReviewSummary;
use App\Models\ReviewSummaryItem;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditReviewSummary extends EditRecord
{
    protected static string $resource = ReviewSummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['manualEntry'] = $this->record->toString();
    
        return $data;
    }

    protected function handleRecordUpdate(Model $summary, array $data): Model
    {
        if(isset($data['manualEntry'])) {
            $summary->items()->delete();
            $lines = explode("\n", $data['manualEntry']);
            foreach($lines as $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }
                $order = $summary->items()->orderBy('order', 'DESC')->first()->order ?? 0;
                if ($line[0] == '/') {
                    $heading = explode(" ", $line, 2)[1] ?? '';
                    ReviewSummaryItem::create([
                        'heading' => empty($heading) ? '' : $heading,
                        'order' => $order + 1,
                        'review_summary_id' => $summary->id
                    ]);            
                } else {
                    $part = Part::unofficial()->firstWhere('filename', $line) ?? Part::official()->firstWhere('filename', $line);
                    if (!empty($part)) {
                        ReviewSummaryItem::create([
                            'part_id' => $part->id,
                            'order' => $order + 1,
                            'review_summary_id' => $summary->id
                        ]);            
                    }
                }
            }
            $summary->refresh();
        }        
 
        return $summary;
    }
}
