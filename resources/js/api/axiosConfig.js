import axios from 'axios';

const apiClient = axios.create({
    baseURL: import.meta.env.VITE_APP_URL,
    headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
    },
});

apiClient.interceptors.request.use(
    (config) => {
        if (token.value) {
            config.headers.Authorization = `Token ${token.value}`;
        }

        return config;
    },
    (error) => {
        return Promise.reject({
            message: 'Request error, please confirm whether the request made is correct one.',
            details: error.message,
        });
    },
);

apiClient.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response) {
            return Promise.reject({
                ...error.response.data,
                status: error.response.status,
            });
        } else {
            return Promise.reject({
                message: 'Network error, please try again later.',
            });
        }
    },
);

export default apiClient;
