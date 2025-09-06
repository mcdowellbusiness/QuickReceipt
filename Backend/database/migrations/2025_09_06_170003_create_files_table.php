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
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('path', 255);
            $table->string('name', 191);
            $table->string('mimetype', 100);
            $table->unsignedBigInteger('size');
            $table->string('disk', 50)->default('public');
            $table->timestamps();

            $table->index(['disk', 'path']);
            $table->index(['mimetype']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};