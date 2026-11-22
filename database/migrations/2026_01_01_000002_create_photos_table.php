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
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')
                  ->constrained('galleries')
                  ->cascadeOnDelete();
            $table->string('gdrive_file_id', 255);
            $table->string('filename', 255);
            $table->boolean('is_selected')->default(false);
            $table->text('client_note')->nullable();
            $table->timestamps();

            // Composite indexes for performance & idempotency
            $table->unique(['gallery_id', 'gdrive_file_id'], 'uniq_gallery_gdrive_file');
            $table->index(['gallery_id', 'is_selected'], 'idx_gallery_is_selected');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photos');
    }
};
