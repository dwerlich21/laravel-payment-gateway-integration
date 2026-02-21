<?php

namespace App\Repositories;

use App\Models\Notification;

class NotificationRepository extends BaseRepository
{
    /**
     * NotificationRepository constructor.
     */
    public function __construct(Notification $model)
    {
        parent::__construct($model);
    }

    /**
     * Get unread count for a user
     */
    public function getUnreadCount(int $userId): int
    {
        return $this->model
            ->forUser($userId)
            ->unread()
            ->count();
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead(int $userId): int
    {
        return $this->model
            ->forUser($userId)
            ->unread()
            ->update(['read' => true]);
    }

    /**
     * Get notifications for a user with optional filters
     */
    public function getForUser(int $userId, ?bool $unreadOnly = null, ?string $type = null, int $limit = 10)
    {
        $query = $this->model
            ->forUser($userId)
            ->orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->unread();
        }

        if ($type) {
            $query->byType($type);
        }

        return $query->paginate($limit);
    }

    /**
     * Bulk delete notifications for a specific user
     */
    public function bulkDeleteForUser(array $ids, int $userId): int
    {
        return $this->model
            ->whereIn('id', $ids)
            ->where('user_id', $userId)
            ->delete();
    }
}
