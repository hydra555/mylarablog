<?php

namespace App\Livewire;

use App\Models\Comment;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class EditComment extends Component
{
    public ?Comment $comment = null;
    public string $commentText = '';
    public bool $isEditing = false;

    protected $listeners = ['editComment' => 'loadComment'];

    public function loadComment($commentId)
    {
        $this->comment = Comment::find($commentId);

        if (!$this->comment || Auth::id() !== $this->comment->user_id) {
            return;
        }

        $this->commentText = $this->comment->comment;
        $this->isEditing = true;
    }

    public function updateComment()
    {
        if (!$this->comment || Auth::id() !== $this->comment->user_id) {
            return;
        }

        $this->validate([
            'commentText' => 'required|min:3|max:500',
        ]);

        $this->comment->update(['comment' => $this->commentText]);
        $this->isEditing = false;
        $this->dispatch('commentUpdated');
    }

    public function render()
    {
        return view('livewire.edit-comment');
    }
}

