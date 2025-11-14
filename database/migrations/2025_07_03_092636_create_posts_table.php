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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('authorId');
            $table->text('content')->nullable();
            $table->string('additionFile')->nullable();
            $table->unsignedBigInteger('groupId')->nullable();
            $table->enum('status', ['private', 'public']);
            $table->unsignedBigInteger('categoryId')->nullable();

            $table->foreign('authorId')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('groupId')->references('id')->on('groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
