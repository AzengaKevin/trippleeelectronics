import axios from 'axios';

export default function useCustomers() {
    const loadCustomers = (query, setOptions) => {
        axios.get(route('api.clients.index'), { params: { query, limit: 5, perPage: null } }).then((results) => {
            setOptions(
                results.data.data.map(({ id, name, email, phone, kra_pin, type }) => ({
                    value: id,
                    label: name,
                    email,
                    phone,
                    kra_pin,
                    type,
                })),
            );
        });
    };

    const processFormCustomer = (order) => {
        return {
            customer: order.customer
                ? {
                      value: order?.customer?.id || '',
                      label: order?.customer?.name || '',
                      email: order?.customer?.email || '',
                      phone: order?.customer?.phone || '',
                      kra_pin: order?.customer?.kra_pin || '',
                      type: order?.customer?.type || '',
                  }
                : null,
            id: order?.customer_id || '',
            type: order?.customer_type || '',
            name: '',
            email: '',
            phone: '',
            address: '',
            kra_pin: '',
            id_number: '',
        };
    };

    return {
        loadCustomers,
        processFormCustomer,
    };
}
