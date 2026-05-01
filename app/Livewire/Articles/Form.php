<?php

namespace App\Livewire\Articles;

use App\Models\Article;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Form extends Component
{
    public ?Article $article = null;

    public string $type = Article::TYPE_NEWS;
    public string $title = '';
    public string $slug = '';
    public string $body = '';
    public ?string $publishedAt = null;

    /**
     * Mount accepts an optional Article. When present we're editing, otherwise creating.
     */
    public function mount(?Article $article = null): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        if ($article && $article->exists) {
            $this->article     = $article;
            $this->type        = $article->type;
            $this->title       = $article->title;
            $this->slug        = $article->slug;
            $this->body        = $article->body;
            $this->publishedAt = $article->published_at?->format('Y-m-d\TH:i');
        } else {
            // Default new articles to "now" so they're visible immediately on save.
            $this->publishedAt = now()->format('Y-m-d\TH:i');
        }
    }

    /**
     * Auto-generate slug from title only when creating a new article.
     * For existing articles we don't touch the slug — bookmarks would break.
     */
    public function updatedTitle(string $value): void
    {
        if ($this->article === null) {
            $this->slug = Str::slug($value);
        }
    }

    public function save()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $validated = $this->validate([
            'type'        => ['required', Rule::in([Article::TYPE_NEWS, Article::TYPE_UPDATE])],
            'title'       => ['required', 'string', 'max:255'],
            'slug'        => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('articles', 'slug')->ignore($this->article?->id),
            ],
            'body'        => ['required', 'string'],
            'publishedAt' => ['nullable', 'date'],
        ]);

        $payload = [
            'type'         => $validated['type'],
            'title'        => $validated['title'],
            'slug'         => $validated['slug'],
            'body'         => $validated['body'],
            'published_at' => $validated['publishedAt'] ?: null,
        ];

        $article = $this->article
            ? tap($this->article)->update($payload)
            : Article::create($payload);

        session()->flash('status', $this->article ? 'Article updated.' : 'Article created.');

        return $this->redirectRoute('articles.show', ['slug' => $article->slug], navigate: true);
    }

    public function render()
    {
        return view('livewire.articles.form');
    }
}