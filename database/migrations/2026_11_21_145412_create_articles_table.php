<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('tag')->nullable()->index();          // e.g. "Event", "News", "Announcement"
            $table->string('location')->nullable();
            $table->date('event_date')->nullable()->index();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();                // HTML from TipTap
            $table->string('cover_image')->nullable();           // URL
            $table->string('status')->default('draft')->index(); // draft | published
            $table->boolean('featured')->default(false)->index();// pin to hero
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
