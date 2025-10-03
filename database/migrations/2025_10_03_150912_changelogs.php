<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('changelogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tweak_id')->constrained()->onDelete('cascade');
            $table->string('version');
            $table->text('changelog')->nullable(); // JSON array of changes
            $table->boolean('is_active')->default(false); // Currently active version
            $table->timestamps();

            $table->unique(['tweak_id', 'version']);
            $table->index(['tweak_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('changelogs');
    }
};
