<?php

namespace App\Livewire\Articles;

use App\Models\Article;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class Index extends Component
{
    use WithPagination;

    public ?int $confirmingDeleteId = null;

    /**
     * Paginated list of published articles, newest first.
     * Admins additionally see drafts (no published_at yet).
     */
    #[Computed]
    public function articles(): LengthAwarePaginator
    {
        $query = Article::query()->orderByDesc('published_at')->orderByDesc('id');

        // Non-admins only see articles with a published_at in the past.
        if (!auth()->user()?->isAdmin()) {
            $query->published();
        }

        return $query->paginate(20);
    }

    /**
     * Two-step delete: first click sets confirmation state, second click commits.
     */
    public function confirmDelete(int $id): void
    {
        $this->authorizeAdmin();
        $this->confirmingDeleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->confirmingDeleteId = null;
    }

    public function delete(int $id): void
    {
        $this->authorizeAdmin();

        // Safety: only delete the article currently flagged for deletion.
        if ($this->confirmingDeleteId !== $id) {
            return;
        }

        Article::findOrFail($id)->delete();
        $this->confirmingDeleteId = null;
        $this->resetPage();
    }

    /**
     * Hard guard for any mutation. Throws 403 for non-admins.
     */
    private function authorizeAdmin(): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
    }

    public function render()
    {
        return view('livewire.articles.index');
    }
}