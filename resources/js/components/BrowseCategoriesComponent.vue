<script setup>
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    categories: {
        type: Array,
        required: true,
    },
});

const isOpen = ref(false);

const closeModal = () => {
    isOpen.value = false;
};

const openModal = () => {
    isOpen.value = true;
};

const pushToCategoriesProductsList = (category) => {
    closeModal();

    selectedCategory.value = null;

    // Visit the selected category
    router.visit(route('products.categories.index', [category.slug]));
};

const onCategorySelected = (category) => {
    if (category.children?.length) {
        selectedCategory.value = category;
    } else {
        pushToCategoriesProductsList(category);
    }
};

const selectedCategory = ref(null);

const selectedCategoryChildren = computed(() => {
    return selectedCategory.value?.children ?? props.categories;
});
</script>
<template>
    <div class="shrink-0">
        <button type="button" @click="openModal" class="btn btn-primary btn-outline">
            <font-awesome-icon icon="bars" />
            <span class="hidden xl:inline">Browse Categories</span>
        </button>
    </div>
    <TransitionRoot appear :show="isOpen" as="template">
        <Dialog as="div" @close="closeModal" class="relative z-20">
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-black/25" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-start text-center">
                    <TransitionChild
                        as="template"
                        enter="transition duration-300 ease-out"
                        enter-from="-translate-x-full"
                        enter-to="translate-x-0"
                        leave="transition duration-200 ease-in"
                        leave-from="translate-x-0"
                        leave-to="-translate-x-full"
                    >
                        <DialogPanel
                            class="bg-base-100 text-base-content flex h-screen w-5/6 transform flex-col overflow-hidden text-left align-middle shadow-xl transition-all sm:w-72"
                        >
                            <div class="from-primary to-accent text-primary-content flex items-center justify-between border-b bg-gradient-to-r p-2">
                                <DialogTitle as="h3" class="text- leading-6 font-medium"> Filter By Categories </DialogTitle>

                                <div class="py-2">
                                    <button type="button" @click="closeModal" class="btn btn-circle btn-ghost">
                                        <font-awesome-icon icon="times" />
                                        <span class="sr-only">Close</span>
                                    </button>
                                </div>
                            </div>
                            <div class="grow overflow-y-auto text-sm">
                                <div class="p-2">
                                    <button
                                        v-if="selectedCategory"
                                        type="button"
                                        @click="selectedCategory = null"
                                        class="btn btn-accent btn-sm btn-outline rounded-full"
                                    >
                                        <font-awesome-icon icon="arrow-left" />
                                        <span>Main</span>
                                    </button>
                                </div>
                                <h2 class="px-2 pt-2 pb-1 text-lg font-semibold">
                                    {{ selectedCategory?.name ?? 'All Categories' }}
                                </h2>
                                <ul v-if="categories?.length">
                                    <li v-if="selectedCategory">
                                        <a
                                            role="button"
                                            @click="() => pushToCategoriesProductsList(selectedCategory)"
                                            class="btn btn-ghost line-clamp-1 flex justify-between rounded-none px-2"
                                        >
                                            <span>All</span>
                                        </a>
                                    </li>
                                    <li v-for="category in selectedCategoryChildren" :key="category.id">
                                        <a
                                            role="button"
                                            @click="() => onCategorySelected(category)"
                                            class="btn btn-ghost line-clamp-1 flex justify-between rounded-none px-2"
                                        >
                                            <span>{{ category.name }}</span>
                                            <font-awesome-icon icon="chevron-right" />
                                        </a>
                                    </li>
                                </ul>
                                <p v-else class="px-2 text-sm text-gray-500">No Categories</p>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
