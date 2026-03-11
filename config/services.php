<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
    ],

    /*
    | Facebook / Meta OAuth (páginas e Instagram conectado).
    | Crear app en https://developers.facebook.com/
    | redirect_uri debe coincidir con la URL configurada en la app (ej. https://tuapp.com/facebook/callback).
    */
    'facebook' => [
        'app_id' => env('FACEBOOK_APP_ID'),
        'app_secret' => env('FACEBOOK_APP_SECRET'),
        'redirect_uri' => env('APP_URL', 'http://localhost').'/facebook/callback',
        'oauth_url' => 'https://www.facebook.com/v20.0/dialog/oauth',
        'token_url' => 'https://graph.facebook.com/v20.0/oauth/access_token',
        'graph_url' => 'https://graph.facebook.com/v20.0',
    ],

    /*
    | API key para n8n/clientes externos (multi-tenant).
    | Una sola clave; la empresa en cada request con header X-Company-Id.
    | N8N_COMPANY_ID solo para un único tenant (opcional).
    */
    'n8n' => [
        'api_key' => env('N8N_API_KEY'),
        'company_id' => env('N8N_COMPANY_ID') ? (int) env('N8N_COMPANY_ID') : null,
        'webhook_generate_url' => env('N8N_WEBHOOK_GENERATE_URL'),
    ],

];
