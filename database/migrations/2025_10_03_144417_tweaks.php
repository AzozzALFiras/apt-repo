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
        Schema::create('tweaks', function (Blueprint $table) {
            $table->id();
            $table->string('package')->unique();
            $table->string('name');
            $table->string('version');
            $table->text('description')->nullable();
            $table->string('author')->nullable();
            $table->string('maintainer')->nullable();
            $table->string('section')->default('Tweaks');
            $table->string('architecture')->nullable();
            $table->string('depends')->nullable();
            $table->string('homepage')->nullable();
            $table->string('icon_url')->nullable();
            $table->string('header_url')->nullable();
            $table->string('sileo_depiction')->nullable();
            $table->string('installed_size')->nullable();

            // File paths
            $table->string('deb_file_path');
            $table->string('extracted_path')->nullable();
            $table->string('icon_path')->nullable();

            // Additional metadata
            $table->json('data_files')->nullable();
            $table->json('control_data')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tweaks');
    }
};
