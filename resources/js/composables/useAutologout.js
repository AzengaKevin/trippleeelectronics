import { onMounted, onUnmounted } from 'vue';

const AUTO_LOGOUT_TIME = 5 * 60 * 1000;
const WARNING_TIME = 10 * 1000;

let logoutTimeout = null;
let warningTimeout = null;

export default function useAutoLogout({ onLogout, onWarning }) {
    const resetTimers = () => {
        clearTimeout(logoutTimeout);

        clearTimeout(warningTimeout);

        const warningDelay = AUTO_LOGOUT_TIME - WARNING_TIME;

        warningTimeout = setTimeout(() => {
            onWarning(confirmStayLoggedIn);
        }, warningDelay);

        logoutTimeout = setTimeout(() => {
            onLogout();
        }, AUTO_LOGOUT_TIME);
    };

    const confirmStayLoggedIn = () => {
        resetTimers();
    };

    const activityHandler = () => {
        resetTimers();
    };

    onMounted(() => {
        resetTimers();
        window.addEventListener('mousemove', activityHandler);
        window.addEventListener('keydown', activityHandler);
    });

    onUnmounted(() => {
        clearTimeout(logoutTimeout);
        clearTimeout(warningTimeout);
        window.removeEventListener('mousemove', activityHandler);
        window.removeEventListener('keydown', activityHandler);
    });
}
