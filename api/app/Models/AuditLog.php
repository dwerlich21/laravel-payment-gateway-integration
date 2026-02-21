<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    // Action constants
    const ACTION_CREATED = 'created';
    const ACTION_UPDATED = 'updated';
    const ACTION_DELETED = 'deleted';
    const ACTION_LOGIN = 'login';
    const ACTION_LOGOUT = 'logout';
    const ACTION_LOGIN_FAILED = 'login_failed';
    const ACTION_EXPORTED_PDF = 'exported_pdf';
    const ACTION_EXPORTED_EXCEL = 'exported_excel';
    const ACTION_STATUS_CHANGED = 'status_changed';
    const ACTION_PASSWORD_RESET = 'password_reset';

    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'model_type',
        'model_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'description',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
    ];

    protected $appends = [
        'model_type_label',
    ];

    /**
     * Get the user that performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: filter by action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope: filter by model type
     */
    public function scopeByModelType($query, $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    /**
     * Scope: filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        return $query;
    }

    /**
     * Get action label in Portuguese
     */
    public static function getActionLabel(string $action): string
    {
        $labels = [
            self::ACTION_CREATED => 'Criado',
            self::ACTION_UPDATED => 'Atualizado',
            self::ACTION_DELETED => 'Deletado',
            self::ACTION_LOGIN => 'Login',
            self::ACTION_LOGOUT => 'Logout',
            self::ACTION_LOGIN_FAILED => 'Login Falhou',
            self::ACTION_EXPORTED_PDF => 'Exportou PDF',
            self::ACTION_EXPORTED_EXCEL => 'Exportou Excel',
            self::ACTION_STATUS_CHANGED => 'Status Alterado',
            self::ACTION_PASSWORD_RESET => 'Senha Resetada',
        ];

        return $labels[$action] ?? $action;
    }

    /**
     * Get action color for badge
     */
    public static function getActionColor(string $action): string
    {
        $colors = [
            self::ACTION_CREATED => 'success',
            self::ACTION_UPDATED => 'info',
            self::ACTION_DELETED => 'danger',
            self::ACTION_LOGIN => 'primary',
            self::ACTION_LOGOUT => 'secondary',
            self::ACTION_LOGIN_FAILED => 'warning',
            self::ACTION_EXPORTED_PDF => 'purple',
            self::ACTION_EXPORTED_EXCEL => 'purple',
            self::ACTION_STATUS_CHANGED => 'info',
            self::ACTION_PASSWORD_RESET => 'warning',
        ];

        return $colors[$action] ?? 'secondary';
    }

    /**
     * Get short model name
     */
    public function getShortModelTypeAttribute(): ?string
    {
        if (!$this->model_type) {
            return null;
        }
        return class_basename($this->model_type);
    }

    /**
     * Get model type label in Portuguese
     */
    public static function getModelTypeLabel(string $modelType): string
    {
        $shortName = class_basename($modelType);

        $labels = [
            'User' => 'Usuário',
            'Goal' => 'Meta',
            'Permission' => 'Permissão',
            'Notification' => 'Notificação',
            'UserAddress' => 'Endereço',
            'AuditLog' => 'Log de Auditoria',
        ];

        return $labels[$shortName] ?? $shortName;
    }

    /**
     * Get translated model type label attribute
     */
    public function getModelTypeLabelAttribute(): ?string
    {
        if (!$this->model_type) {
            return null;
        }
        return self::getModelTypeLabel($this->model_type);
    }

    /**
     * Get all available actions
     */
    public static function getAvailableActions(): array
    {
        return [
            ['value' => self::ACTION_CREATED, 'label' => self::getActionLabel(self::ACTION_CREATED)],
            ['value' => self::ACTION_UPDATED, 'label' => self::getActionLabel(self::ACTION_UPDATED)],
            ['value' => self::ACTION_DELETED, 'label' => self::getActionLabel(self::ACTION_DELETED)],
            ['value' => self::ACTION_LOGIN, 'label' => self::getActionLabel(self::ACTION_LOGIN)],
            ['value' => self::ACTION_LOGOUT, 'label' => self::getActionLabel(self::ACTION_LOGOUT)],
            ['value' => self::ACTION_LOGIN_FAILED, 'label' => self::getActionLabel(self::ACTION_LOGIN_FAILED)],
            ['value' => self::ACTION_EXPORTED_PDF, 'label' => self::getActionLabel(self::ACTION_EXPORTED_PDF)],
            ['value' => self::ACTION_EXPORTED_EXCEL, 'label' => self::getActionLabel(self::ACTION_EXPORTED_EXCEL)],
            ['value' => self::ACTION_STATUS_CHANGED, 'label' => self::getActionLabel(self::ACTION_STATUS_CHANGED)],
            ['value' => self::ACTION_PASSWORD_RESET, 'label' => self::getActionLabel(self::ACTION_PASSWORD_RESET)],
        ];
    }

    /**
     * Get changed fields between old and new values
     */
    public function getChangedFields(): array
    {
        if (!$this->old_values || !$this->new_values) {
            return [];
        }

        $changed = [];
        foreach ($this->new_values as $key => $newValue) {
            $oldValue = $this->old_values[$key] ?? null;
            if ($oldValue !== $newValue) {
                $changed[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changed;
    }
}
