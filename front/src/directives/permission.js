import { usePermissions } from '@/utils/permissions';

export const permission = {
    mounted(el, binding) {
        usePermissions.hasPermission(binding.value)
            .then(result => {
                if (!result) {
                    el.remove();
                }
            })
    }
};