<?php

declare(strict_types=1);

namespace App\Filament\Resources\Sites\Schemas;

use App\Enums\SiteStatus;
use App\Enums\SiteType;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class SiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Site Configuration')
                    ->tabs([
                        Tabs\Tab::make('Genel Bilgiler')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Site Adı')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn ($state, callable $set) => $set('domain', Str::slug($state) . '.test')),

                                        TextInput::make('domain')
                                            ->label('Alan Adı')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255)
                                            ->prefix('https://')
                                            ->placeholder('example.com')
                                            ->helperText('Alan adını tam olarak girin (örn: example.com)'),

                                        Select::make('type')
                                            ->label('Site Tipi')
                                            ->required()
                                            ->options(SiteType::class)
                                            ->default(SiteType::Laravel)
                                            ->native(false)
                                            ->live(),

                                        Select::make('status')
                                            ->label('Durum')
                                            ->required()
                                            ->options(SiteStatus::class)
                                            ->default(SiteStatus::Inactive)
                                            ->native(false),
                                    ])
                                    ->columns(2),
                            ]),

                        Tabs\Tab::make('Dizin Ayarları')
                            ->icon('heroicon-o-folder')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        TextInput::make('root_directory')
                                            ->label('Ana Dizin')
                                            ->required()
                                            ->default('/var/www')
                                            ->helperText('Site dosyalarının ana dizini'),

                                        TextInput::make('public_directory')
                                            ->label('Public Dizin')
                                            ->placeholder('public')
                                            ->helperText('Opsiyonel: Laravel için "public", Static için "dist" vb.'),

                                        Select::make('php_version')
                                            ->label('PHP Versiyonu')
                                            ->options([
                                                '8.4' => 'PHP 8.4',
                                                '8.3' => 'PHP 8.3',
                                                '8.2' => 'PHP 8.2',
                                                '8.1' => 'PHP 8.1',
                                            ])
                                            ->default('8.4')
                                            ->native(false)
                                            ->visible(fn ($get) => in_array($get('type'), [SiteType::Laravel, SiteType::PHP])),
                                    ])
                                    ->columns(2),
                            ]),

                        Tabs\Tab::make('Git Ayarları')
                            ->icon('heroicon-o-code-bracket')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        TextInput::make('git_repository')
                                            ->label('Git Repository')
                                            ->url()
                                            ->placeholder('https://github.com/user/repo.git')
                                            ->helperText('GitHub, GitLab veya Bitbucket repository URL'),

                                        TextInput::make('git_branch')
                                            ->label('Branch')
                                            ->default('main')
                                            ->helperText('Deploy edilecek branch'),

                                        Textarea::make('git_deploy_key')
                                            ->label('Deploy Key')
                                            ->rows(5)
                                            ->placeholder('-----BEGIN RSA PRIVATE KEY-----')
                                            ->helperText('Private repository için SSH deploy key'),

                                        Toggle::make('auto_deploy')
                                            ->label('Otomatik Deploy')
                                            ->default(false)
                                            ->helperText('Git push sonrası otomatik deployment'),
                                    ])
                                    ->columns(2),
                            ]),

                        Tabs\Tab::make('Database')
                            ->icon('heroicon-o-circle-stack')
                            ->schema([
                                Section::make()
                                    ->description('Bu alanlar boş bırakılırsa otomatik oluşturulur.')
                                    ->schema([
                                        TextInput::make('database_name')
                                            ->label('Database Adı')
                                            ->placeholder('Otomatik oluşturulacak'),

                                        TextInput::make('database_user')
                                            ->label('Database Kullanıcısı')
                                            ->placeholder('Otomatik oluşturulacak'),

                                        TextInput::make('database_password')
                                            ->label('Database Şifresi')
                                            ->password()
                                            ->revealable()
                                            ->placeholder('Otomatik oluşturulacak'),
                                    ])
                                    ->columns(2),
                            ]),

                        Tabs\Tab::make('SSL & Güvenlik')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        Toggle::make('ssl_enabled')
                                            ->label('SSL Etkin')
                                            ->default(false)
                                            ->helperText("Let's Encrypt SSL sertifikası"),

                                        TextInput::make('deploy_webhook_token')
                                            ->label('Webhook Token')
                                            ->disabled()
                                            ->helperText('Otomatik oluşturulacak'),
                                    ])
                                    ->columns(2),
                            ]),

                        Tabs\Tab::make('Notlar')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Textarea::make('notes')
                                    ->label('Notlar')
                                    ->rows(5)
                                    ->placeholder('Bu site hakkında notlar...')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
