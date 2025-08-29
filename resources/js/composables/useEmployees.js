import { router } from '@inertiajs/vue3';

export default function useEmployees() {
    const deleteEmployee = (employee) => {
        const response = confirm(`Are you sure you want to delete ${employee.name}?`);

        if (response) {
            router.delete(route('backoffice.employees.destroy', [employee.id]), {
                preserveState: true,
                preserveScroll: true,
            });
        }
    };

    const suspendEmployee = (employee) => {
        const response = confirm(`Are you sure you want to suspend ${employee.name}?`);

        if (response) {
            router.post(route('backoffice.employees.suspend', [employee.id]), {
                preserveState: true,
                preserveScroll: true,
            });
        }
    };

    return {
        deleteEmployee,
        suspendEmployee,
    };
}
