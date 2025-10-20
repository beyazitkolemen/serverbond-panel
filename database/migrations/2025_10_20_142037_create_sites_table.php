<?php

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
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain')->unique();
            $table->string('type')->default('laravel');
            $table->string('root_directory')->default('/var/www');
            $table->string('public_directory')->nullable(); // public or public_html or dist
            $table->string('git_repository')->nullable();
            $table->string('git_branch')->default('main');
            $table->string('git_deploy_key')->nullable();
            $table->string('status')->default('inactive');
            $table->string('php_version')->nullable();
            $table->string('database_name')->nullable();
            $table->string('database_user')->nullable();
            $table->string('database_password')->nullable(); // Encrypted
            $table->boolean('ssl_enabled')->default(false);
            $table->boolean('auto_deploy')->default(false);
            $table->string('deploy_webhook_token')->nullable();
            $table->timestamp('last_deployed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
