<?php

namespace App\Livewire\Dashboard\Admin\Pages;

use App\Models\Part;
use App\Models\PartRenderView;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;

class PartRenderViewManagePage extends BasicResourceManagePage
{
    use InteractsWithForms;
    use InteractsWithTable;
    
    public string $title = "Manage Part Render Orientation";

    public function table(Table $table): Table
    {
        return $table
            ->query(PartRenderView::query())
            ->defaultSort('part_name')
            ->heading('Part Render Orientation Management')
            ->paginated(false)
            ->columns([
                TextColumn::make('part_name'),
                TextColumn::make('matrix'),
            ])
            ->actions([
                EditAction::make()
                    ->form($this->formSchema())
                    ->after(fn (PartRenderView $view) => $this->after($view)),
                DeleteAction::make()
            ])
            ->headerActions([
                CreateAction::make()
                    ->form($this->formSchema())
                    ->after(fn (PartRenderView $view) => $this->after($view)),
            ]);
    }

    protected function formSchema(): array
    {
        return [
            TextInput::make('part_name')
                ->hint('Either a specific part (e.g. 973p01) or the base part name (973) for all parts of that name')
                ->string()
                ->required(),
            TextInput::make('matrix')
                ->hint('The rotation matrix to apply to the part render.')
                ->string()
                ->required(),
        ];
    }

    protected function after(PartRenderView $view): void
    {
        Part::where('filename', 'LIKE', "%{$view->part_name}%.dat")->each( fn (Part $p) =>
            app(\App\LDraw\PartManager::class)->updatePartImage($p)
        );
    }

}
