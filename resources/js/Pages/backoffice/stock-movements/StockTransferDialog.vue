<script setup>
import AppCombobox from '@/components/AppCombobox.vue';
import InputError from '@/components/InputError.vue';
import InputLabel from '@/components/InputLabel.vue';
import useProducts from '@/composables/useProducts';
import useStockLevels from '@/composables/useStockLevels';

import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    stores: {
        type: Array,
        required: true,
    },
});

const page = usePage();

const auth = computed(() => page.props.auth);

const { loadProducts } = useProducts();
const { stockLevels, stockLevelsFormatted, fetchProductStockLevels } = useStockLevels();

const form = useForm({
    item: null,
    quantity: 1,
    from: auth.value.store?.id || null,
    to: null,
});

watch(
    () => form.item,
    (newItem) => {
        if (newItem) {
            fetchProductStockLevels(newItem.value);
        }
    },
);

const submit = () => {
    form.transform((data) => ({
        ...data,
        item: data.item?.value,
    })).post(route('backoffice.stock-movements.transfer'), {
        onSuccess: () => {
            form.reset();
            stockTransferDialog.value.close();
        },
        onError: (errors) => {
            console.error(errors);
        },
    });
};

const stockTransferDialog = ref(null);
</script>
<template>
    <dialog ref="stockTransferDialog" id="stockTransferDialog" class="modal">
        <form @submit.prevent="submit" class="modal-box max-h-[90vh] w-11/12 max-w-5xl space-y-4">
            <h3 class="text-lg font-bold">Inter Store Stock Transfer</h3>

            <div class="min-w-[32rem] space-y-2">
                <AppCombobox
                    v-model="form.item"
                    :label="'Item'"
                    :placeholder="'Select an item...'"
                    :load-options="loadProducts"
                    :required="true"
                    :with-custom-option="false"
                />
                <InputError :message="form.errors.item" />
                <output v-if="stockLevels.length && form.item">{{ stockLevelsFormatted }}</output>
            </div>

            <div class="space-y-2">
                <input-label for="from" value="From" />
                <select
                    id="from"
                    class="select select-bordered w-full"
                    v-model="form.from"
                    :disabled="!auth.user.roles.map((r) => r.name).includes('admin')"
                >
                    <option :value="null">Select Store</option>
                    <option v-for="store in props.stores" :key="store.id" :value="store.id">
                        {{ store.name }}
                    </option>
                </select>
                <input-error :message="form.errors.from" />
            </div>
            <div class="space-y-2">
                <input-label for="to" value="To" />
                <select id="to" class="select select-bordered w-full" v-model="form.to">
                    <option :value="null">Select Store</option>
                    <option v-for="store in props.stores" :key="store.id" :value="store.id">
                        {{ store.name }}
                    </option>
                </select>
                <input-error :message="form.errors.to" />
            </div>
            <div class="space-y-2">
                <InputLabel for="quantity" value="Quantity" />
                <input id="quantity" v-model="form.quantity" type="number" class="input input-bordered w-full" required autocomplete="off" />
                <InputError :message="form.errors.quantity" />
            </div>

            <div class="modal-action">
                <button type="button" @click="stockTransferDialog.close()" class="btn">Nevermind</button>
                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                    <span v-if="form.processing" class="loading loading-lg loading-spinner"></span>
                    Submit
                </button>
            </div>
        </form>
    </dialog>
</template>
