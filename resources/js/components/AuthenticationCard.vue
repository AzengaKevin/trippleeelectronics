<template>
    <div class="bg-base-200 flex min-h-screen flex-col items-center pt-6 sm:justify-center sm:pt-0">
        <div>
            <slot name="logo" />
        </div>

        <div class="bg-base-100 mt-6 w-full overflow-hidden px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg">
            <slot />
        </div>
    </div>
</template>
