<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\Comment;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class PostComments extends Component
{
    use WithPagination;

    public Post $post;
    public ?Comment $commentToDelete = null;
    public ?Comment $commentToEdit = null;

    #[Rule('required|min:3|max:200')]
    public string $comment = '';

    #[Rule('required|min:3|max:200')]
    public string $commentText = '';

    protected $listeners = ['commentUpdated' => '$refresh', 'commentDeleted' => '$refresh'];

    public function postComment()
    {
        if (auth()->guest()) {
            return;
        }

        $this->validateOnly('comment');

        $this->post->comments()->create([
            'comment' => $this->comment,
            'user_id' => auth()->id()
        ]);

        $this->reset('comment');
        $this->resetPage();
        $this->dispatch('commentUpdated');
    }

    #[Computed()]
    public function comments()
    {
        return $this->post->comments()->with('user')->latest()->paginate(5);
    }

    // === Функции удаления ===
    public function confirmDeleteComment($commentId)
    {
        $this->commentToDelete = Comment::find($commentId);
    }

    public function cancelDeleteComment()
    {
        $this->commentToDelete = null;
    }

    public function deleteComment()
    {
        if (!$this->commentToDelete) {
            return;
        }

        // Перепроверяем, есть ли комментарий в базе перед удалением
        $comment = Comment::find($this->commentToDelete->id);

        if (!$comment || $comment->user_id !== auth()->id()) {
            return;
        }

        $comment->delete();
        $this->commentToDelete = null;

        $this->resetPage(); // Обновляем пагинацию после удаления
        $this->dispatch('commentDeleted');
    }

    // === Функции редактирования ===
    public function startEditing($commentId)
    {
        $this->commentToEdit = Comment::find($commentId);
        if ($this->commentToEdit) {
            $this->commentText = $this->commentToEdit->comment;
        }
    }

    public function cancelEditing()
    {
        $this->commentToEdit = null;
        $this->reset('commentText');
    }

    public function updateComment()
    {
        if (!$this->commentToEdit || $this->commentToEdit->user_id !== auth()->id()) {
            return;
        }

        $this->validateOnly('commentText');

        $this->commentToEdit->update([
            'comment' => $this->commentText,
        ]);

        $this->commentToEdit = null;
        $this->reset('commentText');
        $this->dispatch('commentUpdated');
    }

    public function render()
    {
        return view('livewire.post-comments');
    }
}



