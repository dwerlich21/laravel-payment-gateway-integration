<?php

namespace App\Http\Controllers\Api;

use App\Models\AuditLog;
use App\Services\AuditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuditController extends Controller
{
    public function __construct(
        protected AuditService $auditService
    )
    {
    }

    /**
     * List audit logs with filters and pagination
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'user_id',
            'action',
            'model_type',
            'start_date',
            'end_date',
            'search',
        ]);

        $perPage = $request->input('limit', 25);
        $orderBy = $request->input('order_by', 'created_at');
        $order = $request->input('order', 'desc');

        $query = AuditLog::with('user')
            ->orderBy($orderBy, $order);

        if (!empty($filters['user_id'])) {
            $query->byUser($filters['user_id']);
        }

        if (!empty($filters['action'])) {
            $query->byAction($filters['action']);
        }

        if (!empty($filters['model_type'])) {
            $query->byModelType($filters['model_type']);
        }

        if (!empty($filters['start_date']) || !empty($filters['end_date'])) {
            $query->dateRange($filters['start_date'] ?? null, $filters['end_date'] ?? null);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('description', 'like', "%{$filters['search']}%")
                    ->orWhere('user_name', 'like', "%{$filters['search']}%");
            });
        }

        $paginated = $query->paginate($perPage);

        // Transform items to add computed attributes
        $items = $paginated->getCollection()->transform(function ($log) {
            $log->action_label = AuditLog::getActionLabel($log->action);
            $log->action_color = AuditLog::getActionColor($log->action);
            $log->short_model_type = $log->short_model_type;
            $log->changed_fields = $log->getChangedFields();
            return $log;
        });

        return response()->json([
            'success' => true,
            'data'    => [
                'data'     => $items,
                'count'    => $paginated->total(),
                'pages'    => $paginated->lastPage(),
                'page'     => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
            ],
        ]);
    }

    /**
     * Show single audit log details
     */
    public function show(int $id): JsonResponse
    {
        $log = AuditLog::with('user')->findOrFail($id);

        $log->action_label = AuditLog::getActionLabel($log->action);
        $log->action_color = AuditLog::getActionColor($log->action);
        $log->changed_fields = $log->getChangedFields();

        return response()->json([
            'success' => true,
            'data'    => $log,
        ]);
    }

    /**
     * Get available actions
     */
    public function actions(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => AuditLog::getAvailableActions(),
        ]);
    }

    /**
     * Get available model types
     */
    public function modelTypes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->auditService->getModelTypes(),
        ]);
    }

    /**
     * Get audit statistics
     */
    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->auditService->getStats(),
        ]);
    }
}
