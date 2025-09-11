<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Post extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    protected $guarded = [];

    public function likedUsers()
    {
        return $this->belongsToMany(User::class, 'post_users')->withTimestamps();
    }

    public function isLikedBy(?User $user): bool
    {
        if (!$user) return false;
        return $this->likedUsers()->where('user_id', $user->id)->exists();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('id', 'desc');
    }

    public function topLevelComments()
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id')->latest();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('postImages')->singleFile()->registerMediaConversions(function (Media $media = null) {
            $this->addMediaConversion('avatar')->quality('80')->nonQueued();

        });

        $this->addMediaCollection('post')->singleFile()->registerMediaConversions(function (Media $media = null) {
            $this->addMediaConversion('thumb')->quality('80')->nonQueued();

        });
    }
    protected $casts = [
        'tags' => 'array',  // Cast tags field to array
        'published_at' => 'datetime',
    ];

    public function published()
    {
        return $this->status === 'published' && $this->published_at <= now();
    }

    // Relationship with Category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relationship with User (Author)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relationship with Tag (many-to-many)
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'tag_posts')->withTimestamps();
    }

    // Scopes for published posts
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    // Mutator to ensure slug is URL-friendly
    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = \Str::slug($value);
    }
}
