<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use App\Models\Article;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function show(string $slug): View
    {
        $article = Article::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return view('articles.show', compact('article'));
    }


    public function destroy(string $slug): RedirectResponse
    {
        $article = Article::where('slug', $slug)->firstOrFail();
        $article->delete();

        return redirect()
            ->route('articles.index')
            ->with('status', 'Article deleted.');
    }


}