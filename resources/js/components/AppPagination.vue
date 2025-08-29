<script setup>
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

const props = defineProps({
    resource: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <section class="flex flex-col items-center justify-between gap-3 py-3 lg:flex-row">
        <!-- Mobile: Simple count -->
        <div class="text-sm sm:hidden">
            Page <span class="font-bold">{{ resource?.current_page }}</span> of
            <span class="font-bold">{{ resource?.last_page }}</span>
        </div>

        <!-- Desktop: Detailed count -->
        <div class="hidden text-sm sm:block lg:block">
            Showing <span class="font-bold">{{ resource?.from }}</span> to <span class="font-bold">{{ resource?.to }}</span> of
            <span class="font-bold">{{ resource?.total }}</span> results
        </div>

        <div class="join">
            <!-- Previous button -->
            <Link class="join-item btn btn-sm" :class="{ 'btn-disabled': resource.prev_page_url == null }" :href="resource.prev_page_url ?? '#'">
                <chevron-left class="h-4 w-4" />
                <span class="sr-only">Previous</span>
            </Link>

            <!-- Mobile: Simple page indicator -->
            <div class="join-item btn btn-sm pointer-events-none sm:hidden">{{ resource?.current_page }} / {{ resource?.last_page }}</div>

            <!-- Desktop: Full pagination links -->
            <template v-for="(link, index) in resource?.links" :key="index">
                <Link
                    v-if="
                        index === 0 ||
                        index === resource.links.length - 1 ||
                        (link.label >= resource.current_page - 2 && link.label <= resource.current_page + 2)
                    "
                    class="join-item btn btn-sm hidden sm:inline-flex"
                    :class="{
                        'btn-active': link.active,
                        'btn-disabled': link.url == null,
                    }"
                    :href="link.url ?? '#'"
                >
                    <span v-html="link.label" />
                </Link>
                <span
                    v-else-if="index === 1 || index === resource.links.length - 2"
                    class="join-item btn btn-sm btn-disabled pointer-events-none hidden sm:inline-flex"
                >
                    ...
                </span>
            </template>

            <!-- Next button -->
            <Link class="join-item btn btn-sm" :class="{ 'btn-disabled': resource.next_page_url == null }" :href="resource.next_page_url ?? '#'">
                <chevron-right class="h-4 w-4" />
                <span class="sr-only">Next</span>
            </Link>
        </div>
    </section>
</template>
