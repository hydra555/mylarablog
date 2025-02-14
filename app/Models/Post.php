<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class Post extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'slug',
        'image',
        'body',
        'published_at',
        'featured',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->belongsToMany(User::class, 'post_like')->withTimestamps();
    }

    // ✅ Оставляем только один метод `scopePublished()`
    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeWithCategory($query, string $category)
    {
        $query->whereHas('categories', function ($query) use ($category) {
            $query->where('slug', $category);
        });
    }

    public function scopeFeatured($query)
    {
        $query->where('featured', 1);
    }

    public function scopePopular($query)
    {
        $query->withCount('likes')
            ->orderBy("likes_count", 'desc');
    }

    public function scopeSearch($query, string $search = '')
    {
        $query->where('title', 'like', "%{$search}%");
    }

    public function getExcerpt()
    {
        return Str::limit(strip_tags($this->body), 150);
    }

    public function getReadingTime()
    {
        $mins = round(str_word_count($this->body) / 250);
        return ($mins < 1) ? 1 : $mins;
    }

    // ✅ Теперь всегда HTTPS
    public function getThumbnailUrl()
    {
        $isUrl = str_contains($this->image, 'http');

        if ($isUrl) {
            return Str::replaceFirst('http://', 'https://', $this->image);
        }

        return secure_asset('storage/' . $this->image);
    }

    public function setBodyAttribute($value)
    {
        $value = preg_replace('/<a[^>]*>(<img[^>]*>)<\/a>/i', '$1', $value);
        $value = preg_replace('/<figcaption[^>]*>.*?<\/figcaption>/is', '', $value);
        $this->attributes['body'] = $value;
    }

    public static function boot()
    {
        parent::boot();

        static::deleting(function ($post) {
            // Очистка кэша после удаления поста
            Cache::forget('featuredPosts');
            Cache::forget('latestPosts');

            // Удаление всех комментариев (учитываем SoftDeletes)
            foreach ($post->comments as $comment) {
                $comment->forceDelete();
            }

            // Удаление лайков
            $post->likes()->detach();

            // Удаление основного изображения, если есть
            if (!empty($post->image)) {
                Storage::disk('public')->delete($post->image);
            }

            // Удаление meta_image, если оно есть
            if (!empty($post->meta_image)) {
                Storage::disk('public')->delete($post->meta_image);
            }

            // Извлечение и удаление изображений из body (если есть)
            if (!empty($post->body)) {
                preg_match_all('/<img.*?src=["\'](.*?)["\']/i', $post->body, $matches);
                if (!empty($matches[1])) {
                    foreach ($matches[1] as $imagePath) {
                        $imagePath = str_replace(url('/storage/'), '', $imagePath);
                        Storage::disk('public')->delete($imagePath);
                    }
                }
            }
        });

        static::forceDeleting(function ($post) {
            // Очистка title и slug перед удалением (не обязательно, но можно)
            $post->update([
                'title' => null,
                'slug' => null,
            ]);
        });
    }


}
