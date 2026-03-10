<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_FAILED = 'failed';

    /**
     * Valid post statuses.
     *
     * @var list<string>
     */
    public static array $statuses = [
        self::STATUS_DRAFT,
        self::STATUS_SCHEDULED,
        self::STATUS_PUBLISHED,
        self::STATUS_FAILED,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'platform',
        'content',
        'image_url',
        'publish_at',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'publish_at' => 'datetime',
        ];
    }

    /**
     * Get the company that owns the post.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the engagement metrics for the post.
     */
    public function engagementMetrics(): HasMany
    {
        return $this->hasMany(EngagementMetric::class);
    }

    /**
     * Scope: posts that are scheduled and due for publishing.
     */
    public function scopeDueForPublishing($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED)
            ->where('publish_at', '<=', now());
    }
}
