<?php

namespace App\Livewire\Dashboard\Admin\Pages;

use App\Models\PartCategory;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;

class PartCategoryManage extends BasicResourceManagePage
{
    use InteractsWithForms;
    use InteractsWithTable;

    public string $title = "Manage Part Categories";

    public function table(Table $table): Table
    {
        return $table
            ->query(PartCategory::query())
            ->defaultSort('category')
            ->heading('Part Category Management')
            ->paginated(false)
            ->columns([
                TextColumn::make('category')
            ])
/*
            ->actions([
                EditAction::make()
                    ->form($this->formSchema()),
            ])
*/
            ->headerActions([
                CreateAction::make()
                    ->form($this->formSchema())
            ]);
    }

    protected function formSchema(): array
    {
        return [
            TextInput::make('category')
                ->string()
                ->required(),
        ];
    }
}
