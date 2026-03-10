<?php

namespace App\Services;

use OpenAI\Client;

/**
 * Service for generating social media content using the OpenAI API.
 */
class AiContentService
{
    protected Client $client;

    /**
     * Platform-specific prompt templates for viral-style posts.
     *
     * @var array<string, string>
     */
    protected static array $platformPrompts = [
        'instagram' => 'Generate a viral Instagram post about {topic} including emojis and hashtags. Keep it engaging and visual. Return only the post text, no explanation.',
        'facebook' => 'Generate a viral Facebook post about {topic} including emojis and hashtags. Make it shareable and conversation-starting. Return only the post text, no explanation.',
        'linkedin' => 'Generate a professional yet engaging LinkedIn post about {topic}. Include relevant hashtags. Keep it concise and valuable for professionals. Return only the post text, no explanation.',
        'twitter' => 'Generate a viral Twitter/X post about {topic} including emojis and hashtags. Keep it within 280 characters. Return only the post text, no explanation.',
    ];

    public function __construct(?string $apiKey = null)
    {
        $this->client = \OpenAI::client($apiKey ?? config('services.openai.api_key'));
    }

    /**
     * Generate a social media post for the given topic and platform.
     *
     * @param  string  $topic  The topic or theme for the post.
     * @param  string  $platform  One of: instagram, facebook, linkedin, twitter.
     * @return string The generated post content.
     *
     * @throws \InvalidArgumentException If platform is not supported.
     * @throws \Illuminate\Http\Client\RequestException If the API request fails.
     */
    public function generatePost(string $topic, string $platform): string
    {
        $platform = strtolower($platform);

        if (! array_key_exists($platform, self::$platformPrompts)) {
            throw new \InvalidArgumentException("Unsupported platform: {$platform}. Supported: instagram, facebook, linkedin, twitter.");
        }

        $prompt = str_replace('{topic}', $topic, self::$platformPrompts[$platform]);

        $response = $this->client->chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt,
                ],
            ],
            'max_tokens' => 500,
        ]);

        $content = $response->choices[0]->message->content ?? '';

        return trim($content);
    }
}
