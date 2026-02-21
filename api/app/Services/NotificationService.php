<?php

namespace App\Services;

use App\Jobs\SendNotificationEmail;
use App\Repositories\NotificationRepository;

class NotificationService extends BaseService
{
    /**
     * NotificationService constructor.
     */
    public function __construct(NotificationRepository $repository)
    {
        parent::__construct($repository);
    }

    /**
     * Create a new notification
     */
    public function create(array $data): Notification
    {
        $notification = $this->repository->create([
            'user_id' => $data['user_id'],
            'type' => $data['type'],
            'title' => $data['title'],
            'message' => $data['message'],
            'data' => $data['data'] ?? null,
            'read' => false,
            'email_sent' => false,
        ]);

        // Dispatch email job if sendEmail flag is true
        if ($data['send_email'] ?? false) {
            SendNotificationEmail::dispatch($notification);
        }

        return $notification;
    }

    /**
     * Create a goal assigned notification
     */
    public function notifyGoalAssigned(int $userId, array $goalData): Notification
    {
        return $this->create([
            'user_id' => $userId,
            'type' => Notification::TYPE_GOAL_ASSIGNED,
            'title' => 'Nova meta atribuída',
            'message' => "Você recebeu uma nova meta: {$goalData['milestone_description']}",
            'data' => $goalData,
            'send_email' => true,
        ]);
    }

    /**
     * Create a goal deadline notification
     */
    public function notifyGoalDeadline(int $userId, array $goalData, int $daysRemaining): Notification
    {
        $dayText = $daysRemaining === 1 ? 'dia' : 'dias';

        return $this->create([
            'user_id' => $userId,
            'type' => Notification::TYPE_GOAL_DEADLINE,
            'title' => 'Prazo de meta se aproximando',
            'message' => "A meta \"{$goalData['milestone_description']}\" vence em {$daysRemaining} {$dayText}.",
            'data' => array_merge($goalData, ['days_remaining' => $daysRemaining]),
            'send_email' => true,
        ]);
    }

    /**
     * Create a goal completed notification
     */
    public function notifyGoalCompleted(int $userId, array $goalData): Notification
    {
        return $this->create([
            'user_id' => $userId,
            'type' => Notification::TYPE_GOAL_COMPLETED,
            'title' => 'Meta concluída',
            'message' => "A meta \"{$goalData['milestone_description']}\" foi marcada como concluída.",
            'data' => $goalData,
            'send_email' => false,
        ]);
    }

    /**
     * Get notifications for authenticated user
     */
    public function getForCurrentUser(?bool $unreadOnly = null, ?string $type = null, int $limit = 10): array
    {
        $data = $this->repository->getForUser(
            auth()->id(),
            $unreadOnly,
            $type,
            $limit
        );

        return [
            'data' => $data->items(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'per_page' => $data->perPage(),
            'total' => $data->total(),
        ];
    }

    /**
     * Get unread count for authenticated user
     */
    public function getUnreadCount(): int
    {
        return $this->repository->getUnreadCount(auth()->id());
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(int $id): bool
    {
        $notification = $this->repository->find($id);

        // Ensure user owns this notification
        if ($notification->user_id !== auth()->id()) {
            throw new \App\Exceptions\ForbiddenException('Você não tem permissão para acessar esta notificação.');
        }

        return $notification->markAsRead();
    }

    /**
     * Mark all notifications as read for authenticated user
     */
    public function markAllAsRead(): int
    {
        return $this->repository->markAllAsRead(auth()->id());
    }

    /**
     * Delete a notification with ownership check
     */
    public function deleteNotification(int $id): void
    {
        $notification = $this->repository->find($id);

        // Ensure user owns this notification
        if ($notification->user_id !== auth()->id()) {
            throw new \App\Exceptions\ForbiddenException('Você não tem permissão para deletar esta notificação.');
        }

        $this->repository->delete($id);
    }

    /**
     * Bulk delete notifications for authenticated user
     */
    public function bulkDeleteForCurrentUser(array $ids): int
    {
        return $this->repository->bulkDeleteForUser($ids, auth()->id());
    }
}
