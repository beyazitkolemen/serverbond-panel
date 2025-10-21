<?php

declare(strict_types=1);

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('group')
                    ->label('Grup')
                    ->required()
                    ->default('general')
                    ->helperText('Ayarın ait olduğu grup (örn: general, email, deployment)')
                    ->columnSpan(1),

                TextInput::make('key')
                    ->label('Anahtar')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('Ayarın benzersiz anahtarı (örn: site_name, smtp_host)')
                    ->columnSpan(1),

                TextInput::make('label')
                    ->label('Etiket')
                    ->helperText('Ayar için kullanıcı dostu isim')
                    ->columnSpan(1),

                Select::make('type')
                    ->label('Tip')
                    ->required()
                    ->options([
                        'string' => 'Metin',
                        'integer' => 'Sayı',
                        'boolean' => 'Doğru/Yanlış',
                        'json' => 'JSON',
                        'array' => 'Dizi',
                    ])
                    ->default('string')
                    ->reactive()
                    ->columnSpan(1),

                Textarea::make('value')
                    ->label('Değer')
                    ->rows(3)
                    ->helperText('Ayarın değeri')
                    ->columnSpanFull(),

                Textarea::make('description')
                    ->label('Açıklama')
                    ->rows(2)
                    ->helperText('Ayarın ne için kullanıldığının açıklaması')
                    ->columnSpanFull(),

                TextInput::make('order')
                    ->label('Sıralama')
                    ->numeric()
                    ->default(0)
                    ->helperText('Ayarın görüntülenme sırası')
                    ->columnSpan(1),

                Toggle::make('is_public')
                    ->label('Public')
                    ->helperText('Bu ayar frontend\'de görülebilir mi?')
                    ->default(false)
                    ->columnSpan(1),

                Toggle::make('is_encrypted')
                    ->label('Şifreli')
                    ->helperText('Bu ayar şifrelenmiş olarak saklanacak mı?')
                    ->default(false)
                    ->columnSpan(1),
            ]);
    }
}

