<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ReplyCommentRequest;
use App\Http\Requests\Api\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use App\Services\CommentReplyService;
use Illuminate\Http\JsonResponse;

/**
 * Comentarios para n8n Flujo 3: registrar comentario y publicar respuesta (generada por IA en n8n).
 */
class CommentController extends Controller
{
    /**
     * Registrar un comentario recibido (ej. desde n8n webhook que recibe de Meta).
     * n8n puede llamar esto para tener el comment en Laravel y luego usar el id para reply.
     *
     * POST /api/comments
     */
    public function store(StoreCommentRequest $request): JsonResponse
    {
        $companyId = $request->getCompanyId();
        if (! $companyId) {
            return response()->json(['message' => 'Company required (X-Company-Id or user company).'], 422);
        }
        $post = Post::where('id', $request->validated('post_id'))
            ->where('company_id', $companyId)
            ->firstOrFail();

        $comment = Comment::create([
            'post_id' => $post->id,
            'platform' => $request->validated('platform'),
            'platform_comment_id' => $request->validated('platform_comment_id'),
            'username' => $request->validated('username'),
            'text' => $request->validated('text'),
        ]);

        return response()->json(['comment' => $comment], 201);
    }

    /**
     * Publicar respuesta a un comentario (mensaje generado por IA en n8n).
     *
     * POST /api/comments/{comment}/reply
     */
    public function reply(ReplyCommentRequest $request, Comment $comment, CommentReplyService $replyService): JsonResponse
    {
        $companyId = $request->getCompanyId();
        if ($companyId === null || $comment->post->company_id !== $companyId) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $ok = $replyService->reply($comment, $request->validated('message'));

        if (! $ok) {
            return response()->json(['message' => 'No se pudo publicar la respuesta.'], 422);
        }

        return response()->json(['comment' => $comment->fresh()]);
    }
}
