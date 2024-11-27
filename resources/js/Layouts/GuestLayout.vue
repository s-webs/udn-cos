<script setup>
import {useI18n} from 'vue-i18n';
import {computed} from "vue";

const {t, locale} = useI18n();

const languages = {
    kz: 'KZ',
    ru: 'RU'
};
const currentLanguage = computed(() => locale.value);

const changeLanguage = (lang) => {
    locale.value = lang;
    localStorage.setItem('language', lang);
};

if (localStorage.getItem('language')) {
    locale.value = localStorage.getItem('language');
}
</script>

<template>
    <div class="bg-gradient-to-r from-green-300 to-cyan-400 h-screen box-border p-4 flex flex-col">
        <div class="flex flex-row justify-between items-center">
            <div class="flex items-center gap-4">
                <img src="/images/logo.webp" alt="Logo" style="width: 80px;">
                <span class="text-4xl text-green-900 font-bold">{{ t('main.udnCos') }}</span>
            </div>
            <div class="flex flex-row gap-4 text-2xl">
                <button v-for="(lang, key) in languages" @click="changeLanguage(key)"
                        class="p-4 box-border bg-white rounded-lg">{{ lang }}
                </button>
            </div>
        </div>
        <div class="mt-4 flex-grow p-4 overflow-hidden">
            <slot/>
        </div>
    </div>
</template>

<style scoped>

</style>
