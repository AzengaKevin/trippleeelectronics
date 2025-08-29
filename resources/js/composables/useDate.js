import dayjs from 'dayjs';
import localizedFormat from 'dayjs/plugin/localizedFormat';
import timezone from 'dayjs/plugin/timezone.js';
import { computed } from 'vue';

dayjs.extend(localizedFormat);
dayjs.extend(timezone);

export default function useDate() {
    const todaysDate = computed(() => dayjs().tz('Africa/Nairobi').format('YYYY-MM-DD'));

    const formatDate = (date, format = 'DD/MM/YYYY') => {
        if (!date) return null;
        return dayjs(date).format(format);
    };

    const nowIsDurationPast = (timeToCheck, value = 2, unit = 'minutes') => {
        if (!timeToCheck) return false;

        const time = dayjs(timeToCheck);

        const now = dayjs();

        return time.add(value, unit).isBefore(now);
    };

    return {
        todaysDate,
        formatDate,
        nowIsDurationPast,
    };
}
