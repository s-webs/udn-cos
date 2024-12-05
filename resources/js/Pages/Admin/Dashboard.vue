<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {Head, router, usePage} from "@inertiajs/vue3";
import {useI18n} from "vue-i18n";
import {computed, onMounted, onUnmounted, reactive, ref} from "vue";
import axios from "axios";

const {t, locale} = useI18n();

const page = usePage();
const user = page.props.auth?.user || null;

const props = defineProps({
    tables: Array,
    selectedTableId: Number,
    tickets: Array,
});

const localizedTableName = (table) => {
    return table[`name_${locale.value}`] || table.name_ru;
};

const selectedTable = ref(props.selectedTableId);
const selectTable = (tableId) => {
    if (selectedTable.value !== tableId) {
        router.post(
            route("tables.assign", tableId),
            {table: tableId, user: user.id},
            {
                onSuccess: () => {
                    selectedTable.value = tableId;
                },
                onError: () => {
                    alert(t("errors.tableAssign"));
                },
            }
        );
    }
};

const unAssignTable = (tableId) => {
    router.post(
        route("tables.unAssign"),
        {table: tableId},
        {
            onSuccess: () => {
                selectedTable.value = null;
            },
            onError: () => {
                alert(t("errors.tableUnassign"));
            },
        }
    );
};

const assignedCategoryIds = computed(() => {
    const table = props.tables.find((t) => t.id === selectedTable.value);
    if (table && table.categories) {
        return table.categories.map((category) => category.id);
    }
    return [];
});

const tickets = reactive([]);

const totalTicketsInQueue = computed(() => tickets.length);

const currentTicket = ref(null);

const queueIsOpen = ref(true); // Добавлено состояние очереди

const fetchQueueStatus = () => {
    axios
        .get(route("queue.status"))
        .then((response) => {
            queueIsOpen.value = response.data.is_open;
        })
        .catch((error) => {
            console.error("Ошибка при получении состояния очереди:", error);
            queueIsOpen.value = false;
        });
};

const fetchTickets = () => {
    if (assignedCategoryIds.value.length === 0) {
        tickets.splice(0, tickets.length);
        return;
    }

    axios
        .get(route("tickets.index"), {
            params: {
                category_ids: assignedCategoryIds.value,
            },
        })
        .then((response) => {
            tickets.splice(0, tickets.length, ...response.data.tickets);
        })
        .catch((error) => {
            console.error("Ошибка при получении списка талонов:", error);
        });
};

const fetchCurrentTicket = () => {
    axios
        .get(route("tickets.current", {table_id: selectedTable.value}))
        .then((response) => {
            currentTicket.value = response.data.currentAssignment;
        })
        .catch((error) => {
            currentTicket.value = null;
            if (error.response && error.response.status !== 404) {
                console.error("Ошибка при получении текущего талона:", error);
            }
        });
};

onMounted(() => {
    fetchCurrentTicket();
    fetchTickets();
    fetchQueueStatus(); // Вызов получения состояния очереди

    window.Echo.channel("tickets")
        .listen("TicketCreated", (e) => {
            const ticketData = e;

            if (assignedCategoryIds.value.includes(ticketData.category_id)) {
                tickets.push(ticketData);
                tickets.sort((a, b) => a.ticket_number - b.ticket_number);
            }
        })
        .listen("TicketUpdated", (e) => {
            const ticketData = e;

            console.log("Получено событие TicketUpdated:", ticketData);

            const categoryId = ticketData.category_id || (ticketData.category && ticketData.category.id);

            if (!categoryId) {
                console.warn(`Талон с id ${ticketData.id} не имеет category_id или category.id.`);
                return;
            }

            if (
                ["processing", "completed", "skipped", "rejected", "cancelled"].includes(
                    ticketData.status
                )
            ) {
                const index = tickets.findIndex((ticket) => ticket.id === ticketData.id);
                if (index !== -1) {
                    tickets.splice(index, 1);
                    console.log(`Талон с id ${ticketData.id} удален из очереди.`);
                }
            } else if (ticketData.status === "waiting") {
                if (assignedCategoryIds.value.includes(categoryId)) {
                    const exists = tickets.some((ticket) => ticket.id === ticketData.id);
                    if (!exists) {
                        tickets.push(ticketData);
                        tickets.sort((a, b) => a.ticket_number - b.ticket_number);
                        console.log(`Талон с id ${ticketData.id} добавлен обратно в очередь.`);
                    }
                }
            }
        })
        .listen("QueueCleared", (e) => {
            const clearedCategoryIds = e.categoryIds;
            const isAffected = assignedCategoryIds.value.some((categoryId) =>
                clearedCategoryIds.includes(categoryId)
            );
            if (isAffected) {
                tickets.splice(0, tickets.length);
            }
        });

    // Подписываемся на изменения состояния очереди
    window.Echo.channel("queue-status")
        .listen("QueueStatusUpdated", (e) => {
            console.log("Получено событие QueueStatusUpdated:", e);
            queueIsOpen.value = e.is_open;
        });
});

onUnmounted(() => {
    window.Echo.leaveChannel("tickets");
    window.Echo.leaveChannel("queue-status"); // Покидаем канал
});

const nextTicket = () => {
    if (!queueIsOpen.value) {
        alert("Очередь закрыта. Невозможно вызвать следующий талон.");
        return;
    }

    if (!selectedTable.value) {
        alert("Пожалуйста, привяжите стол перед вызовом следующего талона.");
        return;
    }

    const completeCurrentTicket = currentTicket.value
        ? axios.post(route("tickets.complete", currentTicket.value.id))
        : Promise.resolve();

    completeCurrentTicket
        .then(() => {
            currentTicket.value = null;

            axios
                .post(route("tickets.assign"), {table_id: selectedTable.value})
                .then((response) => {
                    currentTicket.value = response.data.currentAssignment;

                    const index = tickets.findIndex(
                        (ticket) => ticket.id === currentTicket.value.ticket_id
                    );
                    if (index !== -1) {
                        tickets.splice(index, 1);
                    }
                })
                .catch((error) => {
                    if (error.response && error.response.status === 404) {
                        alert("Нет доступных талонов.");
                    } else {
                        console.error("Ошибка при назначении следующего талона:", error);
                    }
                });
        })
        .catch((error) => {
            console.error("Ошибка при завершении текущего талона:", error);
        });
};

const skipTicket = () => {
    if (!queueIsOpen.value) {
        alert("Очередь закрыта. Невозможно пропустить талон.");
        return;
    }

    if (!currentTicket.value) {
        alert("Нет текущего талона для пропуска.");
        return;
    }

    axios
        .post(route("tickets.skip", currentTicket.value.id))
        .then(() => {
            currentTicket.value = null;
            fetchTickets();
        })
        .catch((error) => {
            console.error("Ошибка при пропуске талона:", error);
        });
};

const rejectTicket = () => {
    if (!queueIsOpen.value) {
        alert("Очередь закрыта. Невозможно отклонить талон.");
        return;
    }

    if (!currentTicket.value) {
        alert("Нет текущего талона для отклонения.");
        return;
    }

    axios
        .post(route("tickets.reject", currentTicket.value.id))
        .then(() => {
            currentTicket.value = null;
            fetchTickets();
        })
        .catch((error) => {
            console.error("Ошибка при отклонении талона:", error);
        });
};

const clearQueue = () => {
    if (!queueIsOpen.value) {
        alert("Очередь закрыта. Невозможно очистить очередь.");
        return;
    }

    if (!selectedTable.value) {
        alert("Пожалуйста, выберите стол перед очисткой очереди.");
        return;
    }

    axios
        .post(route("tickets.clearQueue"), {category_ids: assignedCategoryIds.value})
        .then(() => {
            tickets.splice(0, tickets.length);
            alert("Очередь успешно очищена.");
        })
        .catch((error) => {
            console.error("Ошибка при очистке очереди:", error);
        });
};

const toggleQueueStatus = () => {
    axios
        .post(route("queue.toggle"))
        .then((response) => {
            queueIsOpen.value = response.data.is_open;
            const message = queueIsOpen.value ? "Очередь открыта." : "Очередь закрыта.";
            alert(message);
        })
        .catch((error) => {
            console.error("Ошибка при изменении состояния очереди:", error);
            alert("Произошла ошибка при изменении состояния очереди.");
        });
};
</script>

<template>
    <Head title="Панель управления"/>
    <AdminLayout>
        <!-- Уведомление о закрытой очереди -->
        <div v-if="!queueIsOpen" class="alert alert-warning text-red-600 font-bold mb-4 text-center text-xl">
            {{ t('main.queueStatusClosed') }}
        </div>

        <div class="border-b-2 pb-4">
            <span class="text-2xl">{{ t("main.inTheQueue") }}: {{ totalTicketsInQueue }}</span>
        </div>
        <div class="flex flex-row gap-4 mt-8">
            <div class="basis-1/5">
                <div class="bg-slate-200 border h-full rounded-lg text-center p-4">
                    <div v-if="currentTicket" class="h-full flex flex-col justify-center items-center">
                        <div class="text-xl font-bold">{{ t("main.currentTicket") }}</div>
                        <div class="text-4xl font-bold mt-8">{{ currentTicket.ticket.ticket_number }}</div>
                        <div class="mt-8">
                            {{ localizedTableName(currentTicket.ticket.category) }}
                        </div>
                    </div>
                    <div v-else class="flex flex-col justify-center items-center h-full">
                        <div class="text-2xl font-bold">{{ t("main.ticketNotAssign") }}</div>
                    </div>
                </div>
            </div>
            <div class="basis-4/5 grid grid-cols-5 gap-4 text-xl overflow-y-auto rounded-lg h-72">
                <div
                    v-for="ticket in tickets"
                    :key="ticket.id"
                    class="flex flex-col justify-center items-center p-4 rounded-lg border bg-gray-100 h-40"
                >
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
                <!-- Кнопка "Вызов" -->
                <button
                    @click="nextTicket"
                    :disabled="!queueIsOpen"
                    :class="[
                        'px-4 py-2 rounded-lg',
                        queueIsOpen
                            ? 'bg-green-600 hover:bg-green-700 text-white'
                            : 'bg-gray-400 text-gray-700 cursor-not-allowed',
                    ]"
                >
                    {{ t("main.nextTicket") }}
                </button>
                <!-- Кнопка "Пропустить" -->
                <button
                    @click="skipTicket"
                    :disabled="!queueIsOpen"
                    :class="[
                        'px-4 py-2 rounded-lg ml-2',
                        queueIsOpen
                            ? 'bg-yellow-600 hover:bg-yellow-700 text-white'
                            : 'bg-gray-400 text-gray-700 cursor-not-allowed',
                    ]"
                >
                    {{ t("main.skipTicket") }}
                </button>
                <!-- Кнопка "Отклонить" -->
                <button
                    @click="rejectTicket"
                    :disabled="!queueIsOpen"
                    :class="[
                        'px-4 py-2 rounded-lg ml-2',
                        queueIsOpen
                            ? 'bg-red-600 hover:bg-red-700 text-white'
                            : 'bg-gray-400 text-gray-700 cursor-not-allowed',
                    ]"
                >
                    {{ t("main.rejectTicket") }}
                </button>
            </div>
            <div class="flex gap-4">
                <!-- Кнопка "Очистить очередь" -->
                <button
                    @click="clearQueue"
                    :disabled="!queueIsOpen"
                    :class="[
                        'px-4 py-2 rounded-lg',
                        queueIsOpen
                            ? 'bg-blue-600 hover:bg-blue-700 text-white'
                            : 'bg-gray-400 text-gray-700 cursor-not-allowed',
                    ]"
                >
                    {{ t("main.clearQueue") }}
                </button>
                <!-- Кнопка "Закрыть очередь" -->
                <button
                    @click="toggleQueueStatus"
                    :class="[
                        'px-4 py-2 text-white rounded-lg',
                        queueIsOpen
                            ? 'bg-red-600 hover:bg-red-700'
                            : 'bg-green-600 hover:bg-green-700 animate-pulse',
                    ]"
                >
                    {{ queueIsOpen ? t("main.closeQueue") : t("main.queueOpen") }}
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
::-webkit-scrollbar {
    width: 0px;
    background: transparent;
}

html {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
