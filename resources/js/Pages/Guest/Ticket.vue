<script setup>
import { Head } from "@inertiajs/vue3";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { useI18n } from "vue-i18n";
import { onMounted, onUnmounted, ref, watch } from "vue";
import axios from 'axios';

const { t, locale } = useI18n();

const props = defineProps({
    ticket: Object,
    category: Object,
    locale: String
});

locale.value = props.locale;

const ticket = ref(props.ticket);

// Новая переменная для числа в очереди
const queueCount = ref(0);

// Аудио
const notificationSound = new Audio('/sounds/notification.mp3');

// Функция форматированного названия категории
const localizedCategoryName = (category) => {
    return category[`name_${locale.value}`] || category.name_ru;
};

// Функция получения количества ожидающих
const fetchQueueCount = () => {
    // Предположим, у категории есть `id`, иначе используйте props.ticket.category_id
    axios.get(route('tickets.queueCount', props.category.id))
        .then(response => {
            queueCount.value = response.data.count;
        })
        .catch(error => {
            console.error('Ошибка при получении очереди:', error);
        });
};

// Монтирование
onMounted(() => {
    // 1) Получаем свежие данные талона
    axios.get(route('ticket.fetch', props.ticket.id))
        .then(response => {
            ticket.value = response.data.ticket;
        })
        .catch(error => {
            console.error('Ошибка при получении талона:', error);
        });

    // 2) Подписываемся на канал tickets
    window.Echo.channel('tickets')
        .listen('TicketUpdated', (e) => {
            console.log("TicketUpdated:", e);
            // Если это обновление именно нашего талона
            if (e.id === ticket.value.id) {
                ticket.value = e;
            }
            // Если нужно динамически обновлять queueCount при изменениях категории
            if (e.category_id === props.ticket.category_id) {
                fetchQueueCount();
            }
        });

    // 3) Получаем кол-во ожидающих в очереди
    fetchQueueCount();
});

// По размонтировании уходим из канала
onUnmounted(() => {
    window.Echo.leaveChannel('tickets');
});

// Следим за изменением статуса
watch(
    () => ticket.value.status,
    (newStatus, oldStatus) => {
        if (newStatus === 'processing' && newStatus !== oldStatus) {
            // Воспроизведение звука
            notificationSound.play().catch((error) => {
                console.error('Ошибка при воспроизведении звука:', error);
            });
        }
    }
);
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
                    <div v-if="ticket.status === 'processing'">
                        <span>№{{ ticket.ticket_number }}</span>
                        <font-awesome-icon class="ml-3 mr-3" :icon="['fas', 'right-long']"/>
                        <span>{{ ticket.assignments[0].table.number }}</span>
                    </div>
                    <div v-else>
                        <span>№{{ ticket.ticket_number }}</span>
                    </div>
                </div>
                <div class="text-center uppercase text-xl border p-2 font-bold">
                    <div v-if="ticket.status === 'waiting'" class="text-blue-600">
                        {{ t('main.waiting') }}
                    </div>
                    <div v-if="ticket.status === 'processing'" class="text-green-600">
                        {{ t('main.expected') }}
                    </div>
                    <div v-if="ticket.status === 'completed'" class="text-green-600">
                        {{ t('main.complete') }}
                    </div>
                    <div v-if="ticket.status === 'rejected'" class="text-red-600">
                        {{ t('main.rejected') }}
                    </div>
                </div>
            </div>
            <!-- Отображаем кол-во человек в очереди -->
            <div class="text-center mt-4">
                {{ t('main.inTheQueue') }} {{ queueCount }}
            </div>
        </div>
    </GuestLayout>
</template>
