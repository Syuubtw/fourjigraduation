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
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->string('client_name', 255);
            $table->string('slug', 255)->unique();
            $table->string('pin', 10)->nullable();
            $table->string('gdrive_folder_id', 255);
            $table->unsignedSmallInteger('max_select')->default(20);
            $table->enum('status', ['draft', 'open', 'submitted', 'completed'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            // Index on status
            $table->index('status', 'idx_galleries_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('galleries');
    }
};
