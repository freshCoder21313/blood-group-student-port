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
        Schema::table('parent_info', function (Blueprint $table) {
            $table->renameColumn('relation_type', 'relationship');
            $table->renameColumn('full_name', 'guardian_name');
            $table->renameColumn('phone', 'guardian_phone');
            $table->renameColumn('email', 'guardian_email');
            
            // Drop unused columns to match story spec strictly
            $table->dropColumn(['occupation', 'address']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parent_info', function (Blueprint $table) {
            $table->renameColumn('relationship', 'relation_type');
            $table->renameColumn('guardian_name', 'full_name');
            $table->renameColumn('guardian_phone', 'phone');
            $table->renameColumn('guardian_email', 'email');
            
            // Restore columns
            $table->string('occupation')->nullable();
            $table->text('address')->nullable();
        });
    }
};
