<script setup>
import { ref, computed } from 'vue';
import {useI18n} from "vue-i18n";

const {t, locale} = useI18n();

const props = defineProps({
    text: String,
    action: String,
    show: Boolean,
});

const emit = defineEmits(['accept', 'close']);
const isOpen = computed(() => props.show);
const onAccept = () => {
    emit('accept', true);
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
                    <div class="text-center p-3 flex-auto justify-center leading-6">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="w-16 h-16 flex items-center text-red-600 mx-auto"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                clip-rule="evenodd"
                            />
                        </svg>
                        <h2 class="text-2xl font-bold py-4">{{ text }}</h2>
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
