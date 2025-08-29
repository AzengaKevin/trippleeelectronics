import useClients from '@/composables/useClients';
import usePrice from '@/composables/usePrice';
import useProducts from '@/composables/useProducts';
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

export default function useUpsertPurchase({ props = null }) {
    const itemBlueprint = {
        item: null,
        quantity: 0,
        cost: '',
        total_cost: '',
        editable: true,
    };

    const purchaseSupplierDialog = ref(null);

    const purchasePaymentsDialog = ref(null);

    const openPurchasePaymentsDialog = () => {
        if (purchasePaymentsDialog.value) {
            purchasePaymentsDialog.value.showModal();
        }
    };

    const closePurchasePaymentsDialog = () => {
        if (purchasePaymentsDialog.value) {
            purchasePaymentsDialog.value.close();
        }
    };

    const openPurchaseSupplierDialog = () => {
        if (purchaseSupplierDialog.value) {
            purchaseSupplierDialog.value.showModal();
        }
    };

    const closePurchaseSupplierDialog = () => {
        if (purchaseSupplierDialog.value) {
            purchaseSupplierDialog.value.close();
        }
    };

    const { formatPrice } = usePrice();

    const { loadProducts } = useProducts();

    const { loadClients, processFormClient } = useClients();

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
                    cost: item.cost,
                    total_cost: item.cost * item.quantity,
                    editable: false,
                };
            });
        } else {
            return [{ ...itemBlueprint }];
        }
    };

    const form = useForm({
        store: props?.purchase?.store_id || null,
        items: processFormItems(props?.purchase?.items || []),
        payments: [],
        supplier: processFormClient(props?.purchase?.supplier),
        amount: props?.order?.amount || null,
        shipping_amount: props?.order?.shipping_amount || 0,
        total_amount: props?.order?.total_amount || null,
    });

    const addItem = () => {
        form.items?.push({ ...itemBlueprint });
    };

    const removeItem = (index) => {
        form.items?.splice(index, 1);
    };

    const updateItem = (index, changeCost = false) => {
        const item = form.items[index];

        if (item.item) {
            if (changeCost) {
                item.cost = item.item?.cost || 0;
            }

            item.total_cost = item.quantity * item.cost;

            if (form.items[form.items.length - 1].item !== null) {
                addItem();
            }
        }
    };

    const filteredItems = computed(() => form.items.filter((item) => item.item !== null));

    const editableItems = computed(() => filteredItems.value.filter((item) => item.editable));

    const itemsSetupProperly = computed(() => filteredItems.value.length > 0);

    const readyToCreateAPurchase = computed(() => itemsSetupProperly.value);

    const readyToUpdateAPurchase = computed(
        () =>
            (itemsSetupProperly.value && editableItems.value.length > 0) ||
            (props.purchase?.shipping_amount !== form.shipping_amount && props.purchase) ||
            (props.purchase?.supplier_id !== form.supplier.id && props.purchase),
    );

    const amount = computed(() => {
        return filteredItems.value.reduce((acc, item) => {
            const cost = item.cost ? parseFloat(item.cost) : 0;
            const quantity = item.quantity ? parseInt(item.quantity) : 0;

            return acc + cost * quantity;
        }, 0);
    });

    const totalAmount = computed(() => parseFloat(form.shipping_amount) + amount.value);

    const createPurchase = () => {
        form.transform((data) => {
            data.items = filteredItems.value.map((item) => {
                return {
                    product: item.item.value,
                    quantity: item.quantity,
                    cost: item.cost,
                };
            });

            data.amount = amount.value;

            data.total_amount = totalAmount.value;

            data.supplier.id = data.supplier?.supplier?.value;

            return data;
        }).post(route('backoffice.purchases.store'), {
            onSuccess: () => form.reset(),
            onError: (errors) => console.error(errors),
        });
    };

    watch(
        () => form.supplier.client,
        (newClient) => {
            form.supplier.id = newClient?.id || '';
            form.supplier.type = newClient?.type || '';
            form.supplier.name = newClient?.label || '';
            form.supplier.email = newClient?.email || '';
            form.supplier.phone = newClient?.phone || '';
            form.supplier.address = newClient?.address || '';
            form.supplier.kra_pin = newClient?.kra_pin || '';
        },
    );

    watch(
        () => form.supplier,
        (newSupplier) => {
            form.supplier.id = newSupplier?.id || newSupplier.client?.value || null;
            form.supplier.type = newSupplier?.type || newSupplier.client?.type || null;
            form.supplier.name = newSupplier?.label || newSupplier.client?.label || null;
            form.supplier.email = newSupplier?.email || newSupplier.client?.email || null;
            form.supplier.phone = newSupplier?.phone || newSupplier.client?.phone || null;
            form.supplier.address = newSupplier?.address || newSupplier.client?.address || null;
            form.supplier.kra_pin = newSupplier?.kra_pin || newSupplier.client?.kra_pin || null;
        },
        {
            immediate: true,
            deep: true,
        },
    );

    return {
        form,
        itemBlueprint,
        purchaseSupplierDialog,
        purchasePaymentsDialog,
        filteredItems,
        editableItems,
        itemsSetupProperly,
        readyToCreateAPurchase,
        readyToUpdateAPurchase,
        totalAmount,
        amount,
        openPurchaseSupplierDialog,
        closePurchaseSupplierDialog,
        openPurchasePaymentsDialog,
        closePurchasePaymentsDialog,
        loadClients,
        loadProducts,
        formatPrice,
        createPurchase,
        addItem,
        removeItem,
        updateItem,
    };
}
