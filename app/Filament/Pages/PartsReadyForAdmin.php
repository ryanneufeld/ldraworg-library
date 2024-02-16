<?php

namespace App\Filament\Pages;

use App\Models\Part;
use Filament\Pages\Page;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class PartsReadyForAdmin extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.parts-ready-for-admin';

    public function table(Table $table): Table
    {
        return $table
            ->query(Part::adminReady())
            ->defaultSort('created_at', 'asc')
            ->emptyState(view('tables.empty', ['none' => 'None']))
            ->columns([
                ImageColumn::make('image')
                    ->state( 
                        fn (Part $p): string => asset("images/library/{$p->libFolder()}/" . substr($p->filename, 0, -4) . '_thumb.png')
                    )
                    ->extraImgAttributes(['class' => 'object-scale-down']),
                TextColumn::make('filename')
                    ->wrap()
                    ->sortable(),
                TextColumn::make('description')
                    ->wrap()
                    ->sortable(),
                 TextColumn::make('download')
                    ->state( 
                        fn (Part $p): string => 
                            "<a href=\"" .
                            route($p->isUnofficial() ? 'unofficial.download' : 'official.download', $p->filename) . 
                            "\">[DAT]</a>"
                    )
                    ->html(),
                ViewColumn::make('vote_sort')
                    ->view('tables.columns.part-status')
                    ->sortable()
                    ->label('Status'),
                
                    ]);
    }
}
