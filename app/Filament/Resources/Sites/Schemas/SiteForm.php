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
                        Tabs\Tab::make('Genel')
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Section::make('Site Detayları')
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Site Adı')
                                            ->required()
                                            ->maxLength(255)
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                // Domain güncelle
                                                $set('domain', Str::slug($state) . '.test');
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

                        Tabs\Tab::make('Git Repository')
                            ->icon('heroicon-o-code-bracket')
                            ->schema([
                                Section::make('Repository Ayarları')
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
                            ]),

                        Tabs\Tab::make('Deployment Script')
                            ->icon('heroicon-o-command-line')
                            ->schema([
                                Section::make('Bash Script')
                                    ->description('Deploy sırasında otomatik çalıştırılacak komutlar (composer install, migration, npm build vb.)')
                                    ->schema([
                                        CodeEditor::make('deployment_script')
                                            ->label('Script İçeriği')
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
                                    ]),
                            ]),

                        Tabs\Tab::make('Environment (.env)')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                Section::make('.env Dosyası')
                                    ->description('Site deploy edildikten sonra .env dosyasını buradan düzenleyebilirsiniz.')
                                    ->schema([
                                        CodeEditor::make('env_content')
                                            ->label('Dosya İçeriği')
                                            ->helperText('Deploy edildiğinde veya güncellendiğinde sadece database bilgileri otomatik güncellenir, diğer değişiklikleriniz korunur.')
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
                                    ->visible(fn($record) => $record !== null && $record->exists),
                            ]),

                        Tabs\Tab::make('Gelişmiş')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make('Database Bilgileri')
                                    ->description('Deployment sonrası otomatik oluşturulan database bilgileri')
                                    ->schema([
                                        TextInput::make('database_name')
                                            ->label('Database Adı')
                                            ->disabled()
                                            ->dehydrated()
                                            ->copyable()
                                            ->placeholder('Deploy sonrası oluşturulacak'),

                                        TextInput::make('database_user')
                                            ->label('Database Kullanıcısı')
                                            ->disabled()
                                            ->dehydrated()
                                            ->copyable()
                                            ->placeholder('Deploy sonrası oluşturulacak'),

                                        TextInput::make('database_password')
                                            ->label('Database Şifresi')
                                            ->disabled()
                                            ->dehydrated()
                                            ->password()
                                            ->revealable()
                                            ->copyable()
                                            ->placeholder('Deploy sonrası oluşturulacak'),
                                    ])
                                    ->columns(3)
                                    ->collapsible()
                                    ->visible(fn($record) => $record !== null && $record->exists && $record->database_name),

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
                                            ->helperText('Otomatik oluşturulacak')
                                            ->password()
                                            ->revealable()
                                            ->copyable(),
                                    ])
                                    ->columns(2)
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
