<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique()->after('name');
            $table->string('avatar')->nullable()->after('email');
            $table->string('phone', 40)->nullable();
            $table->string('company')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('locale', 8)->default('en');
            $table->string('theme', 12)->default('system');
            $table->string('status', 20)->default('active')->index();
            $table->text('bio')->nullable();
            $table->json('preferences')->nullable();
            $table->json('meta')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->string('suspension_reason')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->unsignedInteger('websites_count')->default(0);
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn([
                'username', 'avatar', 'phone', 'company', 'country', 'timezone', 'locale',
                'theme', 'status', 'bio', 'preferences', 'meta', 'last_login_ip', 'last_login_at',
                'suspended_at', 'suspension_reason', 'two_factor_enabled', 'websites_count',
            ]);
        });
    }
};
