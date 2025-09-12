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
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('category_id')->references('id')->on('categories')->cascadeOnDelete();
            $table->string('title');  // Blog title
            $table->string('slug')->unique();  // Slug for SEO-friendly URL
            $table->text('content');  // Blog content
            $table->text('image')->nullable();  // Blog content
            $table->text('source')->nullable();  // Blog content
            $table->text('excerpt')->nullable();  // Blog excerpt/summary
            $table->json('tags')->nullable();  // Tags (stored as JSON)
            $table->integer('views')->default(0);  // Tags (stored as JSON)
            $table->enum('status', ['draft', 'published'])->default('draft');  // Status (draft/published)
            $table->enum('type', ['featured', 'normal', 'special'])->default('normal');  // Status (draft/published)
            $table->string('meta_title')->nullable();  // SEO: Meta title
            $table->string('meta_description')->nullable();  // SEO: Meta description
            $table->timestamp('published_at')->nullable();  // Publish date/time
            $table->timestamps();
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
