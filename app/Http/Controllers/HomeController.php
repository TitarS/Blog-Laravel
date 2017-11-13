<?php

namespace App\Http\Controllers;

use App\Category;
use App\Tag;
use App\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index() {
        $posts = Post::paginate(5);

        return view('pages.index')->with('posts', $posts);
    }

    public function show($slug) {
        $post = Post::where('slug', $slug)->firstOrFail();
        $user = Auth::user();
        //dd(Auth::check());
        return view('pages.show', compact('post', 'user'));
    }

    public function tag($slug) {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $posts = $tag->posts()->paginate(2);

        return view('pages.list', compact('posts'));
    }

    public function category($slug) {
        $category = Category::where('slug', $slug)->firstOrFail();

        $posts = $category->posts()->paginate(2);

        return view('pages.list', compact('posts'));
    }
}
