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
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('platform'); // instagram, facebook, linkedin, twitter
            $table->text('content');
            $table->string('image_url')->nullable();
            $table->timestamp('publish_at')->nullable();
            $table->string('status')->default('draft'); // draft, scheduled, published, failed
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['status', 'publish_at']);
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
