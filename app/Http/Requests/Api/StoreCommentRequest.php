<?php

namespace App\Http\Requests\Api;

use App\Models\SocialAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'post_id' => ['required', 'integer', 'exists:posts,id'],
            'platform' => ['required', 'string', Rule::in(SocialAccount::$platforms)],
            'platform_comment_id' => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255'],
            'text' => ['required', 'string'],
        ];
    }
}
