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

        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->notify(new GeneralNotification($request->message));

        return response()->json(['message' => 'Notification sent to user.']);
    }

    public function getUserNotifications()
    {
        $userID = auth()->id();
        $user = User::find($userID);

        if (!$user) {
            return response()->json([]);
        }

        return NotificationResource::collection($user->unreadNotifications);
    }

    public function getReadNotifications()
    {
        $userID = auth()->id();
        $user = User::find($userID);

        if (!$user) {
            return response()->json([]);
        }

        $readNotifications = $user->notifications()->whereNotNull('read_at')->get();

        return NotificationResource::collection($readNotifications);
    }

    public function deleteAllNotifications()
    {
        $userID = auth()->id();
        $user = User::find($userID);

        if (!$user) {
            return response()->json([]);
        }

        $user->notifications()->forceDelete();

        return response()->json(['message' => 'All notifications deleted.']);
    }

    public function markAsRead($notificationId)
    {
        $userID = auth()->id();
        $user = User::find($userID);

        if (!$user) {
            return response()->json([]);
        }

        $notification = $user->notifications()->find($notificationId);
        if (!$notification) {
            return response()->json([]);
        }

        $notification->update(['read_at' => now()]);

        return response()->json(['message' => 'Notification marked as read.']);
    }

    public function deleteNotification($notificationId)
    {
        $userID = auth()->id();
        $user = User::find($userID);

        if (!$user) {
            return response()->json([]);
        }

        $notification = $user->notifications()->find($notificationId);
        if (!$notification) {
            return response()->json([]);
        }

        $notification->delete();

        return response()->json(['message' => 'Notification deleted.']);
    }
}
