<script setup>
import {Head} from "@inertiajs/vue3";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import {useI18n} from "vue-i18n";
import {onMounted, onUnmounted, ref, watch} from "vue";

const {t, locale} = useI18n();

const props = defineProps({
    ticket: Object,
    category: Object,
    locale: String
})

locale.value = props.locale
const ticket = ref(props.ticket);

onMounted(() => {
    axios.get(route('ticket.fetch', props.ticket.id))
        .then(response => {
            ticket.value = response.data.ticket;
        })
        .catch(error => {
            console.error('Ошибка при получении талона:', error);
        });
    window.Echo.channel('tickets')
        .listen('TicketUpdated', (e) => {
            console.log(e)
            console.log(ticket.value)
            if (e.id === ticket.value.id) {
                ticket.value = e;
            }
        });
});

onUnmounted(() => {
    window.Echo.leaveChannel('tickets');
});

const localizedCategoryName = (category) => {
    return category[`name_${locale.value}`] || category.name_ru;
};


const notificationSound = new Audio('/sounds/notification.mp3');

watch(
    () => ticket.value.status,
    (newStatus, oldStatus) => {
        if (newStatus === 'processing' && newStatus !== oldStatus) {
            // Воспроизводим звук
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
                    <div v-if="ticket.status === 'waiting'" class="text-blue-600">{{ t('main.waiting') }}</div>
                    <div v-if="ticket.status === 'processing'" class="text-green-600">
                        {{ t('main.expected') }}
                    </div>
                    <div v-if="ticket.status === 'completed'" class="text-green-600">{{ t('main.complete') }}</div>
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
