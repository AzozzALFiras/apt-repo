<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tweaks', function (Blueprint $table) {
            // Remove the deleted_at column
            $table->dropColumn('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tweaks', function (Blueprint $table) {
            // Restore the deleted_at column (nullable timestamp for soft deletes)
            $table->timestamp('deleted_at')->nullable();
        });
    }
};
