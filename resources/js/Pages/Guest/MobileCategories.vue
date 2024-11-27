<script setup>
import {Head, Link, router} from "@inertiajs/vue3";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import {useI18n} from 'vue-i18n';

const {t, locale} = useI18n();

const localizedCategoryName = (category) => {
    return category[`name_${locale.value}`] || category.name_ru;
};

const props = defineProps({
    categories: Array
})
</script>

<template>
    <Head title="Электронная очередь"></Head>
    <GuestLayout>
        <div
            class="basis-2/3 grid grid-cols-1 cursor-pointer gap-4 rounded-lg h-full overflow-y-auto text-lg font-medium">
            <Link :href="route('digitalTicket-create', [category.id, locale])" v-for="category in categories"
                  class="bg-white/60  h-48 p-4 rounded-lg content-center text-center">
                {{ localizedCategoryName(category) }}
            </Link>
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
