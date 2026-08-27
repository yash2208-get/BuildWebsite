<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('group', 60)->default('general')->index();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('type', 20)->default('string'); // string|bool|int|json|encrypted
            $table->boolean('is_public')->default(false);
            $table->boolean('is_encrypted')->default(false);
            $table->string('label')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('key_prefix', 16)->index();
            $table->string('key_hash', 128)->unique();
            $table->json('scopes')->nullable();
            $table->unsignedInteger('rate_limit')->default(60);
            $table->unsignedBigInteger('usage_count')->default(0);
            $table->string('last_used_ip', 45)->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('ai_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('website_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 40)->index(); // website|landing|content|blog|palette|image|design
            $table->string('provider', 32)->default('simulated');
            $table->string('model', 60)->nullable();
            $table->text('prompt')->nullable();
            $table->json('options')->nullable();
            $table->longText('result')->nullable();
            $table->string('status', 20)->default('completed')->index();
            $table->text('error')->nullable();
            $table->unsignedInteger('tokens_used')->default(0);
            $table->unsignedInteger('credits_used')->default(1);
            $table->unsignedInteger('duration_ms')->default(0);
            $table->timestamps();
            $table->index(['user_id', 'type']);
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('log_name', 60)->default('default')->index();
            $table->string('event', 60)->index();       // created|updated|deleted|login...
            $table->string('description');
            $table->nullableMorphs('subject');
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('severity', 20)->default('info')->index();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
        });

        Schema::create('security_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event', 60)->index(); // login_failed|password_reset|2fa|blocked
            $table->string('email')->nullable();
            $table->string('ip_address', 45)->nullable()->index();
            $table->string('user_agent')->nullable();
            $table->string('location')->nullable();
            $table->string('level', 20)->default('info')->index();
            $table->json('context')->nullable();
            $table->timestamps();
        });

        Schema::create('website_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->foreignId('page_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date')->index();
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('unique_visitors')->default(0);
            $table->unsignedBigInteger('sessions')->default(0);
            $table->decimal('bounce_rate', 5, 2)->default(0);
            $table->unsignedInteger('avg_duration')->default(0);
            $table->json('referrers')->nullable();
            $table->json('devices')->nullable();
            $table->json('countries')->nullable();
            $table->timestamps();
            $table->unique(['website_id', 'page_id', 'date'], 'analytics_scope_unique');
        });

        Schema::create('backups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('path')->nullable();
            $table->string('disk', 32)->default('local');
            $table->string('type', 20)->default('full'); // full|database|files
            $table->unsignedBigInteger('size')->default(0);
            $table->string('status', 20)->default('pending')->index();
            $table->text('error')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'backups', 'website_analytics', 'security_logs', 'activity_logs',
            'ai_generations', 'api_keys', 'settings',
        ] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
