<?php

namespace App\Console\Commands;

use App\Jobs\PublishPostJob;
use App\Models\Post;
use Illuminate\Console\Command;

class PublishScheduledPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'posts:publish-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish posts that are scheduled and due (status=scheduled, publish_at <= now)';

    /**
     * Execute the console command.
     * Dispatches a PublishPostJob for each due post.
     */
    public function handle(): int
    {
        $posts = Post::dueForPublishing()->get();

        if ($posts->isEmpty()) {
            $this->info('No posts due for publishing.');
            return self::SUCCESS;
        }

        foreach ($posts as $post) {
            PublishPostJob::dispatch($post);
            $this->line("Dispatched job for post id: {$post->id} (company: {$post->company_id}, platform: {$post->platform})");
        }

        $this->info("Dispatched {$posts->count()} post(s) to the queue.");
        return self::SUCCESS;
    }
}
