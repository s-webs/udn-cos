<script setup>
import {Head} from "@inertiajs/vue3";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import {onMounted, onUnmounted, ref, watch} from "vue";

const props = defineProps({
    tickets: Array,
});

const processingTickets = ref(props.tickets);

onMounted(() => {
    window.Echo.channel('tickets')
        .listen('TicketUpdated', (e) => {
            // Обновляем список талонов при изменении статуса
            updateTickets(e);
        });
});

onUnmounted(() => {
    window.Echo.leaveChannel('tickets');
});

const updateTickets = (ticket) => {
    const index = processingTickets.value.findIndex(t => t.id === ticket.id);

    if (ticket.status === 'processing') {
        if (index === -1) {
            processingTickets.value.push(ticket);
        } else {
            processingTickets.value[index] = ticket;
        }
    } else {
        if (index !== -1) {
            processingTickets.value.splice(index, 1);
        }
    }

    // Сортируем талоны по времени назначения (новые вверху)
    processingTickets.value.sort((a, b) => {
        const dateA = new Date(a.processing_started_at);
        const dateB = new Date(b.processing_started_at);
        return dateB - dateA;
    });
};

const notificationSound = new Audio('/sounds/notification.mp3');

watch(
    () => processingTickets.value.length,
    (newLength, oldLength) => {
        if (newLength > oldLength) {
            notificationSound.play().catch((error) => {
                console.error('Ошибка при воспроизведении звука:', error);
            });
        }
    }
);
</script>

<template>
    <Head title="Зал ожидания"></Head>
    <GuestLayout>
        <div class="grid grid-cols-4 gap-4 text-8xl font-bold h-full overflow-y-auto p-4 rounded-lg">
            <div
                v-for="ticket in processingTickets"
                :key="ticket.id"
                class="flex flex-row gap-4 justify-center items-center content-center p-4 rounded-lg bg-gray-100 h-64"
            >
                <span>№{{ ticket.ticket_number }}</span>
                <font-awesome-icon :icon="['fas', 'right-long']"/>
                <span>{{ ticket.assignments[0]?.table?.number || '-' }}</span>
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
