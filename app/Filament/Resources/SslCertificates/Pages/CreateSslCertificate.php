<?php

namespace App\Filament\Resources\SslCertificates\Pages;

use App\Filament\Resources\SslCertificates\SslCertificateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSslCertificate extends CreateRecord
{
    protected static string $resource = SslCertificateResource::class;
}
