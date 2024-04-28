<?php

namespace App\Livewire\ReviewSummary;

use App\Models\Part;
use App\Models\ReviewSummary;
use App\Models\ReviewSummaryItem;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Livewire\Attributes\Layout;
use Livewire\Component;

class Manage extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;
    
    public function table(Table $table): Table
    {
        return $table
            ->query(ReviewSummary::query())
            ->defaultSort('order')
            ->reorderable('order')
            ->heading('Part Review Summary Management')
            ->columns([
                TextColumn::make('header')
            ])
            ->actions([
                EditAction::make()
                    ->form($this->formSchema())
                    ->mutateRecordDataUsing(function (ReviewSummary $summary, array $data): array {
                        $data['manualEntry'] = $summary->toString();
                        return $data;
                    })
                    ->using(fn (ReviewSummary $summary, array $data) => $this->saveEditData($summary, $data)),
                DeleteAction::make()
            ])
            ->headerActions([
                CreateAction::make()
                    ->form($this->formSchema())
                    ->using(fn (ReviewSummary $summary, array $data) => $this->saveEditData($summary, $data)),
            ]);
    }

    protected function formSchema(): array
    {
        return [
            TextInput::make('header')
                ->required()
                ->string(),
            Textarea::make('manualEntry')
                ->rows(30)
                ->string()
                ->required()
        ];
    }

    protected function saveEditData(ReviewSummary $summary, array $data)
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
        if(isset($data['header'])) {
            $summary->header = $data['header'];
        }        
        return $summary;
    }

    #[Layout('components.layout.admin')]
    public function render()
    {
        return view('livewire.review-summary.manage');
    }

}
