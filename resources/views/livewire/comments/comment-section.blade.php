<div>
    @if($show)
        {{-- Backdrop --}}
        <div
            class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm"
            wire:click="closeModal"
        ></div>

        {{-- Modal --}}
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 pointer-events-none">
            <div class="pointer-events-auto w-full max-w-2xl max-h-[85vh] flex flex-col rounded-xl bg-zinc-900 border border-zinc-700 shadow-xl">

                {{-- Header --}}
                <div class="flex items-center justify-between p-5 border-b border-zinc-700/50">
                    <h2 class="text-lg font-semibold text-white">Comments</h2>
                    <button
                        wire:click="closeModal"
                        class="text-zinc-400 hover:text-white transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Scrollable comments --}}
                <div class="flex-1 overflow-y-auto p-5 space-y-4">
                    @forelse($comments as $comment)
                        <div class="space-y-3">
                            {{-- Top level comment --}}
                            <div class="flex gap-3">
                                <flux:avatar
                                    size="sm"
                                    :src="$comment->user->profilePhotoUrl()"
                                    :name="$comment->user->name"
                                    color="auto"
                                    :color:seed="$comment->user->id"
                                />
                                <div class="flex-1 space-y-1">
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-sm font-medium text-white">{{ $comment->user->name }}</span>
                                        <span class="text-xs text-zinc-500">{{ $comment->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-sm text-zinc-300 leading-relaxed">{{ $comment->body }}</p>
                                    <div class="flex items-center gap-3 pt-0.5">
                                        @auth
                                            <button
                                                wire:click="replyTo({{ $comment->id }})"
                                                class="text-xs text-zinc-500 hover:text-zinc-300 transition-colors"
                                            >
                                                reply
                                            </button>
                                        @endauth
                                        @if(auth()->check() && (auth()->id() === $comment->user_id || auth()->user()->canManageGames()))
                                            <button
                                                wire:click="deleteComment({{ $comment->id }})"
                                                wire:confirm="Delete this comment?"
                                                class="text-xs text-zinc-500 hover:text-red-400 transition-colors"
                                            >
                                                delete
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Replies --}}
                            @if($comment->replies->isNotEmpty())
                                <div class="ml-11 space-y-3 border-l border-zinc-700/50 pl-4">
                                    @foreach($comment->replies as $reply)
                                        <div class="flex gap-3">
                                            <flux:avatar
                                                size="xs"
                                                :src="$reply->user->profilePhotoUrl()"
                                                :name="$reply->user->name"
                                                color="auto"
                                                :color:seed="$reply->user->id"
                                            />
                                            <div class="flex-1 space-y-1">
                                                <div class="flex items-baseline gap-2">
                                                    <span class="text-sm font-medium text-white">{{ $reply->user->name }}</span>
                                                    <span class="text-xs text-zinc-500">{{ $reply->created_at->diffForHumans() }}</span>
                                                </div>
                                                <p class="text-sm text-zinc-300 leading-relaxed">{{ $reply->body }}</p>
                                                @if(auth()->check() && (auth()->id() === $reply->user_id || auth()->user()->canManageGames()))
                                                    <button
                                                        wire:click="deleteComment({{ $reply->id }})"
                                                        wire:confirm="Delete this reply?"
                                                        class="text-xs text-zinc-500 hover:text-red-400 transition-colors"
                                                    >
                                                        delete
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-10">
                            <svg class="w-10 h-10 mx-auto mb-3 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <p class="text-sm text-zinc-500">No comments yet. Be the first!</p>
                        </div>
                    @endforelse
                </div>

                {{-- Footer z formularzem --}}
                <div class="p-5 border-t border-zinc-700/50">
                    @auth
                        @if($replyingTo)
                            <div class="flex items-center gap-2 text-xs text-zinc-500 mb-2">
                                <span>Replying to comment</span>
                                <button type="button" wire:click="cancelReply" class="underline hover:text-zinc-300 transition-colors">
                                    cancel
                                </button>
                            </div>
                        @endif

                        <form wire:submit="submit" class="flex gap-2">
                            <textarea
                                wire:model="body"
                                rows="2"
                                placeholder="{{ $replyingTo ? 'Write a reply...' : 'Write a comment...' }}"
                                class="flex-1 rounded-lg bg-zinc-800 border border-zinc-700 px-3 py-2 text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-zinc-500 resize-none"
                            ></textarea>
                            <button
                                type="submit"
                                class="self-end px-4 py-2 text-sm font-medium rounded-lg bg-amber-500/10 text-amber-400 border border-amber-500/20 hover:bg-amber-500/20 transition-colors whitespace-nowrap"
                            >
                                {{ $replyingTo ? 'Reply' : 'Send' }}
                            </button>
                        </form>

                        @error('body')
                            <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    @else
                        <p class="text-sm text-zinc-500">
                            <a href="{{ route('login') }}" wire:navigate class="text-amber-400 hover:text-amber-300">Log in</a> to leave a comment.
                        </p>
                    @endauth
                </div>

            </div>
        </div>
    @endif
</div>