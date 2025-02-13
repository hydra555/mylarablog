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

    #[Rule('required|min:3|max:200')]
    public string $comment = '';

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
        $this->resetPage(); // Обновляем пагинацию
        $this->dispatch('commentUpdated'); // Обновляем список комментариев
    }

    #[Computed()]
    public function comments()
    {
        return $this->post->comments()->with('user')->latest()->paginate(5);
    }

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
        if (!$this->commentToDelete || $this->commentToDelete->user_id !== auth()->id()) {
            return;
        }

        $this->commentToDelete->delete();
        $this->commentToDelete = null;
        $this->dispatch('commentDeleted'); // Обновляем список комментариев
    }

    public function render()
    {
        return view('livewire.post-comments');
    }
}

