<?php

namespace App\Filament\Resources\SslCertificates\Pages;

use App\Filament\Resources\SslCertificates\SslCertificateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSslCertificates extends ListRecords
{
    protected static string $resource = SslCertificateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
