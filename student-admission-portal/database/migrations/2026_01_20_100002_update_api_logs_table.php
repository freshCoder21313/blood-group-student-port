<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_logs', function (Blueprint $table) {
            // Add new columns if they don't exist
            if (!Schema::hasColumn('api_logs', 'request_id')) {
                $table->string('request_id')->nullable()->after('id')->index();
            }
            if (!Schema::hasColumn('api_logs', 'direction')) {
                $table->string('direction', 10)->default('incoming')->after('request_id');
            }
            if (!Schema::hasColumn('api_logs', 'user_agent')) {
                $table->string('user_agent')->nullable()->after('ip_address');
            }
            if (!Schema::hasColumn('api_logs', 'duration_ms')) {
                $table->integer('duration_ms')->nullable()->after('error_message');
            }
            
            // Change text to json for better handling (optional, requires doctrine/dbal usually but safe in recent Laravel)
            // $table->json('request_body')->nullable()->change();
            // $table->json('response_body')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('api_logs', function (Blueprint $table) {
            $table->dropColumn(['request_id', 'direction', 'user_agent', 'duration_ms']);
        });
    }
};
