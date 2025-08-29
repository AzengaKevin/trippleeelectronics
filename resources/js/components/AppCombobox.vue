<script setup>
import { Combobox, ComboboxButton, ComboboxInput, ComboboxOption, ComboboxOptions, TransitionRoot } from '@headlessui/vue';
import { CheckIcon, ChevronUpDownIcon } from '@heroicons/vue/20/solid';
import debounce from 'lodash/debounce';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    id: String,
    label: String,
    description: String,
    required: Boolean,
    modelValue: Object,
    options: {
        type: Array,
        default: () => [],
    },
    loadOptions: Function,
    defaultType: {
        type: String,
        default: 'custom-item',
    },
    withCustomOption: {
        type: Boolean,
        default: true,
    },
    size: {
        type: String,
        default: 'md',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['update:modelValue', 'handleChange']);

const options = ref(props.options);

const isLoading = ref(false);

let query = ref('');

watch(
    query,
    debounce((q) => {
        if (props.loadOptions) {
            isLoading.value = true;

            props.loadOptions(q, (results) => {
                options.value = results;

                isLoading.value = false;
            });
        }
    }, 300),
    { immediate: true },
);

let filteredOptions = computed(() =>
    query.value === ''
        ? options.value
        : options.value.filter((option) => option.label.toLowerCase().replace(/\s+/g, '').includes(query.value.toLowerCase().replace(/\s+/g, ''))),
);

const handleUpdateModelValue = (selected) => {
    emit('update:modelValue', selected);
    emit('handleChange');
};

const queryOption = computed(() => {
    return query.value === ''
        ? null
        : {
              type: props.defaultType,
              value: query.value,
              label: query.value,
          };
});

const addCustomOption = () => {
    if (queryOption.value) {
        options.value.unshift(queryOption.value);

        handleUpdateModelValue(queryOption.value);
    }
};
</script>
<template>
    <div class="w-full space-y-1">
        <template v-if="label">
            <label class="label" :for="id">
                <span class="label-text text-xs">{{ label }}</span>
                <span v-if="required" class="label-text-alt text-error">*</span>
            </label>
        </template>

        <Combobox :model-value="modelValue" @update:model-value="handleUpdateModelValue" by="value" :disabled="disabled">
            <div class="relative">
                <div class="relative w-full min-w-48 cursor-default overflow-hidden text-left">
                    <ComboboxInput
                        :id="id"
                        :class="`input input-${size} w-full pr-10`"
                        :displayValue="(option) => option?.label"
                        @change="query = $event.target.value"
                        :aria-describedby="`${id}-description`"
                        autocomplete="off"
                    />
                    <ComboboxButton class="absolute inset-y-0 right-0 flex items-center pr-2">
                        <ChevronUpDownIcon class="text-base-content/70 h-5 w-5" aria-hidden="true" />
                    </ComboboxButton>
                </div>
                <TransitionRoot leave="transition ease-in duration-100" leaveFrom="opacity-100" leaveTo="opacity-0" @after-leave="query = ''">
                    <ComboboxOptions
                        class="bg-base-100 absolute z-[1] mt-1 max-h-48 w-full overflow-y-auto rounded-md py-1 text-base shadow-lg ring-1 ring-black/5 focus:outline-none sm:text-sm"
                    >
                        <button
                            v-if="filteredOptions.length === 0 && !isLoading"
                            type="button"
                            :disabled="!withCustomOption"
                            class="text-base-content relative flex items-center px-4 py-2"
                            @click="addCustomOption"
                        >
                            <span v-if="withCustomOption">Add "{{ queryOption?.label }}"</span>
                            <span v-else class="text-base-content/70 px-4 py-2">No results found</span>
                        </button>
                        <div v-if="isLoading" class="text-base-content/70 relative select-none">Loading...</div>

                        <template v-if="!isLoading">
                            <ComboboxOption
                                v-for="(option, index) in filteredOptions"
                                as="template"
                                :key="option.value"
                                :value="option"
                                v-slot="{ selected, active }"
                            >
                                <li
                                    class="relative cursor-default py-2 pr-4 pl-10 select-none"
                                    :class="{
                                        'bg-primary text-primary-content': active,
                                        'text-base-content': !active,
                                    }"
                                >
                                    <span class="block truncate" :class="{ 'font-medium': selected, 'font-normal': !selected }">
                                        {{ option.label }}
                                    </span>
                                    <span
                                        v-if="selected"
                                        class="absolute inset-y-0 left-0 flex items-center pl-3"
                                        :class="{ 'text-primary-content': active, 'text-primary': !active }"
                                    >
                                        <CheckIcon class="h-5 w-5" aria-hidden="true" />
                                    </span>
                                </li>
                            </ComboboxOption>
                        </template>
                    </ComboboxOptions>
                </TransitionRoot>
            </div>
        </Combobox>

        <template v-if="description">
            <div :id="`${id}-description`" class="label">
                <span class="label-text-alt">{{ description }}</span>
            </div>
        </template>
    </div>
</template>
