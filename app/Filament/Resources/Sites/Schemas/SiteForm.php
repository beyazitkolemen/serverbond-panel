<?php

declare(strict_types=1);

namespace App\Filament\Resources\Sites\Schemas;

use App\Enums\SiteStatus;
use App\Enums\SiteType;
use App\Models\Site;
use App\Services\DeploymentScriptService;
use App\Services\GitService;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
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
                        Tabs\Tab::make('Temel Bilgiler')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make('Site Detayları')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Site Adı')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                // Domain güncelle
                                                $set('domain', Str::slug($state) . '.test');

                                                // Database oluşturma aktifse ve database adı boşsa
                                                if ($get('create_database') && !$get('database_name')) {
                                                    $dbPrefix = 'sb_' . Str::slug($state, '_');
                                                    $dbPrefix = preg_replace('/[^a-zA-Z0-9_]/', '', $dbPrefix);
                                                    $dbPrefix = substr($dbPrefix, 0, 60);

                                                    $set('database_name', $dbPrefix . '_db');
                                                    $set('database_user', $dbPrefix . '_user');
                                                    $set('database_password', Str::random(16));
                                                }
                                            }),

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

                                Section::make('Dizin Ayarları')
                                    ->schema([
                                        TextInput::make('root_directory')
                                            ->label('Ana Dizin')
                                            ->required()
                                            ->default('/srv/serverbond/sites')
                                            ->helperText('Tüm siteler /srv/serverbond/sites dizinine kurulur'),

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
                                            ->visible(fn($get) => in_array($get('type'), [SiteType::Laravel, SiteType::PHP])),
                                    ])
                                    ->columns(2),
                            ]),

                        Tabs\Tab::make('Git & Deployment')
                            ->icon('heroicon-o-code-bracket')
                            ->schema([
                                Section::make('Git Repository')
                                    ->schema([
                                        TextInput::make('git_repository')
                                            ->label('Git Repository')
                                            ->url()
                                            ->placeholder('https://github.com/user/repo.git')
                                            ->helperText('GitHub, GitLab veya Bitbucket repository URL')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                if (!empty($state)) {
                                                    $gitService = app(GitService::class);

                                                    // Branch'leri çek ve options olarak ayarla
                                                    $branches = $gitService->listRemoteBranches($state);

                                                    if (!empty($branches)) {
                                                        // İlk branch'i seç veya default branch'i bul
                                                        $defaultBranch = $gitService->detectDefaultBranch($state);

                                                        // Branch henüz seçilmediyse default'u seç
                                                        if (empty($get('git_branch'))) {
                                                            $set('git_branch', $defaultBranch);
                                                        }
                                                    }

                                                    // Site adı boşsa, repository adından oluştur
                                                    if (empty($get('name'))) {
                                                        $projectName = $gitService->extractProjectName($state);
                                                        if ($projectName) {
                                                            $set('name', ucfirst($projectName));
                                                            $set('domain', Str::slug($projectName) . '.test');
                                                        }
                                                    }
                                                }
                                            }),

                                        Select::make('git_branch')
                                            ->label('Branch')
                                            ->placeholder('Önce repository girin')
                                            ->helperText('Deploy edilecek branch seçin')
                                            ->options(function (callable $get) {
                                                $repository = $get('git_repository');

                                                if (empty($repository)) {
                                                    return [];
                                                }

                                                $gitService = app(GitService::class);
                                                $branches = $gitService->listRemoteBranches($repository);

                                                if (empty($branches)) {
                                                    // Fallback: Manuel giriş için bazı yaygın branch'ler
                                                    return [
                                                        'main' => 'main',
                                                        'master' => 'master',
                                                        'develop' => 'develop',
                                                    ];
                                                }

                                                // Branch'leri key-value array'e çevir
                                                return array_combine($branches, $branches);
                                            })
                                            ->searchable()
                                            ->native(false)
                                            ->live()
                                            ->suffixIcon('heroicon-o-arrow-path-rounded-square')
                                            ->preload(),

                                        Textarea::make('git_deploy_key')
                                            ->label('Deploy Key (Opsiyonel)')
                                            ->rows(5)
                                            ->placeholder('-----BEGIN RSA PRIVATE KEY-----')
                                            ->helperText('Private repository için SSH deploy key'),

                                        Toggle::make('auto_deploy')
                                            ->label('Otomatik Deploy')
                                            ->default(false)
                                            ->helperText('Git push sonrası otomatik deployment'),
                                    ])
                                    ->columns(2),

                                Section::make('Deployment Script')
                                    ->description('Deploy sırasında otomatik çalıştırılacak komutlar (composer install, npm build vb.)')
                                    ->schema([
                                        CodeEditor::make('deployment_script')
                                            ->label('Bash Script')
                                            ->helperText('Boş bırakılırsa site tipine göre varsayılan script kullanılır.')
                                            ->default(function ($get) {
                                                $type = $get('type');
                                                if ($type) {
                                                    $scriptService = app(DeploymentScriptService::class);
                                                    return $scriptService->getDefaultScript($type);
                                                }
                                                return '';
                                            })
                                            ->reactive()
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsible(),
                            ]),

                        Tabs\Tab::make('Gelişmiş')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make('Database')
                                    ->description('Database oluşturmak istiyorsanız aktifleştirin. Alanlar otomatik doldurulur.')
                                    ->schema([
                                        Toggle::make('create_database')
                                            ->label('Database Oluştur')
                                            ->helperText('Deployment sırasında otomatik database oluşturulsun mu?')
                                            ->default(true)
                                            ->live()
                                            ->dehydrated(false) // Veritabanına kaydedilmez, sadece form'da kullanılır
                                            ->afterStateHydrated(function ($component, $state, $record) {
                                                // Edit modunda, database bilgileri varsa toggle'ı aktif göster
                                                if ($record && ($record->database_name || $record->database_user || $record->database_password)) {
                                                    $component->state(true);
                                                }
                                            })
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                if ($state && !$get('database_name')) {
                                                    $name = $get('name');
                                                    if ($name) {
                                                        $dbPrefix = 'sb_' . Str::slug($name, '_');
                                                        $dbPrefix = preg_replace('/[^a-zA-Z0-9_]/', '', $dbPrefix);
                                                        $dbPrefix = substr($dbPrefix, 0, 60); // MySQL limiti

                                                        $set('database_name', $dbPrefix . '_db');
                                                        $set('database_user', $dbPrefix . '_user');
                                                        $set('database_password', Str::random(16));
                                                    }
                                                } elseif (!$state) {
                                                    // Toggle kapatıldığında database bilgilerini temizle
                                                    $set('database_name', null);
                                                    $set('database_user', null);
                                                    $set('database_password', null);
                                                }
                                            })
                                            ->visible(fn($get) => in_array($get('type'), [SiteType::Laravel, SiteType::PHP]))
                                            ->columnSpanFull(),

                                        TextInput::make('database_name')
                                            ->label('Database Adı')
                                            ->placeholder('Otomatik oluşturulacak')
                                            ->maxLength(64)
                                            ->rule('nullable|regex:/^[a-zA-Z0-9_]+$/')
                                            ->helperText('Sadece harf, rakam ve alt çizgi kullanılabilir')
                                            ->visible(fn($get) => $get('create_database') === true),

                                        TextInput::make('database_user')
                                            ->label('Database Kullanıcısı')
                                            ->placeholder('Otomatik oluşturulacak')
                                            ->maxLength(64)
                                            ->rule('nullable|regex:/^[a-zA-Z0-9_]+$/')
                                            ->helperText('Sadece harf, rakam ve alt çizgi kullanılabilir')
                                            ->visible(fn($get) => $get('create_database') === true),

                                        TextInput::make('database_password')
                                            ->label('Database Şifresi')
                                            ->revealable()
                                            ->placeholder('Otomatik oluşturulacak')
                                            ->helperText('Güvenli bir şifre otomatik oluşturulur')
                                            ->visible(fn($get) => $get('create_database') === true),
                                    ])
                                    ->columns(3)
                                    ->collapsible()
                                    ->visible(fn($get) => in_array($get('type'), [SiteType::Laravel, SiteType::PHP])),

                                Section::make('SSL & Güvenlik')
                                    ->schema([
                                        Toggle::make('ssl_enabled')
                                            ->label('SSL Etkin')
                                            ->default(false)
                                            ->helperText("Let's Encrypt SSL sertifikası")
                                            ->inline(false),

                                        TextInput::make('deploy_webhook_token')
                                            ->label('Webhook Token')
                                            ->disabled()
                                            ->helperText('Otomatik oluşturulacak'),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),

                                Section::make('Environment (.env)')
                                    ->description('Site\'nin .env dosyasını buradan düzenleyebilirsiniz. Deploy sonrası geçerli olur.')
                                    ->schema([
                                        CodeEditor::make('env_content')
                                            ->label('.env Dosyası')
                                            ->helperText('Site deploy edildikten sonra .env dosyası otomatik oluşturulur veya güncellenir.')
                                            ->dehydrateStateUsing(fn($state) => $state)
                                            ->afterStateHydrated(function ($component, $state, $record) {
                                                if ($record && $record->exists) {
                                                    // Site'nin .env dosyasını oku
                                                    $envContent = $record->getEnvFile();

                                                    if ($envContent) {
                                                        $component->state($envContent);
                                                    } else {
                                                        // Yoksa template oluştur
                                                        $component->state($record->getDefaultEnvContent());
                                                    }
                                                }
                                            })
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsible(),

                                Section::make('Notlar')
                                    ->schema([
                                        Textarea::make('notes')
                                            ->label('Notlar')
                                            ->rows(5)
                                            ->placeholder('Bu site hakkında notlar...')
                                            ->columnSpanFull(),
                                    ])
                                    ->collapsible(),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
