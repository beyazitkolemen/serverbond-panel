<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->text('cloudflare_tunnel_token')->nullable()->after('deploy_webhook_token');
            $table->string('cloudflare_tunnel_id')->nullable()->after('cloudflare_tunnel_token');
            $table->boolean('cloudflare_tunnel_enabled')->default(false)->after('cloudflare_tunnel_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn([
                'cloudflare_tunnel_token',
                'cloudflare_tunnel_id',
                'cloudflare_tunnel_enabled',
            ]);
        });
    }
};

