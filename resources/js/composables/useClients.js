import axios from 'axios';

export default function useClients() {
    const loadClients = (query, setOptions) => {
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

    const processFormClient = (client) => {
        return {
            client: client
                ? {
                      value: client?.id || '',
                      label: client?.name || '',
                      email: client?.email || '',
                      phone: client?.phone || '',
                      kra_pin: client?.kra_pin || '',
                      type: client?.type || '',
                  }
                : null,
            id: '',
            type: '',
            name: '',
            email: '',
            phone: '',
            address: '',
            kra_pin: '',
            id_number: '',
        };
    };

    return {
        loadClients,
        processFormClient,
    };
}
