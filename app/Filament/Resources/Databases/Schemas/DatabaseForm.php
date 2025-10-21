<?php

declare(strict_types=1);

namespace App\Filament\Resources\Databases\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class DatabaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Database Bilgileri')
                    ->schema([
                        TextInput::make('name')
                            ->label('Database Adı')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(64)
                            ->rule('regex:/^[A-Za-z0-9_]+$/')
                            ->helperText('Sadece harf, rakam ve alt çizgi kullanılabilir (maksimum 64 karakter)')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Username boşsa, database adından oluştur
                                if (empty($get('username'))) {
                                    $username = Str::limit($state, 16, '');
                                    $set('username', $username);
                                }
                            }),

                        TextInput::make('username')
                            ->label('Kullanıcı Adı')
                            ->required()
                            ->maxLength(32)
                            ->rule('regex:/^[A-Za-z0-9_]+$/')
                            ->helperText('Sadece harf, rakam ve alt çizgi (maksimum 32 karakter)'),

                        TextInput::make('password')
                            ->label('Şifre')
                            ->password()
                            ->revealable()
                            ->required()
                            ->default(fn() => Str::random(16))
                            ->helperText('Güvenli bir şifre kullanın. Otomatik oluşturuldu.'),

                        Select::make('site_id')
                            ->label('İlişkili Site')
                            ->relationship('site', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Bu database hangi siteye ait? (Opsiyonel)'),
                    ])
                    ->columns(2),

                Section::make('Gelişmiş Ayarlar')
                    ->schema([
                        Select::make('charset')
                            ->label('Charset')
                            ->options([
                                'utf8mb4' => 'utf8mb4 (Önerilen)',
                                'utf8' => 'utf8',
                                'latin1' => 'latin1',
                            ])
                            ->default('utf8mb4')
                            ->required()
                            ->native(false),

                        Select::make('collation')
                            ->label('Collation')
                            ->options([
                                'utf8mb4_unicode_ci' => 'utf8mb4_unicode_ci (Önerilen)',
                                'utf8mb4_general_ci' => 'utf8mb4_general_ci',
                                'utf8_unicode_ci' => 'utf8_unicode_ci',
                                'utf8_general_ci' => 'utf8_general_ci',
                                'latin1_swedish_ci' => 'latin1_swedish_ci',
                            ])
                            ->default('utf8mb4_unicode_ci')
                            ->required()
                            ->native(false),

                        TextInput::make('max_connections')
                            ->label('Max Connections')
                            ->numeric()
                            ->default(100)
                            ->minValue(1)
                            ->maxValue(1000)
                            ->helperText('Maksimum eşzamanlı bağlantı sayısı'),

                        Textarea::make('notes')
                            ->label('Notlar')
                            ->rows(3)
                            ->placeholder('Bu database hakkında notlar...')
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ]);
    }
}
