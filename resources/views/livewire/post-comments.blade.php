<div class="pt-10 mt-10 border-t border-gray-100 comments-box">
    <h2 class="mb-5 text-2xl font-semibold text-gray-900">Discussions</h2>

    @auth
        <textarea wire:model.defer="comment"
                  class="w-full p-4 text-sm text-gray-700 border-gray-200 rounded-lg bg-gray-50 focus:outline-none placeholder:text-gray-400"
                  cols="30" rows="7"></textarea>
        <button wire:click="postComment"
                class="inline-flex items-center justify-center h-10 px-4 mt-3 font-medium tracking-wide text-white transition duration-200 bg-gray-900 rounded-lg hover:bg-gray-800 focus:shadow-outline focus:outline-none">
            Post Comment
        </button>
    @else
        <a wire:navigate class="py-1 text-yellow-500 underline" href="{{ route('login') }}"> Login to Post Comments</a>
    @endauth

    <div class="px-3 py-2 mt-5 user-comments" wire:poll.5s>
        @forelse($this->comments as $comment)
            <div class="comment [&:not(:last-child)]:border-b border-gray-100 py-5">
                <div class="flex items-center justify-between mb-4 text-sm user-meta">
                    <div class="flex items-center">
                        <x-posts.author :author="$comment->user" size="sm" />
                        <span class="text-gray-500">. {{ $comment->created_at->diffForHumans() }}</span>
                    </div>

                    @if (Auth::check() && Auth::id() === $comment->user_id)
                        <div class="flex items-center space-x-2">
                            <!-- Кнопка редактирования (тонкий карандаш) -->
                            <button wire:click="$dispatch('editComment', { commentId: {{ $comment->id }} })"
                                    class="text-gray-400 hover:text-green-500 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.832 19.82a4.5 4.5 0 0 1-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 0 1 1.13-1.897L16.863 4.487Zm0 0L19.5 7.125" />
                                </svg>
                            </button>

                            <!-- Кнопка удаления (крестик) -->
                            <button wire:click="confirmDeleteComment({{ $comment->id }})"
                                    class="text-gray-400 hover:text-red-500 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>

                <div class="text-sm text-justify text-gray-700">
                    {{ $comment->comment }}
                </div>
            </div>
        @empty
            <div class="text-center text-gray-500">
                <span> No Comments Posted</span>
            </div>
        @endforelse
    </div>

    <div class="my-2">
        {{ $this->comments->links() }}
    </div>

    <!-- Модальное окно подтверждения удаления -->
    @if($commentToDelete)
        <div class="fixed inset-0 bg-gray-600 bg-opacity-50 flex justify-center items-center">
            <div class="bg-white p-5 rounded-lg shadow-lg">
                <p class="text-lg font-semibold">Удалить комментарий?</p>
                <p class="text-sm text-gray-600 mt-2">Вы уверены, что хотите удалить этот комментарий? Это действие необратимо.</p>
                <div class="mt-4 flex justify-end space-x-2">
                    <button wire:click="cancelDeleteComment"
                            class="px-4 py-2 text-sm text-gray-600 bg-gray-300 rounded-md hover:bg-gray-400">
                        Отмена
                    </button>
                    <button wire:click="deleteComment"
                            class="px-4 py-2 text-sm text-white bg-red-500 rounded-md hover:bg-red-600">
                        Удалить
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
