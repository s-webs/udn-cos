<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {Head, router, usePage} from "@inertiajs/vue3";
import {useI18n} from "vue-i18n";
import {computed, onMounted, onUnmounted, reactive, ref} from "vue";

const {t, locale} = useI18n();

const page = usePage();
const user = page.props.auth?.user || null;

const props = defineProps({
    tables: Array,
    selectedTableId: Number,
    tickets: Array
})

const localizedTableName = (table) => {
    return table[`name_${locale.value}`] || table.name_ru;
};

const selectedTable = ref(props.selectedTableId);
const selectTable = (tableId) => {
    if (selectedTable.value !== tableId) {
        router.post(
            route('tables.assign', tableId),
            {table: tableId, user: user.id},
            {
                onSuccess: () => {
                    selectedTable.value = tableId;
                },
                onError: () => {
                    alert(t('errors.tableAssign'));
                },
            }
        );
    }
};

const unAssignTable = (tableId) => {
    router.post(
        route('tables.unAssign'),
        {table: tableId},
        {
            onSuccess: () => {
                selectedTable.value = null;
            },
            onError: () => {
                alert(t('errors.tableUnassign'));
            },
        }
    );
};

const assignedCategoryIds = computed(() => {
    const table = props.tables.find(t => t.id === selectedTable.value);
    if (table && table.categories) {
        return table.categories.map(category => category.id);
    }
    return [];
});

const tickets = reactive(
    props.tickets.filter(ticket => assignedCategoryIds.value.includes(ticket.category_id))
);
const totalTicketsInQueue = computed(() => tickets.length);

const fetchCurrentTicket = () => {
    axios.get(route('tickets.current', {table_id: selectedTable.value}))
        .then(response => {
            console.log(response.data)
            currentTicket.value = response.data.currentAssignment;
        })
        .catch(error => {
            currentTicket.value = null;
            if (error.response && error.response.status !== 404) {
                console.error('Ошибка при получении текущего талона:', error);
            }
        });
};

onMounted(() => {
    fetchCurrentTicket();
    window.Echo.channel('tickets')
        .listen('TicketCreated', (e) => {
            const ticketData = e;

            if (assignedCategoryIds.value.includes(ticketData.category_id)) {
                tickets.push(ticketData);
                tickets.sort((a, b) => a.ticket_number - b.ticket_number);
            }
        })
        .listen('TicketUpdated', (e) => {
            const ticketData = e;

            // Если талон изменил статус и больше не ожидает, удаляем его из списка
            if (
                ['processing', 'completed', 'skipped', 'rejected'].includes(
                    ticketData.status
                )
            ) {
                const index = tickets.findIndex((ticket) => ticket.id === ticketData.id);
                if (index !== -1) {
                    tickets.splice(index, 1);
                }
            }
        });
})

onUnmounted(() => {
    window.Echo.leaveChannel('tickets');
});

const currentTicket = ref(null);

const nextTicket = () => {
    if (!selectedTable.value) {
        alert('Пожалуйста, привяжите стол перед вызовом следующего талона.');
        return;
    }

    // Если есть текущий талон, завершаем его
    const completeCurrentTicket = currentTicket.value
        ? axios.post(route('tickets.complete', currentTicket.value.id))
        : Promise.resolve();

    completeCurrentTicket
        .then(() => {
            // Сбрасываем текущий талон
            currentTicket.value = null;

            // Запрашиваем следующий талон
            axios.post(route('tickets.assign'), {table_id: selectedTable.value})
                .then(response => {
                    currentTicket.value = response.data.currentAssignment;

                    // Удаляем талон из списка ожидающих
                    const index = tickets.findIndex((ticket) => ticket.id === currentTicket.value.ticket_id);
                    if (index !== -1) {
                        tickets.splice(index, 1);
                    }
                })
                .catch(error => {
                    if (error.response && error.response.status === 404) {
                        alert('Нет доступных талонов.');
                    } else {
                        console.error('Ошибка при назначении следующего талона:', error);
                    }
                });
        })
        .catch(error => {
            console.error('Ошибка при завершении текущего талона:', error);
        });
};

</script>

<template>
    <Head title="Панель управления"/>
    <AdminLayout>
        <div class="border-b-2 pb-4">
            <span class="text-2xl">{{ t('main.inTheQueue') }}: {{ totalTicketsInQueue }}</span>
        </div>
        <div class="flex flex-row gap-4 mt-8">
            <div class="basis-1/5">
                <div class="bg-slate-200 border h-full rounded-lg text-center p-4">
                    <div v-if="currentTicket" class="h-full flex flex-col justify-center items-center">
                        <div class="text-xl font-bold">{{ t('main.currentTicket') }}</div>
                        <div class="text-4xl font-bold mt-8">{{ currentTicket.ticket.ticket_number }}</div>
                        <div class="mt-8">
                            {{ localizedTableName(currentTicket.ticket.category) }}
                        </div>
                    </div>
                    <div v-else class="flex flex-col justify-center items-center h-full">
                        <div class="text-2xl font-bold">{{ t('main.ticketNotAssign') }}</div>
                    </div>
                </div>
            </div>
            <div class="basis-4/5 grid grid-cols-5 gap-4 text-xl overflow-y-auto rounded-lg h-72">
                <div v-for="ticket in tickets" :key="ticket.id"
                     class="flex flex-col justify-center items-center p-4 rounded-lg border bg-gray-100 h-40">
                    <div class="flex flex-row gap-4 justify-center items-center content-center">
                        <span class="font-bold">Талон №{{ ticket.ticket_number }}</span>
                    </div>
                    <div class="text-sm text-center">
                        <span>{{ localizedTableName(ticket.category) }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex justify-between mt-8">
            <div>
                <button @click="nextTicket" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                    {{ t('main.nextTicket') }}
                </button>
                <button @click="skipTicket"
                        class="px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg ml-2">
                    {{ t('main.skipTicket') }}
                </button>
                <button @click="rejectTicket" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg ml-2">
                    {{ t('main.rejectTicket') }}
                </button>
            </div>
            <div class="flex gap-4">
                <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">{{
                        t('main.clearQueue')
                    }}
                </button>
                <button class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">{{
                        t('main.closeQueue')
                    }}
                </button>
            </div>
        </div>
        <div class="border-b-2 pb-4 mt-8 mb-4">
            <span class="text-2xl">{{ t('main.selectionTable') }}</span>
        </div>
        <div class="grid grid-cols-5 gap-4">
            <div v-for="table in tables" :key="table.id"
                 class="p-4 box-border rounded-lg bg-gray-100 border flex flex-col h-full">
                <div class="flex-1">
                    <div class="text-center">
                        <span class="text-2xl text-center font-bold">{{ localizedTableName(table) }}</span>
                    </div>
                    <div class="mt-4">
                        <div class="text-center">
                            <span class="font-bold text-lg">Привязанные категории</span>
                        </div>
                        <ul class="text-sm text-left mt-2">
                            <li class="mb-2" v-for="(category, index) in table.categories" :key="category.id">
                                {{ index + 1 }}. {{ localizedTableName(category) }}
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="text-lg text-center box-border mt-8">
                    <button
                        v-if="selectedTable !== table.id"
                        @click="selectTable(table.id)"
                        :class="{
                            'bg-gray-400': selectedTable === table.id || table.isOccupied,
                            'bg-green-600 hover:bg-green-700': selectedTable !== table.id && !table.isOccupied
                        }"
                        :disabled="selectedTable === table.id || table.isOccupied"
                        class="px-4 py-2 box-border rounded-lg text-white"
                    >
                        {{
                            selectedTable === table.id ? 'Стол привязан' : table.isOccupied ? 'Стол занят' : 'Выбрать стол'
                        }}
                    </button>
                    <button
                        v-if="selectedTable === table.id"
                        @click="unAssignTable(table.id)"
                        class="mt-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg"
                    >
                        Отвязать стол
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
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
