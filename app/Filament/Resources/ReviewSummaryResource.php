<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewSummaryResource\Pages;
use App\Filament\Resources\ReviewSummaryResource\RelationManagers;
use App\Models\ReviewSummary;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ReviewSummaryResource extends Resource
{
    protected static ?string $model = ReviewSummary::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('manualEntry')
                    ->rows(30)
                    ->string()
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('order')
            ->columns([
                TextColumn::make('header')
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviewSummaries::route('/'),
            'create' => Pages\CreateReviewSummary::route('/create'),
            'edit' => Pages\EditReviewSummary::route('/{record}/edit'),
        ];
    }
}
