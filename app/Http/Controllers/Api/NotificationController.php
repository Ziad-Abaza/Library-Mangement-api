<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\GeneralNotification;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\NotificationResource;
use App\Jobs\SendNotificationJob;

class NotificationController extends Controller
{
    protected $environment;

    public function __construct()
    {
        $this->environment = env('DEV_ENVIRONMENT', false);
        if ($this->environment) {
            Auth::loginUsingId(1);
        }
    }

    public function sendToAllUsers(Request $request)
    {
        $request->validate(['message' => 'required|string']);

        User::where('is_active', true)->chunkById(100, function ($users) use ($request) {
            foreach ($users as $user) {
                SendNotificationJob::dispatch($user, $request->message)->delay(now()->addSeconds(5));
            }
        });

        return response()->json(['message' => 'Notification sent to all active users in background.']);
    }

    public function sendToSpecificUser(Request $request, $id)
    {
        $request->validate(['message' => 'required|string']);

        $user = User::findOrFail($id);
        $user->notify(new GeneralNotification($request->message));

        return response()->json(['message' => 'Notification sent to user.']);
    }

    public function getUserNotifications()
    {
        $user = auth()->user();
        return NotificationResource::collection($user->unreadNotifications);
    }

    public function getReadNotifications()
    {
        $user = auth()->user();
        $readNotifications = $user->notifications()->whereNotNull('read_at')->get();

        return NotificationResource::collection($readNotifications);
    }

    public function deleteAllNotifications()
    {
        $user = auth()->user();
        $user->notifications()->forceDelete();

        return response()->json(['message' => 'All notifications deleted.']);
    }

    public function markAsRead($notificationId)
    {
        $user = auth()->user();
        $notification = $user->notifications()->findOrFail($notificationId);
        $notification->update(['read_at' => now()]);

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function deleteNotification($notificationId)
    {
        $user = auth()->user();
        $notification = $user->notifications()->findOrFail($notificationId);
        $notification->delete();

        return response()->json(['message' => 'Notification deleted.']);
    }
}
