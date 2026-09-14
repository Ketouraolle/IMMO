<?php

namespace App\Livewire;

use App\Models\Payment;
use Illuminate\Support\Carbon;
use Livewire\Component;

class NotificationBell extends Component
{
    public function open(string $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return $this->redirect($notification->data['url'] ?? route('dashboard'));
    }

    public function markAllRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }

    /**
     * Translate a stored notification into the viewer's language.
     *
     * @return array{0: string, 1: string} title and body
     */
    public function present(array $data): array
    {
        $params = $data['params'] ?? [];

        if (isset($params['date'])) {
            $params['date'] = Carbon::parse($params['date'])->translatedFormat('D d M');
        }
        if (isset($params['method'])) {
            $params['method'] = __(Payment::METHODS[$params['method']] ?? $params['method']);
        }

        $body = collect([$data['body'] ?? null, $data['suffix'] ?? null])
            ->filter()
            ->map(fn ($line) => __($line, $params))
            ->implode(' ');

        return [__($data['title'] ?? 'Notification'), $body];
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.notification-bell', [
            'notifications' => $user->notifications()->latest()->take(8)->get(),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }
}
