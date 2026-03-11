<?php

namespace App\Http\Requests\Api;

use App\Models\Post;
use App\Models\SocialAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'platform' => ['required', 'string', Rule::in(SocialAccount::$platforms)],
            'content' => ['required', 'string'],
            'image_url' => ['nullable', 'string', 'url'],
            'publish_at' => ['nullable', 'date'],
            'status' => ['nullable', 'string', Rule::in(Post::$statuses)],
            'publish_immediately' => ['nullable', 'boolean'],
        ];
    }
}
