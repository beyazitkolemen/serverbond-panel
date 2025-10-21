<?php

declare(strict_types=1);

namespace App\Filament\Resources\Settings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('group')
                    ->label('Grup')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('key')
                    ->label('Anahtar')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('label')
                    ->label('Etiket')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('value')
                    ->label('Değer')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('type')
                    ->label('Tip')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'string' => 'gray',
                        'integer' => 'info',
                        'boolean' => 'success',
                        'json', 'array' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                IconColumn::make('is_public')
                    ->label('Public')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_encrypted')
                    ->label('Şifreli')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('order')
                    ->label('Sıra')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Güncellenme')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('group')
                    ->label('Grup')
                    ->options(fn () => \App\Models\Setting::query()
                        ->distinct()
                        ->pluck('group', 'group')
                        ->toArray()
                    ),

                SelectFilter::make('type')
                    ->label('Tip')
                    ->options([
                        'string' => 'Metin',
                        'integer' => 'Sayı',
                        'boolean' => 'Doğru/Yanlış',
                        'json' => 'JSON',
                        'array' => 'Dizi',
                    ]),

                SelectFilter::make('is_public')
                    ->label('Public')
                    ->options([
                        '1' => 'Evet',
                        '0' => 'Hayır',
                    ]),
            ])
            ->defaultSort('group')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

