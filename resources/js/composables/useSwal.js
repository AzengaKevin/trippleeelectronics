import Swal from 'sweetalert2';

export default function useSwal() {
    const errorUrl = import.meta.env.VITE_ERROR_SOUND;

    const errorSound = new Audio(errorUrl);

    const successUrl = import.meta.env.VITE_SUCCESS_SOUND;

    const successSound = new Audio(successUrl);

    const notificationUrl = import.meta.env.VITE_NOTIFICATION_SOUND;

    const notificationSound = new Audio(notificationUrl);

    const showInertiaErrorsSwal = (errors) => {
        if (Object.keys(errors).length > 0) {
            errorSound.play();

            const timerValue = 3000;

            const errorList = Object.values(errors)
                .map((msg) => `• ${msg}`)
                .join('<br>');

            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: errorList,
                confirmButtonText: 'OK',
                timer: timerValue,
            });
        }
    };

    const showFeedbackSwal = (feedback) => {
        const timerValue = feedback.type === 'success' ? 1500 : 3000;

        const sound = feedback.type === 'success' ? successSound : errorSound;

        sound.play();

        Swal.fire({
            text: feedback.message,
            icon: feedback.type,
            confirmButtonText: 'OK',
            timer: timerValue,
        });
    };

    const showNotificationSwal = (notification) => {
        const timerValue = 1500;

        notificationSound.play();

        Swal.fire({
            text: notification.message,
            icon: notification.type,
            confirmButtonText: 'OK',
            timer: timerValue,
        });
    };

    return {
        showInertiaErrorsSwal,
        showFeedbackSwal,
        showNotificationSwal,
    };
}
