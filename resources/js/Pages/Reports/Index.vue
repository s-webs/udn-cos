<script setup>
import {ref, computed} from 'vue';
import {Head} from '@inertiajs/vue3';
import axios from 'axios';
import html2pdf from 'html2pdf.js';
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {useI18n} from 'vue-i18n';
import dayjs from 'dayjs';
import 'dayjs/locale/ru';

dayjs.locale('ru'); // Если нужно использовать dayjs для удобства

const {t, locale} = useI18n();

const form = ref({
    period: 'today',
    start_date: '',
    end_date: '',
});

const categoryStats = ref([]);
const userStats = ref([]);
const pdfContent = ref(null);

const generateReport = () => {
    axios.post(route('reports.generate'), form.value)
        .then(response => {
            categoryStats.value = response.data.categoryStats || [];
            userStats.value = response.data.userStats || [];
        })
        .catch(error => {
            console.error('Ошибка при генерации отчёта:', error);
        });
};

const localizedItemName = (table) => {
    return table[`name_${locale.value}`] || table.name_ru;
};

const getCategoryName = (category) => {
    return localizedItemName(category)
};

const getUserName = (user) => {
    return user ? (user.name || 'Имя не указано') : 'Неизвестный пользователь';
};

const categoryTotal = computed(() => {
    return categoryStats.value.reduce((acc, stat) => acc + stat.total, 0);
});

// Функция форматирования дат из YYYY-MM-DD в DD.MM.YYYY
const formatDate = (dateString) => {
    if (!dateString) return '';
    const [year, month, day] = dateString.split('-');
    return `${day}.${month}.${year}`;
};

const selectedDateText = computed(() => {
    const now = dayjs(); // Используем dayjs для удобства
    let start, end;

    switch (form.value.period) {
        case 'today':
            // Сегодня: просто одна дата
            return formatDate(now.format('YYYY-MM-DD'));

        case 'week':
            // Неделя: с понедельника по воскресенье
            start = now.startOf('week').add(1, 'day'); // dayjs startOf('week') в некоторых локалях воскресенье, поэтому add(1,'day') чтобы начать с понедельника
            end = start.endOf('week');
            return `${formatDate(start.format('YYYY-MM-DD'))} - ${formatDate(end.format('YYYY-MM-DD'))}`;

        case 'month':
            // Месяц: с 1 числа месяца по последний день месяца
            start = now.startOf('month');
            end = now.endOf('month');
            return `${formatDate(start.format('YYYY-MM-DD'))} - ${formatDate(end.format('YYYY-MM-DD'))}`;

        case 'half_year':
            // Полугодие: последние 6 месяцев включая текущий месяц
            // Допустим берем start = now - 5 месяцев в начало месяца, end = конец текущего месяца
            start = now.subtract(5, 'month').startOf('month');
            end = now.endOf('month');
            return `${formatDate(start.format('YYYY-MM-DD'))} - ${formatDate(end.format('YYYY-MM-DD'))}`;

        case 'year':
            // Год: с 1 января текущего года по 31 декабря
            start = now.startOf('year');
            end = now.endOf('year');
            return `${formatDate(start.format('YYYY-MM-DD'))} - ${formatDate(end.format('YYYY-MM-DD'))}`;

        case 'custom':
            // Произвольный период: формируем по выбранным датам
            const startCustom = formatDate(form.value.start_date);
            const endCustom = formatDate(form.value.end_date);
            return `${startCustom} - ${endCustom}`;

        default:
            // Если вдруг какой-то другой вариант, пусть будет сегодня
            return formatDate(now.format('YYYY-MM-DD'));
    }
});

const downloadPDF = () => {
    const element = pdfContent.value;
    const options = {
        filename: 'report.pdf',
        image: {type: 'jpeg', quality: 0.98},
        html2canvas: {scale: 2},
        jsPDF: {unit: 'pt', format: 'a4', orientation: 'portrait'}
    };
    html2pdf().from(element).set(options).save();
};
</script>

<template>
    <Head title="Отчёты"/>
    <AdminLayout>
        <h1 class="text-2xl font-bold mb-4">{{ t('main.reports') }}</h1>
        <form @submit.prevent="generateReport" class="mb-8">
            <div class="flex items-center space-x-4">
                <label>
                    <input type="radio" value="today" v-model="form.period"/>
                    {{ t('main.today') }}
                </label>
                <label>
                    <input type="radio" value="week" v-model="form.period"/>
                    {{ t('main.week') }}
                </label>
                <label>
                    <input type="radio" value="month" v-model="form.period"/>
                    {{ t('main.month') }}
                </label>
                <label>
                    <input type="radio" value="half_year" v-model="form.period"/>
                    {{ t('main.halfYear') }}
                </label>
                <label>
                    <input type="radio" value="year" v-model="form.period"/>
                    {{ t('main.year') }}
                </label>
                <label>
                    <input type="radio" value="custom" v-model="form.period"/>
                    {{ t('main.period') }}
                </label>
            </div>

            <div v-if="form.period === 'custom'" class="mt-4">
                <label>
                    {{ t('main.dateStart') }}:
                    <input type="date" v-model="form.start_date" required/>
                </label>
                <label>
                    {{ t('main.dateEnd') }}:
                    <input type="date" v-model="form.end_date" required/>
                </label>
            </div>

            <button type="submit" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded">
                {{ t('main.generateReport') }}
            </button>
        </form>

        <!-- Оборачиваем результаты в контейнер для PDF -->
        <div v-if="categoryStats.length || userStats.length" ref="pdfContent" class="p-12">
            <div class="text-center flex items-center gap-2 justify-center border border-black p-4">
                <img src="/images/logo.webp" alt="" class="w-20">
                <span class="text-xl font-bold">{{ t('main.udn') }}</span>
            </div>
            <div class="mt-8">
                <div class="text-end font-bold my-8">
                    {{ selectedDateText }}
                </div>

                <h2 class="text-xl font-bold mb-2 text-center">{{ t('main.statCategories') }}</h2>
                <table class="table-auto w-full mb-8">
                    <tbody>
                    <tr v-for="stat in categoryStats" :key="stat.category_id">
                        <td class="border border-black px-4 py-2">{{ getCategoryName(stat.category) }}</td>
                        <td class="border border-black px-4 py-2 text-center">{{ stat.total }}</td>
                    </tr>
                    <tr>
                        <td class="border border-black px-4 py-2 font-bold bg-yellow-300">{{ t('main.total') }}</td>
                        <td class="border border-black px-4 py-2 font-bold text-center bg-yellow-300">{{
                                categoryTotal
                            }}
                        </td>
                    </tr>
                    </tbody>
                </table>

                <h2 class="text-xl font-bold mb-2 text-center">{{ t('main.statUsers') }}</h2>
                <table class="table-auto w-full">
                    <tbody>
                    <tr v-for="stat in userStats" :key="stat.user_id">
                        <td class="border border-black px-4 py-2">{{ getUserName(stat.user) }}</td>
                        <td class="border border-black px-4 py-2 text-center">{{ stat.total }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="categoryStats.length || userStats.length">
            <button @click="downloadPDF" class="mt-4 px-4 py-2 bg-green-600 text-white rounded">
                {{ t('main.downloadPdf') }}
            </button>
        </div>
    </AdminLayout>
</template>
