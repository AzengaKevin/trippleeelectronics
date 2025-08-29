import { useFieldArray, useForm } from 'vee-validate';
import { computed } from 'vue';
import * as yup from 'yup';

export default function useOrderForm({ props = null }) {
    const validationSchema = yup.object({
        store: yup.string().required('Store is required'),
        customer: yup.object({
            value: yup.string(),
            label: yup.string(),
            email: yup.string().email('Invalid email format').nullable(),
            phone: yup.string().nullable(),
            kra_pin: yup.string().nullable(),
            type: yup.string().nullable(),
        }),
        shipping_amount: yup.number().min(0).default(0),
        accept_partial_payment: yup.boolean(),
        pay_later: yup.boolean(),
        items: yup.array().of(
            yup.object({
                item: yup
                    .object({
                        value: yup.mixed().required('Item is required'),
                        label: yup.string(),
                        type: yup.string(),
                    })
                    .required('Item is required'),
                quantity: yup.number().min(1, 'Must be at least 1').required('Quantity is required'),
                price: yup.number().min(0, 'Must be greater than 0').required('Price is required'),
            }),
        ),
    });

    const orderItemBluePrint = {
        item: null,
        quantity: 1,
        price: 0,
        total_price: null,
        on_hand: null,
    };

    const setupFormItems = (items) => {
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
                    total_price: item.price * item.quantity,
                    on_hand: item.item.quantity || 0,
                };
            });
        } else {
            return [{ ...orderItemBluePrint }];
        }
    };

    const setupFormCustomer = (order) => {
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

    const initialValues = {
        store: props?.order?.store_id || props?.auth?.store?.id || null,
        items: setupFormItems(props?.order?.items || []),
        payments: [],
        customer: setupFormCustomer(props?.order || {}),
        amount: props?.order?.amount || null,
        shipping_amount: props?.order?.shipping_amount || 0,
        total_amount: props?.order?.total_amount || null,
        accept_partial_payment: false,
        pay_later: false,
    };

    const { handleSubmit, resetForm, values, errors, isSubmitting } = useForm({
        initialValues,
        validationSchema,
    });

    const { fields: items, remove: removeItem, push: addItemToForm, update: updateItemInForm } = useFieldArray('items');

    const addItem = () => {
        addItemToForm({ ...orderItemBluePrint });
    };

    const updateItem = (index, changePrice = false) => {
        const item = items.value[index];
        if (item.item) {
            const newPrice = changePrice ? item.item?.price || 0 : item.price;
            const updated = {
                item: item.item,
                price: parseFloat(newPrice || 0),
                quantity: parseInt(item.quantity || 1),
                total_price: parseFloat(newPrice || 0) * parseInt(item.quantity || 1),
                on_hand: item.item?.on_hand || 0,
            };

            updateItemInForm(index, updated);

            if (items.value[items.value.length - 1].item) {
                addItem();
            }
        }
    };

    const amount = computed(() =>
        items.value.reduce((acc, item) => {
            const price = parseFloat(item.price || 0);
            const quantity = parseInt(item.quantity || 0);
            const discount = parseFloat(item.discount || 0);
            return acc + price * quantity - discount;
        }, 0),
    );

    const totalAmount = computed(() => amount.value + parseFloat(values.shipping_amount || 0));

    return {
        orderItemBluePrint,
        handleSubmit,
        values,
        errors,
        isSubmitting,
        items,
        amount,
        totalAmount,
        addItem,
        removeItem,
        updateItem,
        resetForm,
    };
}
