<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class PostController extends Controller
{
    public function index()
    {

        $categories = Cache::remember('categories', now()->addDays(3), function () {
            return Category::whereHas('posts', function ($query) {
                $query->whereNotNull('published_at')->where('published_at', '<=', now());
            })->take(10)->get();
        });

        return view(
            'posts.index',
            [
                'categories' => $categories
            ]
        );
    }

    public function show(Post $post)
    {
        // Убираем ссылки вокруг изображений
        $post->body = preg_replace('/<a[^>]+href="[^"]+"[^>]*>(<img[^>]+>)<\/a>/i', '$1', $post->body);

        // Убираем подписи к изображениям
        $post->body = preg_replace('/<figcaption[^>]*>.*?<\/figcaption>/is', '', $post->body);

        return view('posts.show', ['post' => $post]);
    }


}
