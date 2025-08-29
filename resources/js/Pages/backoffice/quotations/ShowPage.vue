<script setup>
import AppAlert from '@/components/AppAlert.vue';
import useDate from '@/composables/useDate';
import usePrice from '@/composables/usePrice';
import useQuotations from '@/composables/useQuotations';
import useSwal from '@/composables/useSwal';
import BackofficeLayout from '@/layouts/BackofficeLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    auth: {
        type: Object,
        required: true,
    },
    quotation: {
        type: Object,
        required: true,
    },
    feedback: {
        type: Object,
        default: null,
    },
});

const breadcrumbs = [
    {
        title: 'Dashboard',
        href: route('backoffice.dashboard'),
    },
    {
        title: 'Quotations',
        href: route('backoffice.quotations.index'),
    },
    {
        title: `Quotation #${props.quotation.reference}`,
        href: null,
    },
];

const { deleteQuotation } = useQuotations();
const { showInertiaErrorsSwal, showFeedbackSwal } = useSwal();
const { formatPrice } = usePrice();
const { formatDate } = useDate();

const quotationQuotation = computed(() => props.quotation.items.map((i) => i.sub_total).reduce((total, num) => total + num, 0));

watch(
    () => props.feedback,
    (newFeedback) => {
        if (newFeedback) {
            showFeedbackSwal(newFeedback);
        }
    },
    {
        immediate: true,
    },
);
</script>

<template>
    <Head>
        <title>Quotation Details</title>
    </Head>

    <BackofficeLayout :breadcrumbs="breadcrumbs" title="Quotation Details">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3 lg:justify-between">
                <div class="flex flex-wrap items-center gap-3">
                    <Link
                        v-if="auth.permissions.includes('update-quotations')"
                        :href="route('backoffice.quotations.edit', [quotation.id])"
                        class="btn btn-sm btn-info btn-outline rounded-full"
                    >
                        <font-awesome-icon icon="edit" />
                        Edit
                    </Link>
                    <button
                        v-if="auth.permissions.includes('delete-quotations')"
                        type="button"
                        @click="deletequotation(quotation)"
                        class="btn btn-sm btn-error btn-outline rounded-full"
                    >
                        <font-awesome-icon icon="trash-alt" />
                        Delete
                    </button>
                </div>
                <div class="flex-wrap-gap-3 flex items-center">
                    <a
                        target="_blank"
                        :href="route('backoffice.quotations.print', [quotation.id])"
                        class="btn btn-sm btn-primary btn-outline rounded-full"
                    >
                        <font-awesome-icon icon="print" />
                        Print
                    </a>
                </div>
            </div>

            <app-alert :feedback="feedback" />

            <div class="grid grid-cols-12 items-start gap-4">
                <div class="col-span-12 md:col-span-6">
                    <div class="card bg-base-100 shadow">
                        <div class="card-body lg-p-6 p-2 sm:p-4">
                            <div class="overflow-x-auto">
                                <table class="table w-full">
                                    <tbody>
                                        <tr>
                                            <th>Author</th>
                                            <td>{{ quotation.author?.name ?? '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>ID</th>
                                            <td>{{ quotation.id }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Reference</th>
                                            <td>{{ quotation.reference ?? '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Customer</th>
                                            <td>{{ quotation?.customer?.name ?? '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>User</th>
                                            <td>{{ quotation?.user?.name ?? '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Store</th>
                                            <td>{{ quotation?.store?.name ?? '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Amount</th>
                                            <td>{{ formatPrice(quotation.amount) }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Shipping Amount</th>
                                            <td>{{ quotation.shipping_amount ? formatPrice(quotation.shipping_amount) : '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Tax Amount</th>
                                            <td>{{ quotation.tax_amount ? formatPrice(quotation.tax_amount) : '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Discount Amount</th>
                                            <td>{{ quotation.discount_amount ? formatPrice(quotation.discount_amount) : '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Total Amount</th>
                                            <td>{{ quotation.total_amount ? formatPrice(quotation.total_amount) : '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Notes</th>
                                            <td>{{ quotation.notes ?? '-' }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Created At</th>
                                            <td>{{ formatDate(quotation.created_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <th>Updated At</th>
                                            <td>{{ formatDate(quotation.updated_at, 'YY-MM-DD HH:mm:ss') }}</td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-span-12 md:sticky md:top-0 md:col-span-6">
                    <div class="card bg-base-100 shadow">
                        <div class="card-body space-y-4">
                            <h2 class="text-xl font-semibold">Quotation Items</h2>
                            <div class="overflow-x-auto">
                                <table class="table w-full">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Item</th>
                                            <th>Quantity</th>
                                            <th>Discount</th>
                                            <th>Tax</th>
                                            <th>Price</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, index) in quotation.items" :key="item.id">
                                            <td>{{ index + 1 }}</td>
                                            <td>{{ item.item?.name ?? '-' }}</td>
                                            <td>{{ item.quantity ?? '-' }}</td>
                                            <td>{{ item.discount_rate ?? '-' }}</td>
                                            <td>{{ item.tax_rate ?? '-' }}</td>
                                            <td>{{ item.final_price_per_item ? formatPrice(item.final_price_per_item) : '-' }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="font-semibold">
                                        <tr>
                                            <td></td>
                                            <th colspan="4">Total</th>
                                            <td>{{ formatPrice(quotation.total_amount) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BackofficeLayout>
</template>
