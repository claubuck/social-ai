<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Publica respuestas a comentarios en las APIs de cada red.
 * Usado por n8n Flujo 3: después de generar la respuesta con IA, n8n llama a POST /api/comments/{id}/reply.
 */
class CommentReplyService
{
    public function reply(Comment $comment, string $message): bool
    {
        return match (strtolower($comment->platform)) {
            'instagram', 'facebook' => $this->replyMeta($comment, $message),
            'linkedin' => $this->replyLinkedin($comment, $message),
            'twitter' => $this->replyTwitter($comment, $message),
            default => false,
        };
    }

    /**
     * Meta Graph API: responder a un comentario.
     * https://developers.facebook.com/docs/graph-api/reference/object/comments
     */
    protected function replyMeta(Comment $comment, string $message): bool
    {
        $account = SocialAccount::where('company_id', $comment->post->company_id)
            ->where('platform', $comment->platform)
            ->first();

        if (! $account || ! $account->access_token) {
            Log::warning('CommentReply: no account or token', ['comment_id' => $comment->id]);
            return false;
        }

        $url = "https://graph.facebook.com/v21.0/{$comment->platform_comment_id}/comments";
        $response = Http::post($url, [
            'message' => $message,
            'access_token' => $account->access_token,
        ]);

        if (! $response->successful()) {
            Log::warning('CommentReply Meta failed', [
                'comment_id' => $comment->id,
                'response' => $response->json(),
            ]);
            return false;
        }

        $comment->update([
            'replied_at' => now(),
            'reply_text' => $message,
        ]);
        return true;
    }

    protected function replyLinkedin(Comment $comment, string $message): bool
    {
        // LinkedIn UGC comments API si lo necesitas más adelante
        Log::info('CommentReply LinkedIn not implemented', ['comment_id' => $comment->id]);
        return false;
    }

    protected function replyTwitter(Comment $comment, string $message): bool
    {
        // Twitter reply as a new tweet replying to the comment's tweet
        Log::info('CommentReply Twitter not implemented', ['comment_id' => $comment->id]);
        return false;
    }
}
