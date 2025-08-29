import { ref } from 'vue';

export default function useSteps() {
    const step = ref(1);

    const nextStep = () => {
        step.value++;
    };

    const prevStep = () => {
        step.value--;
    };
    return {
        step,
        nextStep,
        prevStep,
    };
}
