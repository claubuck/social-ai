<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends Model
{
    use HasFactory;

    public const PLATFORM_INSTAGRAM = 'instagram';
    public const PLATFORM_FACEBOOK = 'facebook';
    public const PLATFORM_LINKEDIN = 'linkedin';
    public const PLATFORM_TWITTER = 'twitter';

    /**
     * Supported platforms.
     *
     * @var list<string>
     */
    public static array $platforms = [
        self::PLATFORM_INSTAGRAM,
        self::PLATFORM_FACEBOOK,
        self::PLATFORM_LINKEDIN,
        self::PLATFORM_TWITTER,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'platform',
        'account_name',
        'access_token',
        'page_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'access_token',
    ];

    /**
     * Get the company that owns the social account.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
