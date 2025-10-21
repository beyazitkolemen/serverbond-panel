<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deployment_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deployment_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('site_id')->constrained()->onDelete('cascade');
            $table->string('level', 20)->default('info'); // info, warning, error, success
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['deployment_id', 'created_at']);
            $table->index(['site_id', 'created_at']);
            $table->index('level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployment_logs');
    }
};

