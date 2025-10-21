<?php

declare(strict_types=1);

namespace App\Filament\Resources\Sites\Schemas;

use App\Enums\SiteStatus;
use App\Enums\SiteType;
use App\Models\Site;
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
                                            ->afterStateUpdated(fn($state, callable $set) => $set('domain', Str::slug($state) . '.test')),

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
                                            ->default(fn() => env('APP_ENV') === 'local'
                                                ? storage_path('app/sites')
                                                : '/var/www')
                                            ->helperText('Site dosyalarının ana dizini (local: storage/app/sites, production: /var/www)'),

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

                        Tabs\Tab::make('Git Ayarları')
                            ->icon('heroicon-o-code-bracket')
                            ->schema([
                                Section::make()
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
                                            ->placeholder('Otomatik oluşturulacak')
                                            ->maxLength(64)
                                            ->rule('nullable|regex:/^[A-Za-z0-9_]+$/')
                                            ->helperText('Sadece harf, rakam ve alt çizgi kullanılabilir (maksimum 64 karakter).'),

                                        TextInput::make('database_user')
                                            ->label('Database Kullanıcısı')
                                            ->placeholder('Otomatik oluşturulacak')
                                            ->maxLength(64)
                                            ->rule('nullable|regex:/^[A-Za-z0-9_]+$/')
                                            ->helperText('Sadece harf, rakam ve alt çizgi kullanılabilir (maksimum 64 karakter).'),

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

                        Tabs\Tab::make('Deployment Script')
                            ->icon('heroicon-o-command-line')
                            ->schema([
                                Section::make()
                                    ->description('Bu script, site deploy edildiğinde git pull sonrası otomatik olarak çalıştırılır.')
                                    ->schema([
                                        CodeEditor::make('deployment_script')
                                            ->label('Deployment Script')
                                            ->helperText('Site tipinize göre otomatik oluşturulmuştur. İhtiyacınıza göre düzenleyebilirsiniz.')
                                            ->default(fn($get) => $get('type') ? Site::getDefaultDeploymentScript($get('type')) : '')
                                            ->reactive()
                                            ->columnSpanFull(),
                                    ]),
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
