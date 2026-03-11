<?php

namespace App\Http\Requests;

use App\Models\SocialAccount;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSocialAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'platform' => ['required', 'string', Rule::in(SocialAccount::$platforms)],
            'account_name' => ['required', 'string', 'max:255'],
            'access_token' => ['required', 'string'],
            'page_id' => ['nullable', 'string', 'max:255'],
        ];
    }
}
