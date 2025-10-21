<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\MySQLService;
use Illuminate\Console\Command;

class SyncMySQLDatabases extends Command
{
    protected $signature = 'mysql:sync-databases 
                            {--force : Mevcut kayıtları da güncelle}';

    protected $description = 'MySQL server\'daki mevcut database\'leri panel\'e senkronize et';

    public function handle(MySQLService $mysqlService): int
    {
        $this->info('🔄 MySQL database\'leri taranıyor...');

        try {
            $result = $mysqlService->syncDatabases();

            $this->newLine();
            $this->info("📊 Senkronizasyon Tamamlandı:");
            $this->line("   • Toplam database: {$result['total']}");
            $this->line("   • Yeni eklenen: " . count($result['synced']));
            $this->line("   • Atlanan (mevcut): " . count($result['skipped']));

            if (!empty($result['synced'])) {
                $this->newLine();
                $this->info('✅ Eklenen Database\'ler:');
                foreach ($result['synced'] as $dbName) {
                    $this->line("   • {$dbName}");
                }
            }

            if (!empty($result['skipped'])) {
                $this->newLine();
                $this->comment('⏭️  Atlanan Database\'ler (zaten mevcut):');
                foreach ($result['skipped'] as $dbName) {
                    $this->line("   • {$dbName}");
                }
            }

            $this->newLine();
            $this->info('✨ İşlem başarıyla tamamlandı!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Hata: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
