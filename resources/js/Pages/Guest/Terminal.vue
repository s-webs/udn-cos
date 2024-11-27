<script setup>
import {Head} from "@inertiajs/vue3";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import {useI18n} from 'vue-i18n';
import {ref} from "vue";
import TicketQrCode from "@/Components/TicketQrCode.vue";
import QRCode from 'qrcode.vue';

const {t, locale} = useI18n();

const localizedCategoryName = (category) => {
    return category[`name_${locale.value}`] || category.name_ru;
};

const props = defineProps({
    categories: Array
})

const isModalVisible = ref(false);
const selectedCategory = ref(null);
const generateUrl = ref(null)

const showQRCode = (category) => {
    selectedCategory.value = category;
    generateUrl.value = `${window.location.origin}/ticket/${selectedCategory.value.id}/${locale.value}`
    isModalVisible.value = true;
};

const closeModal = () => {
    selectedCategory.value = null;
    generateUrl.value = null
    isModalVisible.value = false;
};
</script>

<template>
    <Head title="Электронная очередь"></Head>
    <GuestLayout>
        <div v-if="isModalVisible"
             class="fixed inset-0 bg-gray-800 bg-opacity-50 flex justify-center items-center z-50">
            <div class="bg-white rounded-lg p-8 text-center">
                <h2 class="text-2xl font-bold mb-4">{{ t('main.ticketScan') }}</h2>
                <div class="w-80 h-80 mx-auto mt-12">
                    <TicketQrCode :url="generateUrl" :locale="locale"/>
                </div>
                <div class="mt-12">
                    <button
                        class="bg-blue-500 text-white p-2 rounded-lg mt-4"
                        @click="closeModal">
                        {{ t('main.close') }}
                    </button>
                </div>
            </div>
        </div>


        <div class="flex flex-row gap-4 h-full">
            <div
                class="basis-2/3 grid grid-cols-3 cursor-pointer gap-4 rounded-lg h-full overflow-y-auto text-xl font-medium">
                <div @click="showQRCode(category)" v-for="category in categories"
                     class="bg-white/60  h-48 p-4 rounded-lg content-center text-center">
                    {{ localizedCategoryName(category) }}
                </div>
            </div>
            <div class="basis-2/6 content-center bg-white/60 rounded-lg box-border p-4">
                <div class="flex justify-center">
                    <QRCode :value="route('mobileCategories')" :size="256" />
                </div>
                <div class="text-center box-border mt-8">
                    <span class="text-2xl font-bold">{{ t('main.welcomeScan') }}</span>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
::-webkit-scrollbar {
    width: 0px;
    background: transparent;
}

html {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
