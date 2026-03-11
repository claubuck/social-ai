<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->string('platform'); // instagram, facebook, linkedin, twitter
            $table->string('platform_comment_id'); // id del comentario en la red social
            $table->string('username')->nullable();
            $table->text('text');
            $table->timestamp('replied_at')->nullable();
            $table->text('reply_text')->nullable();
            $table->timestamps();

            $table->unique(['post_id', 'platform_comment_id']);
            $table->index(['post_id', 'platform']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
