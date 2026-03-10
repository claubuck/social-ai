<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GeneratePostRequest;
use App\Services\AiContentService;
use Illuminate\Http\JsonResponse;

class AiGeneratePostController extends Controller
{
    /**
     * Generate a social media post using AI for the given topic and platform.
     *
     * POST /api/ai/generate-post
     */
    public function store(GeneratePostRequest $request, AiContentService $aiService): JsonResponse
    {
        try {
            $content = $aiService->generatePost(
                $request->validated('topic'),
                $request->validated('platform')
            );

            return response()->json([
                'content' => $content,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Failed to generate post.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
