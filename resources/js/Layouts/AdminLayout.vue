<script setup>
import {Link, router, usePage} from "@inertiajs/vue3";
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import {useI18n} from 'vue-i18n';
import {computed} from "vue";

const {t, locale} = useI18n();

const page = usePage();
const user = page.props.auth?.user || null;

const languages = {
    kz: 'KZ',
    ru: 'RU'
};
const currentLanguage = computed(() => locale.value);

const logout = () => {
    router.post(route('logout'), {}, {
        onSuccess: () => {
            router.get(route('login'));
        }
    });
};

const changeLanguage = (lang) => {
    locale.value = lang;
    localStorage.setItem('language', lang);
};

if (localStorage.getItem('language')) {
    locale.value = localStorage.getItem('language');
}
</script>

<template>
    <div class="bg-gray-200 w-screen h-screen flex flex-row items-center box-border">
        <div class="h-full w-72 box-border p-4">
            <div class="bg-slate-600 h-full w-full rounded-lg">
                <div class="text-center box-border py-4">
                    <span class="">
                        <img class="w-14 mx-auto mb-2" src="/images/logo.webp" alt="">
                        <span class="text-2xl text-white font-bold">{{ t('main.udnCos') }}</span>
                    </span>
                </div>
                <div class="px-4 flex flex-col gap-4">
                    <Link
                        :href="route('dashboard')"
                        :class="{'p-2 rounded-lg text-white cursor-pointer bg-slate-500 hover:bg-slate-400 border-2': route().current('dashboard'),
                'p-2 rounded-lg text-white cursor-pointer bg-slate-500 hover:bg-slate-400 border-2 border-slate-500': !route().current('dashboard')}">
                        <font-awesome-icon :icon="['fas', 'bars-progress']"/>
                        {{ t('main.dashboard') }}
                    </Link>

                    <!-- Категории -->
                    <Link
                        :href="route('category')"
                        :class="{
                'p-2 rounded-lg text-white cursor-pointer bg-slate-500 hover:bg-slate-400 border-2': route().current('category'),
                'p-2 rounded-lg text-white cursor-pointer bg-slate-500 hover:bg-slate-400 border-2 border-slate-500': !route().current('category')
            }"
                    >
                        <font-awesome-icon :icon="['fas', 'list']"/>
                        {{ t('main.categories') }}
                    </Link>

                    <!-- Столы -->
                    <Link :href="route('tables')"
                          :class="{
                'p-2 rounded-lg text-white cursor-pointer bg-slate-500 hover:bg-slate-400 border-2': route().current('tables'),
                'p-2 rounded-lg text-white cursor-pointer bg-slate-500 hover:bg-slate-400 border-2 border-slate-500': !route().current('tables')
            }"
                    >
                        <font-awesome-icon :icon="['fas', 'table-cells-large']"/>
                        {{ t('main.tables') }}
                    </Link>
                    <!-- Отчеты -->
                    <Link :href="route('reports.index')"
                          :class="{
                'p-2 rounded-lg text-white cursor-pointer bg-slate-500 hover:bg-slate-400 border-2': route().current('tables'),
                'p-2 rounded-lg text-white cursor-pointer bg-slate-500 hover:bg-slate-400 border-2 border-slate-500': !route().current('tables')
            }"
                    >
                        <font-awesome-icon :icon="['fas', 'table-cells-large']"/>
                        {{ t('main.reports') }}
                    </Link>
                </div>
            </div>
        </div>
        <div class="box-border p-4 w-full flex flex-col h-full">
            <div class="bg-slate-600 flex flex-row items-center justify-between p-4 rounded-lg">
                <div class="flex flex-row gap-4">
                    <button v-for="(lang, key) in languages" @click="changeLanguage(key)"
                            class="bg-slate-500 hover:bg-slate-400 text-center content-center w-8 h-8 rounded-sm text-white">
                        {{ lang }}
                    </button>
                </div>
                <div class="flex flex-row items-center gap-4">
                    <div class="text-lg text-white">
                        {{ user.name }}
                    </div>
                    <button
                        @click.prevent="logout"
                        class="bg-slate-500 hover:bg-slate-400 text-center content-center px-4 h-8 rounded-sm text-white">
                        {{ t('main.exit') }}
                    </button>
                </div>
            </div>
            <div class="bg-white w-full flex-1 overflow-y-auto rounded-lg box-border p-4 mt-4">
                <slot/>
            </div>
        </div>
    </div>
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
