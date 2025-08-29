import { router } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, ref } from 'vue';

export default function useStockLevels() {
    const stockLevels = ref([]);

    const deleteStockLevel = (stockLevel) => {
        const confirmed = confirm(`Are you sure you want to delete the stock Level: ${stockLevel.id}?`);

        if (confirmed) {
            router.delete(route('backoffice.stock-levels.destroy', [stockLevel.id]));
        }
    };

    const fetchProductStockLevels = async (productId) => {
        try {
            const response = await axios.get(route('api.products.stock-levels.index', [productId]));

            stockLevels.value = response.data.data;
        } catch (error) {
            console.error('Error fetching stock levels:', error);
        }
    };

    const fetchStockLevels = async ({ products = [], store = null }) => {
        try {
            const response = await axios.get(route('api.stock-levels.index'), {
                params: {
                    products,
                    store,
                },
            });

            stockLevels.value = response.data.data;
        } catch (error) {
            console.error('Error fetching stock levels:', error);
        }
    };

    const stockLevelsFormatted = computed(() => stockLevels.value.map((i) => `${i.store} = ${i.quantity ?? 0}`).join(','));

    return {
        stockLevels,
        stockLevelsFormatted,
        deleteStockLevel,
        fetchProductStockLevels,
        fetchStockLevels,
    };
}
