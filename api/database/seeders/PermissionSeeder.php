<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */
        $this->createOrUpdatePermission('dashboard', 'Dashboard');
        $this->createOrUpdatePermission('dashboard.index', 'Visualizar Dashboard', $this->getParentId('dashboard'));

        /*
        |--------------------------------------------------------------------------
        | Usuários
        |--------------------------------------------------------------------------
        */
        $this->createModuleWithCrud('users', 'Usuários');
        $this->createOrUpdatePermission('users.change-active', 'Ativar/Desativar Usuário', $this->getParentId('users'));
        $this->createOrUpdatePermission('users.bulk-delete', 'Excluir em Massa', $this->getParentId('users'));
        $this->createOrUpdatePermission('users.bulk-change-active', 'Ativar/Desativar em Massa', $this->getParentId('users'));

        /*
        |--------------------------------------------------------------------------
        | Metas
        |--------------------------------------------------------------------------
        */
        $this->createModuleWithCrud('goals', 'Metas');
        $this->createOrUpdatePermission('goals.change-active', 'Ativar/Desativar Meta', $this->getParentId('goals'));
        $this->createOrUpdatePermission('goals.bulk-delete', 'Excluir em Massa', $this->getParentId('goals'));
        $this->createOrUpdatePermission('goals.bulk-change-active', 'Ativar/Desativar em Massa', $this->getParentId('goals'));
        $this->createOrUpdatePermission('goals.bulk-change-status', 'Alterar Status em Massa', $this->getParentId('goals'));
        $this->createOrUpdatePermission('goals.kanban', 'Visualizar Kanban', $this->getParentId('goals'));
        $this->createOrUpdatePermission('goals.update-status', 'Atualizar Status (Drag & Drop)', $this->getParentId('goals'));

        /*
        |--------------------------------------------------------------------------
        | Notificações
        |--------------------------------------------------------------------------
        */
        $this->createOrUpdatePermission('notifications', 'Notificações');
        $this->createOrUpdatePermission('notifications.index', 'Listar Notificações', $this->getParentId('notifications'));
        $this->createOrUpdatePermission('notifications.unread-count', 'Contagem de Não Lidas', $this->getParentId('notifications'));
        $this->createOrUpdatePermission('notifications.mark-read', 'Marcar como Lida', $this->getParentId('notifications'));
        $this->createOrUpdatePermission('notifications.mark-all-read', 'Marcar Todas como Lidas', $this->getParentId('notifications'));
        $this->createOrUpdatePermission('notifications.destroy', 'Excluir Notificação', $this->getParentId('notifications'));
        $this->createOrUpdatePermission('notifications.bulk-delete', 'Excluir em Massa', $this->getParentId('notifications'));

        /*
        |--------------------------------------------------------------------------
        | Relatórios
        |--------------------------------------------------------------------------
        */
        $this->createOrUpdatePermission('reports', 'Relatórios');
        $this->createOrUpdatePermission('reports.dashboard', 'Dashboard de Relatórios', $this->getParentId('reports'));
        $this->createOrUpdatePermission('reports.goals', 'Relatório de Metas', $this->getParentId('reports'));
        $this->createOrUpdatePermission('reports.users', 'Relatório de Usuários', $this->getParentId('reports'));
        $this->createOrUpdatePermission('reports.export-pdf', 'Exportar PDF', $this->getParentId('reports'));
        $this->createOrUpdatePermission('reports.export-excel', 'Exportar Excel', $this->getParentId('reports'));

        /*
        |--------------------------------------------------------------------------
        | Logs de Auditoria
        |--------------------------------------------------------------------------
        */
        $this->createOrUpdatePermission('audit-logs', 'Logs de Auditoria');
        $this->createOrUpdatePermission('audit-logs.index', 'Listar Logs', $this->getParentId('audit-logs'));
        $this->createOrUpdatePermission('audit-logs.show', 'Visualizar Detalhes', $this->getParentId('audit-logs'));
        $this->createOrUpdatePermission('audit-logs.stats', 'Visualizar Estatísticas', $this->getParentId('audit-logs'));

        // Atribuir todas as permissões aos usuários existentes
        $this->createPermissionsToUsers();
    }

    /**
     * Cria ou atualiza uma permissão
     */
    private function createOrUpdatePermission(string $name, ?string $description = null, ?int $parentId = null): Permission
    {
        $slug = str_replace('.', '/', $name);

        return Permission::updateOrCreate(
            ['slug' => $slug],
            [
                'name'        => $name,
                'description' => $description,
                'parent_id'   => $parentId,
            ]
        );
    }

    /**
     * Obtém o ID de uma permissão pai pelo nome
     */
    private function getParentId(string $name): ?int
    {
        $parent = Permission::where('name', $name)->first();

        return $parent?->id;
    }

    /**
     * Cria um módulo pai e suas permissões CRUD
     */
    private function createModuleWithCrud(string $module, string $moduleName): void
    {
        $parent = $this->createOrUpdatePermission($module, $moduleName);
        $this->createCrudPermissions($module, $moduleName, $parent->id);
    }

    /**
     * Cria as permissões CRUD para um recurso específico
     * Nomes compatíveis com apiResource do Laravel: index, store, show, update, destroy
     */
    private function createCrudPermissions(string $resource, string $resourceName, int $parentId): void
    {
        $actions = [
            'index'   => "Listar {$resourceName}",
            'store'   => "Criar {$resourceName}",
            'show'    => "Visualizar {$resourceName}",
            'edit'    => "Editar {$resourceName}",
            'destroy' => "Excluir {$resourceName}",
        ];

        foreach ($actions as $action => $description) {
            $this->createOrUpdatePermission("{$resource}.{$action}", $description, $parentId);
        }
    }

    /**
     * Atribui todas as permissões aos usuários existentes
     */
    private function createPermissionsToUsers(): void
    {
        $permissions = Permission::pluck('id')->toArray();
        $users = User::where('id', '<', 3)
            ->get();

        foreach ($users as $user) {
            $user->permissions()->sync($permissions);
        }
    }
}
