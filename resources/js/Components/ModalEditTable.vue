<script setup>
import {ref, computed, watch} from 'vue';
import {useI18n} from 'vue-i18n';
import {useForm} from '@inertiajs/vue3';

const {t, locale} = useI18n();

const localizedCategoryName = (category) => {
    return category[`name_${locale.value}`] || category.name_ru;
};

const props = defineProps({
    table: Object,
    categories: Array,
    selectedCategories: Array,
    action: String,
    show: Boolean,
});

const emit = defineEmits(['accept', 'close']);
const isOpen = computed(() => props.show);

const form = useForm({
    name_kz: '',
    name_ru: '',
    number: '',
    selectedCategories: []
});

// Обновляем форму при изменении props.table
watch(
    () => props.table,
    (newTable) => {
        if (newTable) {
            form.name_kz = newTable.name_kz;
            form.name_ru = newTable.name_ru;
            form.number = newTable.number;

            // Обновляем выбранные категории
            form.selectedCategories = newTable.categories ? newTable.categories.map(category => category.id) : [];
        }
    },
    { immediate: true }
);


const onAccept = () => {
    emit('accept', {...form, categories: form.selectedCategories});
    emit('close');
};

const onClose = () => {
    emit('close');
};
</script>

<template>
    <transition name="fade">
        <div v-if="isOpen">
            <div
                @click="onClose"
                class="absolute bg-black opacity-70 inset-0 z-0"
            ></div>
            <div
                class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-4xl p-3 mx-auto my-auto rounded-xl shadow-lg bg-white"
            >
                <div>
                    <div class="flex flex-row gap-4">
                        <div class="basis-2/5">
                            <label class="block mb-2" for="grid-name-kz">
                                {{ t('main.tableInKz') }}
                                <input
                                    v-model="form.name_kz"
                                    class="appearance-none block w-full bg-gray-200 text-gray-700 border rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                                    id="grid-name-kz" type="text" placeholder="KZ">
                            </label>
                            <label class="block mb-2" for="grid-name-ru">
                                {{ t('main.tableInRu') }}
                                <input
                                    v-model="form.name_ru"
                                    class="appearance-none block w-full bg-gray-200 text-gray-700 border rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                                    id="grid-name-ru" type="text" placeholder="RU">
                            </label>
                            <label class="block mb-2" for="grid-number">
                                {{ t('main.numberTable') }}
                                <input
                                    v-model="form.number"
                                    class="appearance-none block w-full bg-gray-200 text-gray-700 border rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                                    id="grid-number" type="text" placeholder="NUM">
                            </label>
                        </div>
                        <div class="basis-3/5">
                            <div class="text-xl mb-2">{{ t('main.categories') }}</div>
                            <label :for="'category_'+category.id" v-for="category in categories" :key="category.id"
                                   class="flex items-center border mb-2 p-1">
                                <input
                                    :id="'category_'+category.id"
                                    :name="category.id"
                                    type="checkbox"
                                    :value="category.id"
                                    v-model="form.selectedCategories"
                                    class="mr-2"
                                />
                                {{ localizedCategoryName(category) }}
                            </label>
                        </div>
                    </div>
                    <div class="p-3 mt-2 text-center space-x-4 md:block">
                        <button
                            @click="onAccept"
                            class="mb-2 md:mb-0 bg-red-500 border border-red-500 px-5 py-2 text-sm shadow-sm font-medium tracking-wider text-white rounded-md hover:shadow-lg hover:bg-red-600"
                        >
                            {{ action }}
                        </button>
                        <button
                            @click="onClose"
                            class="mb-2 md:mb-0 bg-white px-5 py-2 text-sm shadow-sm font-medium tracking-wider border text-gray-600 rounded-md hover:shadow-lg hover:bg-gray-100"
                        >
                            {{ t('main.cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<style>
.fade-enter,
.fade-leave-to {
    opacity: 0;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 500ms ease-out;
}
</style>
