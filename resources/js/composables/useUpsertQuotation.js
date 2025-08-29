import useStockLevels from '@/composables/useStockLevels';
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

export default function useUpsertOrder({ props = null }) {
    const { stockLevels, fetchStockLevels } = useStockLevels();

    const currentItem = ref(null);

    const itemBlueprint = {
        item: null,
        quantity: 1,
        price: null,
        cost: null,
        total_price: null,
        taxable: false,
        tax_rate: null,
        discount_rate: null,
        tax_rate: null,
        on_hand: null,
        editable: true,
        existing: false,
    };

    const quotationCustomerDialog = ref(null);

    const openQuotationCustomerDialog = () => {
        if (quotationCustomerDialog.value) {
            quotationCustomerDialog.value.showModal();
        }
    };

    const closeQuotationCustomerDialog = () => {
        if (quotationCustomerDialog.value) {
            quotationCustomerDialog.value.close();
        }
    };

    const processFormItems = (items) => {
        if (items?.length) {
            return items.map((item) => {
                return {
                    item: {
                        ...item.item,
                        type: item.item_type || '',
                        value: item.item_id,
                        label: item.item.name,
                    },
                    quantity: item.quantity,
                    price: item.price,
                    cost: item.item.cost,
                    total_price: item.final_price_per_item * item.quantity,
                    on_hand: item.item.quantity || 0,
                    taxable: item.taxable || false,
                    tax_rate: item.tax_rate || null,
                    discount_rate: item.discount_rate || null,
                    editable: true,
                    existing: true,
                };
            });
        } else {
            return [{ ...itemBlueprint }];
        }
    };

    const processFormCustomer = (quotation) => {
        return {
            customer: quotation.customer
                ? {
                      value: quotation?.customer?.id || '',
                      label: quotation?.customer?.name || '',
                      email: quotation?.customer?.email || '',
                      phone: quotation?.customer?.phone || '',
                      kra_pin: quotation?.customer?.kra_pin || '',
                      type: quotation?.customer?.type || '',
                  }
                : null,
            id: quotation?.customer_id || '',
            type: quotation?.customer_type || '',
            name: '',
            email: '',
            phone: '',
            address: '',
            kra_pin: '',
            id_number: '',
        };
    };

    const form = useForm({
        store: props?.quotation?.store_id || props?.auth?.store?.id || null,
        items: processFormItems(props?.quotation?.items || []),
        customer: processFormCustomer(props?.quotation || {}),
        amount: props?.quotation?.amount || null,
        shipping_amount: props?.quotation?.shipping_amount || 0,
        total_amount: props?.quotation?.total_amount || null,
    });

    const addItem = () => {
        form.items?.push({ ...itemBlueprint });
    };

    const removeItem = (index) => {
        form.items?.splice(index, 1);
    };

    const updateItem = (index, changePrice = false) => {
        const item = form.items[index];

        if (item.item) {
            if (changePrice) {
                item.price = item.item?.price || 0;
            }

            const totalPrice = item.price * item.quantity;

            const discountAmount = item.discount_rate ? (totalPrice * item.discount_rate) / 100 : 0;

            const rate = item.tax_rate || item.item?.tax_rate || props.primaryTax?.rate || 0;

            const taxAmount = item.taxable ? (totalPrice * rate) / 100 : 0;

            item.total_price = totalPrice - discountAmount + taxAmount;

            item.on_hand = item.item?.on_hand || 0;

            item.cost = item.item?.cost || 0;

            item.tax_rate = item.taxable ? rate : null;

            if (form.items[form.items.length - 1]?.item !== null) {
                addItem();
            }

            if (currentItem.value === null || currentItem.value.value !== item.item.value) {
                currentItem.value = item.item;
            }
        }
    };

    const filteredItems = computed(() => form.items.filter((item) => item.item !== null));

    const editableItems = computed(() => filteredItems.value.filter((item) => item.editable));

    const newItems = computed(() => filteredItems.value.filter((item) => !item.existing));

    const itemsSetupProperly = computed(() => filteredItems.value.length > 0 && filteredItems.value.every((item) => !priceIsLessThanCost(item)));

    const totalAmount = computed(() => amount.value + parseFloat(form.shipping_amount));

    const amount = computed(() =>
        filteredItems.value.reduce((acc, item) => {
            const price = item.price || 0;

            const quantity = item.quantity || 0;

            const totalPrice = price * quantity;

            const discountAmount = (totalPrice * item.discount_rate) / 100;

            const rate = item.item?.tax_rate || props.primaryTax?.rate || 0;

            const taxAmount = (totalPrice * (item.taxable ? rate : 0)) / 100;

            return acc + totalPrice - discountAmount + taxAmount;
        }, 0),
    );

    watch(
        () => form.items,
        (items) => {
            const productsToFetch = items
                .filter((item) => item.item?.value !== null)
                .filter((item) => item.item?.type !== 'custom-item')
                .map((item) => item.item?.value);

            if (productsToFetch.length && props.auth?.store) {
                fetchStockLevels({
                    products: productsToFetch,
                });
            } else {
                stockLevels.value = [];
            }
        },
        { deep: true, immediate: true },
    );

    const currentItemStockLevels = computed(() => {
        if (!currentItem.value) {
            return [];
        }

        return stockLevels.value.filter((stock) => stock.product === currentItem.value.value);
    });

    const currentItemStockLevelsFormatted = computed(() => currentItemStockLevels.value.map((i) => `${i.store} = ${i.quantity ?? 0}`).join(','));

    watch(
        () => form.customer,
        (newCustomer) => {
            form.customer.id = newCustomer?.id || newCustomer.customer?.value || newCustomer.client?.value || null;
            form.customer.type = newCustomer?.type || newCustomer.customer?.type || newCustomer.client?.type || null;
            form.customer.name = newCustomer?.label || newCustomer.customer?.label || newCustomer.client?.label || null;
            form.customer.email = newCustomer?.email || newCustomer.customer?.email || newCustomer.client?.email || null;
            form.customer.phone = newCustomer?.phone || newCustomer.customer?.phone || newCustomer.client?.phone || null;
            form.customer.address = newCustomer?.address || newCustomer.customer?.address || newCustomer.client?.address || null;
            form.customer.kra_pin = newCustomer?.kra_pin || newCustomer.customer?.kra_pin || newCustomer.client?.kra_pin || null;
        },
        {
            immediate: true,
            deep: true,
        },
    );

    const priceIsLessThanCost = (item) => {
        if (item.item === null || item.item?.type === 'custom-item') {
            return false;
        }

        if (!item.editable) {
            return false;
        }

        if (!item.price || !item.cost) {
            return true;
        }

        return parseFloat(item.price) <= parseFloat(item.cost);
    };

    const resetForm = () => {
        form.reset();
        form.items = [{ ...itemBlueprint }];
        tenderedAmount.value = null;
        currentItem.value = null;
    };

    const readyToSave = computed(() => itemsSetupProperly.value);

    const readyToUpdate = computed(
        () =>
            (itemsSetupProperly.value && newItems.value.length > 0) ||
            (props.quotation?.shipping_amount !== form.shipping_amount && props.quotation) ||
            (props.quotation?.customer_id !== form.customer.id && props.quotation),
    );

    return {
        form,
        itemBlueprint,
        currentItem,
        filteredItems,
        itemsSetupProperly,
        totalAmount,
        amount,
        currentItemStockLevelsFormatted,
        currentItemStockLevels,
        readyToSave,
        readyToUpdate,
        quotationCustomerDialog,
        addItem,
        removeItem,
        updateItem,
        resetForm,
        processFormCustomer,
        priceIsLessThanCost,
        openQuotationCustomerDialog,
        closeQuotationCustomerDialog,
    };
}
