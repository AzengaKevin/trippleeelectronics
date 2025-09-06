<script setup>
import axios from 'axios';
import { ErrorMessage, Field, Form } from 'vee-validate';
import { computed, onMounted, ref } from 'vue';
import * as yup from 'yup';

const props = defineProps({
    state: Object,
    types: Array,
    previousStep: Function,
    nextStep: Function,
    submitProperty: Function,
    features: Object,
});

const emits = defineEmits(['updateState']);

const imagePreviews = ref([]);

const setImagePreviews = (files) => {
    imagePreviews.value = [];

    // Loop through each selected file

    for (let i = 0; i < files.length; i++) {
        const file = files[i];

        // Create a FileReader
        const reader = new FileReader();

        // Set up an event listener to handle the file read
        reader.onload = () => {
            // Push the data URL of the image into the imagePreviews array
            imagePreviews.value.push(reader.result);
        };

        // Read the file as a data URL
        reader.readAsDataURL(file);
    }
};

const selectedTypes = computed(() => {
    return props.types
        ?.filter((t) => props.state?.types?.includes(t.id))
        ?.map((t) => t.name)
        .reduce((collector, name) => (collector += `${name}, `));
});

const selectedFeatures = computed(() => {
    const features = {};

    for (const key in props.features) {
        if (Object.hasOwnProperty.call(props.features, key)) {
            const groupFeatures = props.features[key];

            features[key] = groupFeatures.filter((item) => props.state.features.includes(item.id)).map((item) => item.name);
        }
    }

    return features;
});

const selectedCustomFeatures = ref([]);

const populateCustomFeatures = () => {
    const params = { types: props.state?.types };

    axios
        .get(route('api.property-features.index'), { params })
        .then((response) => {
            const results = response.data?.data;

            selectedCustomFeatures.value = props.state?.custom_features
                ?.map((value, key) => ({
                    label: results.find((item) => item.id == key)?.name,
                    value,
                }))
                .filter((item) => !!item.value);
        })
        .catch((error) => {
            console.error(error);
        });
};

const initialValues = {
    cover_image: props.state?.cover_image,
};

const schema = yup.object().shape({
    cover_image: yup.string().required('A cover image is required, click on your preffered image to select it as the cover image'),
});

onMounted(() => {
    populateCustomFeatures();

    setImagePreviews(props.state?.files);
});

const submit = (values) => {
    emits('updateState', values);

    props.submitProperty?.();
};
</script>
<template>
    <Form @submit="submit" v-slot="{ errors }" :validation-schema="schema" :initial-values="initialValues" class="d-flex flex-column gap-5">
        <div class="card rounded-0">
            <div class="card-header">
                <h5 class="card-title my-0">Location</h5>
            </div>
            <div class="card-body">
                <dl>
                    <dt>Location Name</dt>
                    <dd>{{ state.location_name?.label }}</dd>
                </dl>
            </div>
        </div>
        <div class="card rounded-0">
            <div class="card-header">
                <h5 class="card-title my-0">Basic Information</h5>
            </div>
            <div class="card-body">
                <dl>
                    <dt>Name</dt>
                    <dd>{{ state.name }}</dd>
                </dl>
                <dl>
                    <dt>Category</dt>
                    <dd>{{ state.category }}</dd>
                </dl>
                <dl>
                    <dt>Type(s)</dt>
                    <dd>{{ selectedTypes }}</dd>
                </dl>
                <dl>
                    <dt>Description</dt>
                    <dd v-html="state?.description"></dd>
                </dl>
            </div>
        </div>

        <div class="card rounded-0">
            <div class="card-header">
                <h5 class="card-title my-0">Unit(s)</h5>
            </div>
            <div class="card-body">
                <template v-if="state?.multiple">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>NAME</th>
                                    <th>PRICE</th>
                                    <th>SIZE</th>
                                    <template v-if="state?.bedroomsAndBathrooms">
                                        <th>BEDROOMS</th>
                                        <th>BATHROOMS</th>
                                    </template>
                                    <th>PLAN</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(unit, index) in state?.units">
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ unit.name }}</td>
                                    <td>
                                        <div v-for="value in unit.price">
                                            <div class="d-flex gap-2">
                                                <span>/</span>
                                                <span class="text-muted">{{ value ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div v-for="value in unit.size">
                                            <div class="d-flex gap-2">
                                                <span>/</span>
                                                <span class="text-muted">{{ value ?? '-' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <template v-if="state?.bedroomsAndBathrooms">
                                        <td>{{ unit.bedrooms ?? '-' }}</td>
                                        <td>{{ unit.bathrooms ?? '-' }}</td>
                                    </template>
                                    <td>{{ unit.payment_plan ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </template>

                <template v-else>
                    <dl>
                        <dt>Price</dt>
                        <dd class="d-flex flex-wrap gap-3">
                            <div v-for="(value, key) in state?.price">
                                <div class="d-flex gap-2">
                                    <span class="text-capitalize">{{ key }}</span>
                                    <span>:</span>
                                    <span class="text-muted">{{ value ?? '-' }}</span>
                                </div>
                            </div>
                        </dd>
                    </dl>
                    <dl>
                        <dt>Size</dt>
                        <dd class="d-flex flex-wrap gap-3">
                            <div v-for="(value, key) in state?.size">
                                <div class="d-flex gap-2">
                                    <span class="text-capitalize">{{ key }}</span>
                                    <span>:</span>
                                    <span class="text-muted">{{ value ?? '-' }}</span>
                                </div>
                            </div>
                        </dd>
                    </dl>

                    <template v-if="state?.bedroomsAndBathrooms">
                        <dl>
                            <dt>Bedrooms</dt>
                            <dd>{{ state.bedrooms ?? '-' }}</dd>
                        </dl>
                        <dl>
                            <dt>Bathrooms</dt>
                            <dd>{{ state.bathrooms ?? '-' }}</dd>
                        </dl>
                    </template>
                    <dl>
                        <dt>Payment Plan</dt>
                        <dd>{{ state?.payment_plan }}</dd>
                    </dl>
                </template>
            </div>
        </div>

        <div class="card rounded-0">
            <div class="card-header">
                <h5 class="card-title my-0">Features</h5>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-3">
                    <dl v-for="(categoryFeatures, key) in selectedFeatures" :key="key">
                        <dt>{{ key }} Features</dt>
                        <dd v-if="categoryFeatures.length">
                            {{ categoryFeatures?.join(', ') }}
                        </dd>
                        <dd v-else>-</dd>
                    </dl>
                    <dl v-if="selectedCustomFeatures.length">
                        <dt>Custom Features</dt>

                        <dd>
                            <div class="table-responsive">
                                <table class="table-hover table">
                                    <thead class="bg-body-secondary">
                                        <tr>
                                            <th>Field</th>
                                            <th>Value</th>
                                        </tr>
                                    </thead>
                                    <tbody class="table-group-divider">
                                        <tr v-for="(item, index) in selectedCustomFeatures" :key="index">
                                            <td>{{ item.label }}</td>
                                            <td>{{ item.value }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="card rounded-0">
            <div class="card-header">
                <h5 class="card-title my-0">Media</h5>
            </div>
            <div class="card-body d-flex flex-column gap-3">
                <div>
                    <label for="files" class="form-label">Select Cover Image</label>
                    <fieldset class="row g-3 row-cols-1 row-cols-sm-2 row-cols-md-3" :class="{ 'is-invalid': errors.cover_image }">
                        <div v-for="(file, index) in state?.files" class="col">
                            <div class="d-flex flex-column gap-2">
                                <label :for="`file-${index}-radio`" class="form-check-label">
                                    <img
                                        v-if="imagePreviews"
                                        :src="imagePreviews[index]"
                                        class="object-fit-cover img-thumbnail c-h-64 m-0 w-100 p-0"
                                        :alt="file.name"
                                    />
                                    <span v-else>{{ file.name }}</span>
                                </label>
                                <Field
                                    type="radio"
                                    name="cover_image"
                                    :id="`file-${index}-radio`"
                                    class="form-check-input m-0 p-0"
                                    :value="file.name"
                                />
                            </div>
                        </div>
                    </fieldset>
                    <ErrorMessage class="invalid-feedback" name="cover_image" />
                </div>
            </div>
        </div>
        <div class="d-flex gap-3">
            <button type="button" @click="previousStep" class="btn btn-outline-primary">Previous</button>
            <button type="submit" class="btn btn-success">Complete</button>
        </div>
    </Form>
</template>
