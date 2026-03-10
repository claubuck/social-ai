<?php

namespace App\Jobs;

use App\Models\Post;
use App\Services\SocialPublisher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PublishPostJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Post $post
    ) {}

    /**
     * Execute the job: publish the post via SocialPublisher and update status.
     */
    public function handle(SocialPublisher $publisher): void
    {
        $post = $this->post;

        if ($post->status !== Post::STATUS_SCHEDULED) {
            return;
        }

        $success = $publisher->publish($post);

        if ($success) {
            $post->update(['status' => Post::STATUS_PUBLISHED]);
        }
    }
}
