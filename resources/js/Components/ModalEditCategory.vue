<script setup>
import { ref, computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useForm } from '@inertiajs/vue3';

const { t } = useI18n();

const props = defineProps({
    category: Object,
    action: String,
    show: Boolean,
});

const emit = defineEmits(['accept', 'close']);
const isOpen = computed(() => props.show);

const form = useForm({
    name_kz: '',
    name_ru: ''
});

// Обновляем форму при изменении props.category
watch(
    () => props.category,
    (newCategory) => {
        if (newCategory) {
            form.name_kz = newCategory.name_kz;
            form.name_ru = newCategory.name_ru;
        }
    },
    { immediate: true } // Обновляем форму сразу после загрузки
);

const onAccept = () => {
    emit('accept', { ...form }); // Передаём форму в событии
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
                class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-lg p-3 mx-auto my-auto rounded-xl shadow-lg bg-white"
            >
                <div>
                    <div class="">
                        <label class="block mb-2" for="grid-name-kz">
                            {{ t('main.categoryInKz') }}
                            <input
                                v-model="form.name_kz"
                                class="appearance-none block w-full bg-gray-200 text-gray-700 border rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                                id="grid-name-kz" type="text" placeholder="KZ">
                        </label>
                        <label class="block mb-2" for="grid-name-ru">
                            {{ t('main.categoryInRu') }}
                            <input
                                v-model="form.name_ru"
                                class="appearance-none block w-full bg-gray-200 text-gray-700 border rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                                id="grid-name-ru" type="text" placeholder="RU">
                        </label>
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
