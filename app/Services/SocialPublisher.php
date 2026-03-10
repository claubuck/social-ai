<?php

namespace App\Services;

use App\Models\Post;
use App\Models\SocialAccount;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service for publishing posts to social network APIs.
 *
 * Each platform method should be implemented according to the official API
 * (e.g. Meta Graph API, LinkedIn API, Twitter API). These implementations
 * are stubs that can be replaced with real API calls when credentials are configured.
 */
class SocialPublisher
{
    /**
     * Publish a post to the appropriate platform based on the post's platform.
     *
     * @return bool True if published successfully, false otherwise.
     */
    public function publish(Post $post): bool
    {
        return match (strtolower($post->platform)) {
            'instagram' => $this->publishToInstagram($post),
            'facebook' => $this->publishToFacebook($post),
            'linkedin' => $this->publishToLinkedin($post),
            'twitter' => $this->publishToTwitter($post),
            default => $this->markFailed($post, "Unsupported platform: {$post->platform}"),
        };
    }

    /**
     * Publish a post to Instagram (via Meta Graph API / Instagram Graph API).
     *
     * Requires: access_token and page_id (or instagram_business_account id) on SocialAccount.
     */
    public function publishToInstagram(Post $post): bool
    {
        $account = $this->getSocialAccount($post);
        if (! $account) {
            return $this->markFailed($post, 'No Instagram account linked for this company.');
        }

        try {
            // Instagram Content Publishing: create container then publish
            // https://developers.facebook.com/docs/instagram-api/guides/content-publishing
            $createUrl = "https://graph.facebook.com/v21.0/{$account->page_id}/media";
            $params = [
                'image_url' => $post->image_url ?? 'https://via.placeholder.com/1080x1080',
                'caption' => $post->content,
                'access_token' => $account->access_token,
            ];

            $response = Http::post($createUrl, $params);

            if (! $response->successful()) {
                Log::warning('Instagram media create failed', [
                    'post_id' => $post->id,
                    'response' => $response->json(),
                ]);
                return $this->markFailed($post, 'Instagram API error: ' . $response->body());
            }

            $containerId = $response->json('id');
            if (! $containerId) {
                return $this->markFailed($post, 'Instagram did not return a container id.');
            }

            $publishUrl = "https://graph.facebook.com/v21.0/{$account->page_id}/media_publish";
            $publishResponse = Http::post($publishUrl, [
                'creation_id' => $containerId,
                'access_token' => $account->access_token,
            ]);

            if (! $publishResponse->successful()) {
                Log::warning('Instagram publish failed', [
                    'post_id' => $post->id,
                    'response' => $publishResponse->json(),
                ]);
                return $this->markFailed($post, 'Instagram publish error: ' . $publishResponse->body());
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Instagram publish exception', ['post_id' => $post->id, 'message' => $e->getMessage()]);
            return $this->markFailed($post, $e->getMessage());
        }
    }

    /**
     * Publish a post to Facebook (Meta Graph API).
     *
     * Requires: access_token and page_id on SocialAccount.
     */
    public function publishToFacebook(Post $post): bool
    {
        $account = $this->getSocialAccount($post);
        if (! $account) {
            return $this->markFailed($post, 'No Facebook account linked for this company.');
        }

        try {
            $url = "https://graph.facebook.com/v21.0/{$account->page_id}/feed";
            $params = [
                'message' => $post->content,
                'access_token' => $account->access_token,
            ];
            if ($post->image_url) {
                $params['link'] = $post->image_url;
            }

            $response = Http::post($url, $params);

            if (! $response->successful()) {
                Log::warning('Facebook publish failed', [
                    'post_id' => $post->id,
                    'response' => $response->json(),
                ]);
                return $this->markFailed($post, 'Facebook API error: ' . $response->body());
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Facebook publish exception', ['post_id' => $post->id, 'message' => $e->getMessage()]);
            return $this->markFailed($post, $e->getMessage());
        }
    }

    /**
     * Publish a post to LinkedIn (Share API).
     *
     * Requires: access_token on SocialAccount. Uses UGC post API.
     */
    public function publishToLinkedin(Post $post): bool
    {
        $account = $this->getSocialAccount($post);
        if (! $account) {
            return $this->markFailed($post, 'No LinkedIn account linked for this company.');
        }

        try {
            // LinkedIn requires person or organization URN. page_id could store member or organization id.
            $authorUrn = $account->page_id ? "urn:li:organization:{$account->page_id}" : null;
            if (! $authorUrn) {
                return $this->markFailed($post, 'LinkedIn page_id (organization URN) not set.');
            }

            $body = [
                'author' => $authorUrn,
                'lifecycleState' => 'PUBLISHED',
                'specificContent' => [
                    'com.linkedin.ugc.ShareContent' => [
                        'shareCommentary' => ['text' => $post->content],
                        'shareMediaCategory' => 'NONE',
                    ],
                ],
                'visibility' => ['com.linkedin.ugc.MemberNetworkVisibility' => 'PUBLIC'],
            ];

            $response = Http::withToken($account->access_token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post('https://api.linkedin.com/v2/ugcPosts', $body);

            if (! $response->successful()) {
                Log::warning('LinkedIn publish failed', [
                    'post_id' => $post->id,
                    'response' => $response->json(),
                ]);
                return $this->markFailed($post, 'LinkedIn API error: ' . $response->body());
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('LinkedIn publish exception', ['post_id' => $post->id, 'message' => $e->getMessage()]);
            return $this->markFailed($post, $e->getMessage());
        }
    }

    /**
     * Publish a post to Twitter / X (Twitter API v2).
     *
     * Requires: access_token (or OAuth 2.0 token) on SocialAccount.
     */
    public function publishToTwitter(Post $post): bool
    {
        $account = $this->getSocialAccount($post);
        if (! $account) {
            return $this->markFailed($post, 'No Twitter account linked for this company.');
        }

        try {
            $response = Http::withToken($account->access_token)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post('https://api.twitter.com/2/tweets', [
                    'text' => mb_substr($post->content, 0, 280),
                ]);

            if (! $response->successful()) {
                Log::warning('Twitter publish failed', [
                    'post_id' => $post->id,
                    'response' => $response->json(),
                ]);
                return $this->markFailed($post, 'Twitter API error: ' . $response->body());
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Twitter publish exception', ['post_id' => $post->id, 'message' => $e->getMessage()]);
            return $this->markFailed($post, $e->getMessage());
        }
    }

    /**
     * Get the social account for the post's company and platform.
     */
    protected function getSocialAccount(Post $post): ?SocialAccount
    {
        return SocialAccount::where('company_id', $post->company_id)
            ->where('platform', $post->platform)
            ->first();
    }

    /**
     * Mark the post as failed and return false.
     */
    protected function markFailed(Post $post, string $reason): bool
    {
        $post->update(['status' => Post::STATUS_FAILED]);
        Log::warning('Post marked as failed', ['post_id' => $post->id, 'reason' => $reason]);
        return false;
    }
}
