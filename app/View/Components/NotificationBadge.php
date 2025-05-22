<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationBadge extends Component
{
    /**
     * Nombre de notifications non lues
     *
     * @var int
     */
    public $count;

    /**
     * Liste des notifications non lues
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    public $notifications;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        $user = Auth::user();
        
        if ($user) {
            $this->notifications = Notification::where('user_id', $user->id)
                ->nonLues()
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
                
            $this->count = $this->notifications->count();
        } else {
            $this->notifications = collect();
            $this->count = 0;
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.notification-badge');
    }
}
