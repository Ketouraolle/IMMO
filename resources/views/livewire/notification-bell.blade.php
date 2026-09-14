<div class="notif" wire:poll.30s x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
    <button type="button" class="icon-btn" @click="open = !open" :aria-expanded="open" aria-label="Notifications">
        <i class="bi bi-bell"></i>
        @if($unreadCount)
            <span class="notif__dot">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
    </button>

    <div class="notif__panel" x-show="open" x-transition.origin.top.right x-cloak>
        <div class="notif__head">
            <span>Notifications</span>
            @if($unreadCount)
                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" wire:click="markAllRead">Mark all as read</button>
            @endif
        </div>
        <div class="notif__list">
            @forelse($notifications as $n)
                <button type="button" class="notif__item {{ $n->read_at ? '' : 'is-unread' }}" wire:click="open('{{ $n->id }}')" wire:key="notif-{{ $n->id }}">
                    <span class="notif__icon"><i class="bi {{ $n->data['icon'] ?? 'bi-bell' }}"></i></span>
                    <span>
                        <span class="notif__title d-block">{{ $n->data['title'] ?? 'Notification' }}</span>
                        <span class="notif__body d-block">{{ $n->data['body'] ?? '' }}</span>
                        <span class="notif__time d-block">{{ $n->created_at->diffForHumans() }}</span>
                    </span>
                </button>
            @empty
                <div class="text-center text-muted small py-5">
                    <i class="bi bi-bell-slash d-block fs-4 mb-2 opacity-50"></i>
                    You're all caught up
                </div>
            @endforelse
        </div>
    </div>
</div>
