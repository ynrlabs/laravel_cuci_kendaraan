<?php

namespace App\Http\Controllers\Api\V1\Notification;

use App\Http\Controllers\Controller;
use App\Http\Requests\Notification\UpdateFcmTokenRequest;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get customer's notifications
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', 'string', 'in:email,push,sms,in_app'],
            'read' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $user = $request->user();

        $query = NotificationLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        if (!empty($validated['type'])) {
            $query->where('type', $validated['type']);
        }

        if (isset($validated['read'])) {
            if ($validated['read']) {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }

        $perPage = $validated['per_page'] ?? 20;
        $notifications = $query->paginate($perPage);

        return response()->json([
            'data' => $notifications->getCollection()->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'body' => $notification->body,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at?->format('Y-m-d H:i:s'),
                    'sent_at' => $notification->sent_at?->format('Y-m-d H:i:s'),
                    'created_at' => $notification->created_at?->format('Y-m-d H:i:s'),
                ];
            }),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(string $id): JsonResponse
    {
        $user = Auth::user();

        $notification = NotificationLog::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return response()->json([
            'message' => 'Notifikasi ditandai sudah dibaca.',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();

        NotificationLog::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'message' => 'Semua notifikasi ditandai sudah dibaca.',
        ]);
    }

    /**
     * Update FCM token for push notifications
     */
    public function updateFcmToken(UpdateFcmTokenRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        // Store/update FCM token in user's metadata or a separate table
        // For simplicity, we'll store in user's avatar field or create a device_tokens table
        // Here we'll use a simple approach - store in user's metadata via update
        
        // In production, you might want a separate device_tokens table
        $user->update([
            'fcm_token' => $validated['fcm_token'],
            'device_type' => $validated['device_type'] ?? 'unknown',
            'device_id' => $validated['device_id'] ?? null,
        ]);

        return response()->json([
            'message' => 'FCM token berhasil diperbarui.',
        ]);
    }

    /**
     * Get unread notification count
     */
    public function unreadCount(): JsonResponse
    {
        $user = Auth::user();

        $count = NotificationLog::where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'unread_count' => $count,
        ]);
    }
}