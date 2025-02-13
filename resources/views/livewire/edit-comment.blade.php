<div>
    @if ($isEditing)
        <textarea wire:model="commentText"
                  class="w-full p-2 text-sm text-gray-700 border-gray-200 rounded-lg bg-gray-50 focus:outline-none placeholder:text-gray-400"
                  rows="3"></textarea>

        <div class="flex justify-end mt-2">
            <button wire:click="updateComment"
                    class="px-4 py-2 text-sm text-white bg-green-500 rounded-md hover:bg-green-600">
                Save
            </button>
            <button wire:click="$set('isEditing', false)"
                    class="px-4 py-2 ml-2 text-sm text-gray-600 bg-gray-300 rounded-md hover:bg-gray-400">
                Cancel
            </button>
        </div>
    @else
        <p class="text-sm text-gray-700">{{ $comment->comment }}</p>
    @endif
</div>
