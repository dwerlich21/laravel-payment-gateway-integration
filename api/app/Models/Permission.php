<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     * Atributos que podem ser preenchidos em massa
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
    ];

    /**
     * Relação com a permissão pai
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Permission::class, 'parent_id');
    }

    /**
     * Relação com as permissões filhas
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(Permission::class, 'parent_id');
    }

    /**
     * Obtenha os usuários que possuem esta permissão.
     * Get the users that have this permission.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_permissions');
    }
    /**
     * Escopo para obter permissões de nível superior (sem pai)
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Obtém todas as permissões com hierarquia
     *
     * @return array
     */
    public static function getHierarchical()
    {
        $rootPermissions = self::root()->with('children')->get();
        return $rootPermissions->toArray();
    }
}
