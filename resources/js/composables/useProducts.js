import axios from 'axios';
import { ref } from 'vue';

export default function useProducts() {
    const products = ref([]);

    const fetchProducts = async ({ query = null, limit = null, perPage = null } = {}) => {
        try {
            const response = await axios.get(route('api.products.index'), {
                params: {
                    query,
                    limit,
                    perPage,
                },
            });

            products.value = response.data.data;
        } catch (error) {
            console.log(error);
        }
    };

    const loadProducts = (query, setOptions) => {
        axios.get(route('api.products.index'), { params: { query, limit: 5, perPage: null, includeVariants: true } }).then((results) => {
            setOptions(
                results.data.data.map(({ id, sku, name, slug, cost, price, type, quantity, pos_name, tax_rate }) => ({
                    value: id,
                    label: name,
                    sku,
                    slug,
                    cost,
                    price,
                    type,
                    quantity,
                    pos_name,
                    tax_rate,
                    on_hand: quantity,
                })),
            );
        });
    };

    return {
        products,
        fetchProducts,
        loadProducts,
    };
}
