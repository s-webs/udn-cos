<script setup>
 import {Head} from "@inertiajs/vue3";
 import GuestLayout from "@/Layouts/GuestLayout.vue";
</script>

<template>
    <Head title="Зал ожидания"></Head>
    <GuestLayout>
        <div class="grid grid-cols-4 gap-4 text-8xl font-bold h-full overflow-y-auto p-4 rounded-lg">
            <!-- Пример элементов списка -->
            <div v-for="n in 40" :key="n" class="flex flex-row gap-4 justify-center items-center content-center p-4 rounded-lg bg-gray-100 h-64">
                <span>{{ n }}</span>
                <font-awesome-icon :icon="['fas', 'right-long']" />
                <span>1</span>
            </div>
        </div>
    </GuestLayout>
</template>

<style scoped>
::-webkit-scrollbar { /* chrome based */
    width: 0px; /* ширина scrollbar'a */
    background: transparent; /* опционально */
}

html {
    -ms-overflow-style: none; /* IE 10+ */
    scrollbar-width: none; /* Firefox */
}
</style>
