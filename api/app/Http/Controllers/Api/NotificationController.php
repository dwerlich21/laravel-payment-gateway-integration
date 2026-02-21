<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\NotificationRequest;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * NotificationController constructor.
     */
    public function __construct(NotificationService $service)
    {
        parent::__construct($service, new NotificationRequest);
    }

    /**
     * List notifications for authenticated user
     */
    public function index(Request $request): JsonResponse
    {
        return $this->handleWithoutTransaction(function () use ($request) {
            $unreadOnly = $request->boolean('unread_only', false) ?: null;
            $type = $request->filled('type') ? $request->get('type') : null;
            $limit = (int) $request->get('limit', 10);

            $data = $this->service->getForCurrentUser($unreadOnly, $type, $limit);

            return $this->successResponse($data);
        }, 'Erro ao buscar notificações');
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(): JsonResponse
    {
        return $this->handleWithoutTransaction(function () {
            $count = $this->service->getUnreadCount();

            return $this->successResponse(['count' => $count]);
        }, 'Erro ao buscar contagem de notificações');
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(string $id): JsonResponse
    {
        return $this->handleWithTransaction(function () use ($id) {
            $this->service->markAsRead((int) $id);

            return $this->successResponse(['message' => 'Notificação marcada como lida']);
        }, 'Erro ao marcar notificação como lida');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): JsonResponse
    {
        return $this->handleWithTransaction(function () {
            $count = $this->service->markAllAsRead();

            return $this->successResponse([
                'message' => 'Todas as notificações foram marcadas como lidas',
                'count'   => $count,
            ]);
        }, 'Erro ao marcar notificações como lidas');
    }

    /**
     * Delete a notification
     */
    public function destroy(string $id): JsonResponse
    {
        return $this->handleWithTransaction(function () use ($id) {
            $this->service->deleteNotification((int) $id);

            return $this->successResponse(['message' => 'Notificação deletada com sucesso']);
        }, 'Erro ao deletar notificação');
    }

    /**
     * Bulk delete notifications
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        return $this->handleWithTransaction(function () use ($request) {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return $this->errorResponse('Nenhuma notificação selecionada', 400);
            }

            $count = $this->service->bulkDeleteForCurrentUser($ids);

            return $this->successResponse(
                ['deleted' => $count],
                "{$count} notificação(ões) deletada(s) com sucesso"
            );
        }, 'Erro ao deletar notificações');
    }
}
