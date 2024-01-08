<?php
namespace App\Livewire;
 
use App\Models\PartEvent;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Livewire\Component;
 
class PartEventsShow extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;
    
    public function table(Table $table): Table
    {
        return $table
            ->query(PartEvent::latest())
            ->columns([
                ViewColumn::make('status')->view('components.event.icon.submit-with-comment'),
                TextColumn::make('user.name'),
                TextColumn::make('created_at')->since(),
                ImageColumn::make('image')
                    ->state(
                        function (PartEvent $event) {
                            if (!is_null($event->part)) {
                                return asset("images/library/{$event->part->libFolder()}" . substr($event->part->filename, 0, -4) . '_thumb.png');
                            } else {
                                return '';
                            }
                        }
                    )
                    ->extraImgAttributes(['class' => 'object-scale-down']),
                TextColumn::make('part.filename')
                    ->state(
                        fn (PartEvent $e) =>
                            !is_null($e->part) ? $e->part->filename : $e->deleted_filename
                    ),
                TextColumn::make('part.description')
                    ->state(
                        fn (PartEvent $e) =>
                            !is_null($e->part) ? $e->part->description : $e->deleted_description
                    ),
            ])
            ->filters([
                // ...
            ])
            ->actions([
                // ...
            ])
            ->bulkActions([
                // ...
            ]);
    }
    
    public function render(): View
    {
        return view('livewire.part-events-show');
    }
}