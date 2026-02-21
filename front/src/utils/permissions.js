export const usePermissions = {
    setPermissions(permissions) {
        localStorage.setItem('permissions', JSON.stringify(permissions));
    },

    async getPermissions() {
        const permissions = localStorage.getItem('permissions');
        return permissions ? JSON.parse(permissions) : [];
    },

    async hasPermission(permissionSlug) {
        const permissions = await this.getPermissions();
        // Se for string, mantém a verificação original
        if (typeof permissionSlug === 'string') {
            return permissions.indexOf(permissionSlug) > -1;
        }

        // Se for array, verifica se pelo menos um item do array está nas permissões
        if (Array.isArray(permissionSlug)) {
            return permissionSlug.some(slug => permissions.indexOf(slug) > -1);
        }

        // Se não for nem string nem array, retorna false
        return false;
    },

    clearPermissions() {
        localStorage.removeItem('permissions');
    }
}; 