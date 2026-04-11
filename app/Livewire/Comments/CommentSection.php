<?php

namespace App\Livewire\Comments;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CommentSection extends Component
{
    public bool $show = false;
    public ?Model $model = null;
    public string $modelType = '';
    public int $modelId = 0;

    #[Validate('required|string|min:2|max:1000')]
    public string $body = '';

    public ?int $replyingTo = null;

    #[On('open-comments')]
    public function openModal(string $modelType, int $modelId): void
    {
        abort_unless(class_exists($modelType), 404);

        $this->model = app($modelType)->findOrFail($modelId);
        $this->modelType = $modelType;
        $this->modelId = $modelId;
        $this->show = true;
    }

    public function closeModal(): void
    {
        $this->reset('show', 'body', 'replyingTo', 'modelType', 'modelId');
        $this->model = null;
    }

    public function submit(): void
    {
        if (!auth()->check() || !$this->model) {
            return;
        }

        $this->validate();

        $this->model->comments()->create([
            'user_id'   => auth()->id(),
            'parent_id' => $this->replyingTo,
            'body'      => $this->body,
        ]);

        $this->reset('body', 'replyingTo');
    }

    public function replyTo(int $commentId): void
    {
        $this->replyingTo = $commentId;
        $this->reset('body');
    }

    public function cancelReply(): void
    {
        $this->reset('replyingTo', 'body');
    }

    public function deleteComment(int $commentId): void
    {
        $comment = Comment::findOrFail($commentId);

        $user = auth()->user();
        if (!$user || ($comment->user_id !== $user->id && !$user->canManageGames())) {
            return;
        }

        $comment->delete();
    }

    public function render()
    {
        return view('livewire.comments.comment-section', [
            'comments' => $this->model
                ? $this->model->topLevelComments()->get()
                : collect(),
        ]);
    }
}