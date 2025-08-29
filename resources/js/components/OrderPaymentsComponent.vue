<script setup>
import usePrice from '@/composables/usePrice';
import * as yup from 'yup';

import { router } from '@inertiajs/vue3';
import { ErrorMessage, Field, Form } from 'vee-validate';
import { computed, reactive, ref, watch } from 'vue';

const props = defineProps({
    paymentMethods: {
        type: Array,
        default: () => [],
    },
    paymentStatuses: {
        type: Array,
        default: () => [],
    },
    order: {
        type: Object,
        default: null,
    },
    totalAmount: {
        type: Number,
        default: null,
    },
});

const payments = ref([]);

const paymentsData = reactive({
    pay_later: false,
    accept_partial_payments: false,
    payments: payments.value,
});

watch(
    props,
    (newProps) => {
        payments.value =
            newProps.order?.payments?.map((payment) => ({
                method: payment.id,
                payment_method: payment.payment_method,
                amount: parseFloat(payment.amount) || 0,
                phone_number: payment.phone_number || '',
                existing: true,
            })) || [];

        newProps.paymentMethods.forEach((method) => {
            payments.value.push({
                method: method.value,
                payment_method: method,
                amount: 0,
                phone_number: '',
                existing: false,
            });
        });

        paymentsData.payments = payments.value;
    },
    {
        immediate: true,
    },
);

const addPayment = (paymentMethodValue) => {
    const payment = payments.value.find((p) => p.payment_method.value === paymentMethodValue);
    payment.amount = remainingAmount.value > 0 ? remainingAmount.value : 0;
};

const emit = defineEmits(['orderPaymentsUpdated', 'closeDialog']);

const currentOrder = computed(() => props.order);

const totalAmount = computed(() => props.totalAmount ?? currentOrder.value.total_amount);

const { formatPrice } = usePrice();

const paidAmount = computed(() => {
    return (
        currentOrder.value?.payments?.reduce((total, payment) => {
            return total + (payment.status === 'paid' ? parseFloat(payment.amount) : 0);
        }, 0) ?? 0
    );
});

const newPayments = computed(() => paymentsData.payments.filter((p) => !p.existing && p.amount > 0));

const tenderedAmount = computed(() => newPayments.value.reduce((total, payment) => total + payment.amount, 0));

const outstandingAmount = computed(() => totalAmount.value - paidAmount.value);

const remainingAmount = computed(() => totalAmount.value - (paidAmount.value + tenderedAmount.value));

const balanceAmount = computed(() => tenderedAmount.value - outstandingAmount.value);

const showSubmitButton = computed(
    () =>
        (!currentOrder.value &&
            (remainingAmount.value <= 0 ||
                (paymentsData.pay_later && newPayments.value.length === 0) ||
                (newPayments.value.length && paymentsData.accept_partial_payments))) ||
        (currentOrder.value && newPayments.value.length),
);

const paymentSchema = yup.object().shape({
    method: yup.string().required('Payment method is required'),
    amount: yup.number().required('Amount is required').typeError('Amount must be a number'),
    phone_number: yup.string().nullable(),
});

const schema = yup.object().shape({
    pay_later: yup.boolean(),
    accept_partial_payments: yup.boolean(),
    payments: yup.array().of(paymentSchema).min(1, 'At least one payment is required'),
});

const onSubmit = ({ payments, pay_later, accept_partial_payments }) => {
    const processedPayments = payments
        .filter((p) => !p.existing)
        .filter((p) => p.amount > 0)
        .map((p) => ({
            amount: p.amount,
            phone_number: p.phone_number,
            payment_method: p.method,
        }));

    if (currentOrder.value) {
        router.post(
            route('backoffice.orders.payments.store', [currentOrder.value.id]),
            {
                payments: processedPayments,
            },
            {
                onSuccess: () => {
                    emit('closeDialog');
                },
                onError: (errors) => {
                    console.error(errors);
                },
            },
        );
    } else {
        emit('orderPaymentsUpdated', {
            pay_later,
            accept_partial_payments,
            payments: processedPayments,
        });
    }
};
</script>
<template>
    <Form
        @submit="onSubmit"
        :validation-schema="schema"
        :initial-values="paymentsData"
        v-slot="{ errors }"
        class="modal-box max-h-[90vh] w-11/12 max-w-5xl space-y-4"
    >
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-2xl font-bold underline underline-offset-8">Order Payments</h3>

            <button class="btn btn-ghost btn-circle" type="button" @click="$emit('closeDialog')">
                <font-awesome-icon icon="times" size="lg" />
            </button>
        </div>

        <div class="grid grid-cols-12 gap-4">
            <div class="col-span-12 space-y-3 lg:col-span-8">
                <div class="flex flex-wrap items-center gap-2">
                    <label class="label cursor-pointer">
                        <Field type="checkbox" name="pay_later" v-model="paymentsData.pay_later" :value="true" class="checkbox checkbox-primary" />
                        <span class="label-text">Pay Later</span>
                    </label>
                    <label class="label cursor-pointer">
                        <Field
                            type="checkbox"
                            name="accept_partial_payments"
                            :value="true"
                            v-model="paymentsData.accept_partial_payments"
                            class="checkbox checkbox-primary"
                        />
                        <span class="label-text">Accept Partial Payments</span>
                    </label>
                </div>
                <div class="border-base-content/5 bg-base-100 overflow-x-auto border">
                    <div class="overflow-x-auto">
                        <table class="table-sm table w-full">
                            <tbody>
                                <template v-for="(payment, index) in paymentsData.payments" :key="index">
                                    <tr v-if="payment.existing">
                                        <td>
                                            <button
                                                class="btn btn-primary btn-outline w-full justify-start whitespace-nowrap uppercase"
                                                type="button"
                                                disabled
                                            >
                                                {{
                                                    paymentMethods.find((method) => method.value === payment.payment_method.id)?.label ||
                                                    'Unknown Method'
                                                }}
                                            </button>
                                        </td>
                                        <td>
                                            <input type="text" class="input input-bordered" :value="formatPrice(payment.amount)" disabled />
                                        </td>
                                        <td>{{ payment.phone_number ?? '-' }}</td>
                                    </tr>
                                    <tr v-else>
                                        <td>
                                            <button
                                                class="btn btn-primary btn-outline w-full justify-start whitespace-nowrap uppercase"
                                                type="button"
                                                @click="addPayment(payment.payment_method.value)"
                                            >
                                                {{ payment.payment_method.label }}
                                            </button>
                                        </td>
                                        <td class="space-y-1">
                                            <Field
                                                :name="`payments[${index}].amount`"
                                                type="number"
                                                class="input input-bordered w-fit"
                                                :class="{ 'input-error': errors[`payments[${index}].amount`] }"
                                                v-model.number="payment.amount"
                                                :disabled="outstandingAmount <= 0"
                                            />
                                            <ErrorMessage :name="`payments[${index}].amount`" class="text-error" />
                                        </td>
                                        <td class="space-y-1">
                                            <Field
                                                :name="`payments[${index}].phone_number`"
                                                type="tel"
                                                placeholder="Mpesa Phone Number"
                                                class="input input-bordered w-fit"
                                                :class="{ 'is-invalid': errors[`payments[${index}].phone_number`] }"
                                                v-model="payment.phone_number"
                                                :disabled="outstandingAmount <= 0"
                                            />
                                            <ErrorMessage :name="`payments[${index}].phone_number`" class="text-error" />
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="space-x-2">
                                        <button type="button" class="btn btn-warning btn-outline" @click="$emit('closeDialog')">
                                            <font-awesome-icon icon="times" />
                                            <span>Cancel</span>
                                        </button>
                                        <button v-if="showSubmitButton" class="btn btn-success btn-outline">
                                            <font-awesome-icon icon="save" />
                                            <span v-if="!currentOrder">Save</span>
                                            <span v-else>Pay</span>
                                        </button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="order-first col-span-12 space-y-4 border-slate-300 lg:order-last lg:col-span-4 lg:border-l lg:ps-4">
                <div class="space-y-2">
                    <label class="label"><span class="label-text">Total Amount</span></label>
                    <div>
                        <span class="text-xl font-bold">{{ formatPrice(totalAmount) }}</span>
                    </div>
                </div>

                <template v-if="currentOrder">
                    <div class="space-y-2">
                        <label class="label"><span class="label-text">Paid Amount</span></label>
                        <div>
                            <span class="text-xl font-bold">{{ formatPrice(paidAmount) }}</span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="label"><span class="label-text">Outstanding Amount</span></label>
                        <div>
                            <span class="text-xl font-bold">{{ formatPrice(outstandingAmount) }}</span>
                        </div>
                    </div>
                </template>

                <div class="space-y-2">
                    <label class="label"><span class="label-text">Tendered Amount</span></label>
                    <div>
                        <span class="text-xl font-bold">{{ formatPrice(tenderedAmount) }}</span>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="label"><span class="label-text">Balance Amount</span></label>
                    <div>
                        <span class="text-xl font-bold">{{ formatPrice(balanceAmount) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </Form>
</template>
