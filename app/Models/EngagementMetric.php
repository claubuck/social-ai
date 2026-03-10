<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngagementMetric extends Model
{
    /**
     * Indicates if the model should be timestamped.
     * Only created_at is used.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'post_id',
        'likes',
        'comments',
        'shares',
        'views',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'likes' => 'integer',
            'comments' => 'integer',
            'shares' => 'integer',
            'views' => 'integer',
        ];
    }

    /**
     * Get the post that owns the engagement metric.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
