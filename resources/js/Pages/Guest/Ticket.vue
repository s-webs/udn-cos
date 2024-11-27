<script setup>
import {Head} from "@inertiajs/vue3";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import {useI18n} from "vue-i18n";

const {t, locale} = useI18n();

const props = defineProps({
    ticket: Object,
    category: Object,
    locale: String
})

locale.value = props.locale

const localizedCategoryName = (category) => {
    return category[`name_${locale.value}`] || category.name_ru;
};

</script>

<template>
    <Head/>
    <GuestLayout>
        <div class="h-full bg-white rounded-lg flex flex-col justify-between p-4">
            <div>
                <div class="text-xl font-bold text-center">{{ localizedCategoryName(category) }}</div>
            </div>
            <div class="">
                <div class="text-center text-4xl font-bold mt-4 mb-4">
                    <span>№{{ ticket.ticket_number }}</span>
<!--                    <span>№{{ ticket.ticket_number }}</span>-->
<!--                    <font-awesome-icon class="ml-3 mr-3" :icon="['fas', 'right-long']"/>-->
<!--                    <span>1</span>-->
                </div>
                <div class="text-center uppercase text-xl border p-2 font-bold">
                    <div v-if="ticket.status === 'waiting'" class="text-blue-600">{{ t('main.waiting') }}</div>
                    <div v-if="ticket.status === 'expected'" class="text-green-600">
                        {{ t('main.expected') }}
                    </div>
                    <div v-if="ticket.status === 'complete'" class="text-green-600">{{ t('main.complete') }}</div>
                    <div v-if="ticket.status === 'rejected'" class="text-red-600">{{ t('main.rejected') }}</div>
                </div>
            </div>
            <div>
                <div class="text-center">В очереди 39</div>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>

</style>
