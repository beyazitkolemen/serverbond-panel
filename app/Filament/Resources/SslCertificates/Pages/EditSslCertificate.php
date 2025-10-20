<?php

namespace App\Filament\Resources\SslCertificates\Pages;

use App\Filament\Resources\SslCertificates\SslCertificateResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSslCertificate extends EditRecord
{
    protected static string $resource = SslCertificateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
