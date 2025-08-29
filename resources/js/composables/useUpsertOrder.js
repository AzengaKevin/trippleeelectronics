import useSteps from '@/composables/useSteps';
import useStockLevels from '@/composables/useStockLevels';

import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

export default function useUpsertOrder({ props = null, existingOrderItemsEditable = true }) {
    const { step, nextStep, prevStep } = useSteps();

    const { stockLevels, fetchStockLevels } = useStockLevels();

    const currentItem = ref(null);

    const orderItemBluePrint = {
        item: null,
        quantity: 1,
        price: null,
        total_price: null,
        discount_rate: null,
        tax_rate: null,
        on_hand: null,
        cost: null,
        taxable: false,
        editable: true,
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
                    total_price: item.price * item.quantity,
                    on_hand: item.item.quantity || 0,
                    discount_rate: item.discount_rate || null,
                    tax_rate: item.tax_rate || null,
                    taxable: item.taxable || false,
                    editable: existingOrderItemsEditable,
                };
            });
        } else {
            return [{ ...orderItemBluePrint }];
        }
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

    const form = useForm({
        store: props?.order?.store_id || props?.auth?.store?.id || null,
        items: processFormItems(props?.order?.items || []),
        payments: [],
        customer: processFormCustomer(props?.order || {}),
        amount: props?.order?.amount || null,
        shipping_amount: props?.order?.shipping_amount || 0,
        total_amount: props?.order?.total_amount || null,
        accept_partial_payment: false,
        pay_later: false,
    });

    const addItem = () => {
        form.items?.push({ ...orderItemBluePrint });
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

            const rate = parseFloat(item.item?.tax_rate ?? props.primaryTax?.rate);

            const taxAmount = item.taxable ? (totalPrice * rate) / 100 : 0;

            item.tax_rate = item.taxable ? rate : null;

            item.total_price = totalPrice - discountAmount + taxAmount;

            item.on_hand = item.item?.on_hand || 0;

            item.cost = item.item?.cost || 0;

            if (form.items[form.items.length - 1]?.item !== null) {
                addItem();
            }

            if (currentItem.value === null || currentItem.value.value !== item.item.value) {
                currentItem.value = item.item;
            }
        }
    };

    const payLater = computed(() => form.pay_later);

    const filteredItems = computed(() => form.items.filter((item) => item.item !== null));

    const editableItems = computed(() => filteredItems.value.filter((item) => item.editable));

    const itemsSetupProperly = computed(
        () =>
            filteredItems.value.length > 0 &&
            filteredItems.value.every((item) => !itemIsInsufficientInTheCurrentStore(item)) &&
            filteredItems.value.every((item) => !priceIsLessThanCost(item)),
    );

    const tenderedAmount = ref(null);

    const tenderedEnoughMoney = computed(() => {
        if (tenderedAmount.value === null) {
            return false;
        }

        return tenderedAmount.value >= totalAmount.value;
    });

    const totalAmount = computed(() => amount.value + parseFloat(form.shipping_amount));

    const balanceAmount = computed(() => {
        if (tenderedAmount.value === null || totalAmount.value === null) {
            return null;
        }

        return tenderedAmount.value - totalAmount.value;
    });

    const acceptPartialPayment = computed(() => form.accept_partial_payment);

    const showOrderSummaryNextButton = computed(
        () => step.value === 1 && itemsSetupProperly.value && (tenderedEnoughMoney.value || payLater.value || acceptPartialPayment.value),
    );

    const showOrderSummaryNextButtonWithoutPayments = computed(() => step.value === 1 && itemsSetupProperly.value);

    const amount = computed(() =>
        filteredItems.value.reduce((acc, item) => {
            const price = item.price || 0;

            const quantity = item.quantity || 0;

            const totalPrice = price * quantity;

            const discountAmount = (totalPrice * item.discount_rate) / 100;

            const rate = parseFloat(item.item?.tax_rate ?? props.primaryTax?.rate);

            const taxAmount = (totalPrice * (item.taxable ? rate : 0)) / 100;

            return acc + totalPrice - discountAmount + taxAmount;
        }, 0),
    );

    watch(
        () => form.items,
        (items) => {
            step.value = 1;

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
            form.customer.id = newCustomer?.id || newCustomer.customer?.value || null;
            form.customer.type = newCustomer?.type || newCustomer.customer?.type || null;
            form.customer.name = newCustomer?.label || newCustomer.customer?.label || null;
            form.customer.email = newCustomer?.email || newCustomer.customer?.email || null;
            form.customer.phone = newCustomer?.phone || newCustomer.customer?.phone || null;
            form.customer.address = newCustomer?.address || newCustomer.customer?.address || null;
            form.customer.kra_pin = newCustomer?.kra_pin || newCustomer.customer?.kra_pin || null;
        },
        {
            immediate: true,
            deep: true,
        },
    );

    const itemIsInsufficientInTheCurrentStore = (item) => {
        if (!item.editable) {
            return false;
        }

        if (!props.auth?.store) {
            return false;
        }

        if (item.item === null || item.item?.type === 'custom-item') {
            return false;
        }

        const stock = stockLevels.value.find((stock) => stock.store_id === props.auth.store.id && stock.product === item.item.value);

        return stock ? stock.quantity < item.quantity : true;
    };

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
        form.items = [{ ...orderItemBluePrint }];
        tenderedAmount.value = null;
        currentItem.value = null;
    };

    const paymentsSetupCorrectly = computed(
        () => tenderedEnoughMoney.value || form.pay_later || (form.accept_partial_payment && form.payments?.length >= 1),
    );

    const readyToSave = computed(() => itemsSetupProperly.value && paymentsSetupCorrectly.value);

    const readyToUpdate = computed(
        () =>
            (itemsSetupProperly.value && editableItems.value.length > 0) ||
            (props.order?.shipping_amount !== form.shipping_amount && props.order) ||
            (props.order?.customer_id !== form.customer.id && props.order),
    );

    return {
        form,
        orderItemBluePrint,
        currentItem,
        filteredItems,
        itemsSetupProperly,
        tenderedAmount,
        tenderedEnoughMoney,
        totalAmount,
        balanceAmount,
        showOrderSummaryNextButton,
        showOrderSummaryNextButtonWithoutPayments,
        amount,
        currentItemStockLevelsFormatted,
        currentItemStockLevels,
        step,
        readyToSave,
        readyToUpdate,
        paymentsSetupCorrectly,
        nextStep,
        prevStep,
        addItem,
        removeItem,
        updateItem,
        resetForm,
        itemIsInsufficientInTheCurrentStore,
        processFormCustomer,
        priceIsLessThanCost,
    };
}
