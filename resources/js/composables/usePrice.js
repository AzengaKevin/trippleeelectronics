export default function usePrice() {
    const formatPrice = (price) => {
        return new Intl.NumberFormat('en-KE', {
            // style: 'currency',
            currency: 'KES',
            // notation: "compact",
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }).format(price);
    };

    return {
        formatPrice,
    };
}
