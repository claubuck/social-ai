<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SchedulePostRequest;
use App\Http\Requests\Api\StorePostRequest;
use App\Jobs\PublishPostJob;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Create a new post for the authenticated user's company.
     *
     * POST /api/posts
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        $companyId = $request->getCompanyId();
        if (! $companyId) {
            return response()->json(['message' => 'Company required (X-Company-Id or user company).'], 422);
        }

        $publishImmediately = $request->boolean('publish_immediately');
        $publishAt = $request->validated('publish_at');
        $status = $request->validated('status', Post::STATUS_DRAFT);

        if ($publishImmediately) {
            $status = Post::STATUS_SCHEDULED;
            $publishAt = $publishAt ?? now();
        }

        $post = Post::create([
            'company_id' => $companyId,
            'platform' => $request->validated('platform'),
            'content' => $request->validated('content'),
            'image_url' => $request->validated('image_url'),
            'publish_at' => $publishAt,
            'status' => $status,
        ]);

        if ($publishImmediately) {
            PublishPostJob::dispatch($post);
        }

        return response()->json(['post' => $post], 201);
    }

    /**
     * Schedule an existing post for publication at a given time.
     *
     * POST /api/posts/schedule
     */
    public function schedule(SchedulePostRequest $request): JsonResponse
    {
        $companyId = $request->getCompanyId();
        if (! $companyId) {
            return response()->json(['message' => 'Company required (X-Company-Id or user company).'], 422);
        }

        $post = Post::where('id', $request->validated('post_id'))
            ->where('company_id', $companyId)
            ->firstOrFail();

        $post->update([
            'status' => Post::STATUS_SCHEDULED,
            'publish_at' => $request->validated('publish_at'),
        ]);

        return response()->json(['post' => $post->fresh()]);
    }

    /**
     * Return posts that are scheduled and due for publishing (status=scheduled, publish_at <= now).
     *
     * GET /api/posts/pending
     */
    public function pending(Request $request): JsonResponse
    {
        $companyId = $request->getCompanyId();
        if (! $companyId) {
            return response()->json(['posts' => []]);
        }

        $posts = Post::dueForPublishing()
            ->where('company_id', $companyId)
            ->orderBy('publish_at')
            ->get();

        return response()->json(['posts' => $posts]);
    }

    /**
     * Mark a post as published.
     *
     * POST /api/posts/{post}/mark-published
     */
    public function markPublished(Request $request, Post $post): JsonResponse
    {
        $companyId = $request->getCompanyId();
        if ($companyId === null || $post->company_id !== $companyId) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $post->update(['status' => Post::STATUS_PUBLISHED]);

        return response()->json(['post' => $post->fresh()]);
    }
}
