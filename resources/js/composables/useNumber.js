export default function useNumber() {
    const formatMoney = (value) => {
        if (value === null || value === undefined) {
            return '';
        }
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(value);
    };

    const formatPercent = (value) => Number.parseFloat(value).toFixed(2) + '%';

    return {
        formatMoney,
        formatPercent,
    };
}
