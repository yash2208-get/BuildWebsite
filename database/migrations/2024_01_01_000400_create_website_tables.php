<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->json('tokens')->nullable();     // colors, radius, shadows
            $table->json('typography')->nullable();
            $table->json('spacing')->nullable();
            $table->text('custom_css')->nullable();
            $table->string('preview_image')->nullable();
            $table->boolean('is_global')->default(false)->index();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('theme_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->index();
            $table->string('subdomain')->unique();
            $table->string('custom_domain')->nullable()->unique();
            $table->string('domain_status', 20)->default('unverified');
            $table->string('domain_verification_token', 64)->nullable();
            $table->text('description')->nullable();
            $table->string('favicon')->nullable();
            $table->string('logo')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('category', 60)->nullable()->index();
            $table->string('status', 20)->default('draft')->index(); // draft|published|suspended|archived
            $table->json('settings')->nullable();
            $table->json('seo')->nullable();
            $table->json('integrations')->nullable();
            $table->longText('custom_css')->nullable();
            $table->longText('custom_js')->nullable();
            $table->longText('head_scripts')->nullable();
            $table->longText('body_scripts')->nullable();
            $table->boolean('is_password_protected')->default(false);
            $table->string('access_password')->nullable();
            $table->boolean('maintenance_mode')->default(false);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedInteger('pages_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('last_edited_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'status']);
        });

        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->index();
            $table->string('path')->default('/');
            $table->string('type', 24)->default('page'); // page|blog|landing|system
            $table->longText('html')->nullable();
            $table->longText('css')->nullable();
            $table->longText('js')->nullable();
            $table->json('grapes_data')->nullable();   // GrapesJS project JSON
            $table->json('blocks')->nullable();
            $table->json('seo')->nullable();
            $table->string('layout', 40)->default('default');
            $table->boolean('is_homepage')->default(false);
            $table->boolean('show_in_nav')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status', 20)->default('draft')->index();
            $table->unsignedBigInteger('views_count')->default(0);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['website_id', 'slug']);
        });

        Schema::create('page_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('version')->default(1);
            $table->longText('html')->nullable();
            $table->longText('css')->nullable();
            $table->json('grapes_data')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
            $table->index(['page_id', 'version']);
        });

        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('category', 60)->index();
            $table->json('tags')->nullable();
            $table->string('preview_image')->nullable();
            $table->string('demo_url')->nullable();
            $table->longText('html')->nullable();
            $table->longText('css')->nullable();
            $table->json('grapes_data')->nullable();
            $table->json('pages')->nullable();       // multipage template definition
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('uses_count')->default(0);
            $table->decimal('rating', 3, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug')->index();
            $table->string('category', 60)->index();  // hero|pricing|faq|team...
            $table->text('description')->nullable();
            $table->longText('html')->nullable();
            $table->longText('css')->nullable();
            $table->json('schema')->nullable();       // editable props
            $table->string('icon', 60)->nullable();
            $table->string('preview_image')->nullable();
            $table->boolean('is_global')->default(true);
            $table->boolean('is_premium')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('uses_count')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('website_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('file_name');
            $table->string('path');
            $table->string('disk', 32)->default('public');
            $table->string('mime_type', 100)->nullable();
            $table->string('type', 20)->default('image')->index(); // image|video|document|audio
            $table->unsignedBigInteger('size')->default(0);
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text')->nullable();
            $table->json('conversions')->nullable();
            $table->string('folder')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
        Schema::dropIfExists('components');
        Schema::dropIfExists('templates');
        Schema::dropIfExists('page_revisions');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('websites');
        Schema::dropIfExists('themes');
    }
};
