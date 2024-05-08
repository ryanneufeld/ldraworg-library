<?php

namespace App\Livewire\Dashboard\Admin\Pages;

use App\Models\Part;
use App\Models\PartRenderView;
use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use MathPHP\LinearAlgebra\MatrixFactory;

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
                ->required()
                ->rules([
                    fn (): Closure => function (string $attribute, $value, Closure $fail) {
                        $pattern = "#^\s*(-?(?:[0-9]*[.])?[0-9]+)\s+(-?(?:[0-9]*[.])?[0-9]+)\s+(-?(?:[0-9]*[.])?[0-9]+)\s+(-?(?:[0-9]*[.])?[0-9]+)\s+(-?(?:[0-9]*[.])?[0-9]+)\s+(-?(?:[0-9]*[.])?[0-9]+)\s+(-?(?:[0-9]*[.])?[0-9]+)\s+(-?(?:[0-9]*[.])?[0-9]+)\s+(-?(?:[0-9]*[.])?[0-9]+)\s*$#";
                        $m = preg_match($pattern, $value, $terms);
                        if ($m !== 1) {
                            $fail('Invalid matrix');
                            return;
                        }
                        $matrix = [
                            [$terms[1], $terms[2], $terms[3]],
                            [$terms[4], $terms[5], $terms[6]],
                            [$terms[7], $terms[8], $terms[9]],
                        ];
                        $matrix = MatrixFactory::create($matrix);
                        if ($matrix->isSingular()) {
                            $fail('Singular Matrix');
                        }
                    },
                ]),
        ];
    }

    protected function after(PartRenderView $view): void
    {
        Part::where('filename', 'LIKE', "%{$view->part_name}%.dat")->each( fn (Part $p) =>
            app(\App\LDraw\PartManager::class)->updatePartImage($p)
        );
    }

}
