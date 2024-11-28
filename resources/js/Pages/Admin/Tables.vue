<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {Head, router, useForm} from "@inertiajs/vue3";
import {useI18n} from "vue-i18n";
import ModalAccept from "@/Components/ModalAccept.vue";
import {ref} from "vue";
import ModalEditTable from "@/Components/ModalEditTable.vue";
import ModalEditCategory from "@/Components/ModalEditCategory.vue";

const {t, locale} = useI18n();

const props = defineProps({
    tables: Array,
    categories: Array,
    flash: Object,
})

const localizedTableName = (table) => {
    return table[`name_${locale.value}`] || table.name_ru;
};

const form = useForm({
    name_kz: null,
    name_ru: null,
    number: null,
})

// ADD TABLE
const addTable = () => {
    form.post(route('createTable'), {
        onSuccess: () => {
            form.reset();
        },
        onError: (errors) => {
            console.error(errors);
        }
    });
}

// DELETE TABLE
const isModalAcceptVisible = ref(false)
const currentId = ref(null)

const openModalAccept = (id) => {
    isModalAcceptVisible.value = true;
    currentId.value = id
};

const handleModalAccept = (value) => {
    console.log('Accepted:', value);
    router.delete(route('deleteTable', currentId.value))
    currentId.value = null
};

const handleModalAcceptClose = () => {
    isModalAcceptVisible.value = false;
};

// EDIT TABLE
const isModalEditVisible = ref(false)
const editedTable = ref(null)

const openModalEdit = (category) => {
    isModalEditVisible.value = true;
    editedTable.value = category
};

const handleModalEdit = (updatedCategory) => {
    if (editedTable.value) {
        router.patch(route('updateTable', editedTable.value.id), updatedCategory, {
            onSuccess: () => {
                console.log('Стол обновлен');
            },
            onError: (errors) => {
                console.error(errors);
            }
        });
    }
    editedTable.value = null;
    isModalEditVisible.value = false;
};

const handleModalEditClose = () => {
    isModalEditVisible.value = false;
};
</script>

<template>
    <Head title="Столы"/>
    <ModalAccept :show="isModalAcceptVisible"
                 :text="t('main.areYouSure')"
                 :action="t('main.delete')"
                 @accept="handleModalAccept"
                 @close="handleModalAcceptClose"/>
    <ModalEditTable
        :show="isModalEditVisible"
        :action="t('main.update')"
        :table="editedTable"
        :categories="categories"
        @accept="handleModalEdit"
        @close="handleModalEditClose"
    />

    <AdminLayout>
        <div class="flex flex-col h-full">
            <div>
                <div class="border-b-2 pb-4">
                    <span class="text-2xl">{{ t('main.addNewTable') }}</span>
                </div>
                <form @submit.prevent="addTable">
                    <div class="grid grid-cols-3 gap-4 mt-4">
                        <label class="block mb-2" for="grid-name-kz">
                            {{ t('main.tableInKz') }}
                            <input
                                v-model="form.name_kz"
                                class="appearance-none block w-full bg-gray-200 text-gray-700 border rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                                id="grid-name-kz" type="text" placeholder="KZ">
                        </label>
                        <label class="block mb-2" for="grid-name-ru">
                            {{ t('main.tableInRu') }}
                            <input
                                v-model="form.name_ru"
                                class="appearance-none block w-full bg-gray-200 text-gray-700 border rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                                id="grid-name-ru" type="text" placeholder="RU">
                        </label>
                        <label class="block mb-2" for="grid-number">
                            {{ t('main.numberTable') }}
                            <input
                                v-model="form.number"
                                class="appearance-none block w-full bg-gray-200 text-gray-700 border rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                                id="grid-number" type="number" min="1" placeholder="NUM">
                        </label>
                    </div>
                    <button
                        class="bg-green-500 py-2 px-4 rounded-lg hover:bg-green-600 text-white">{{
                            t('main.add')
                        }}
                    </button>
                </form>
            </div>
            <div class="border-b-2 pb-4 mt-8">
                <span class="text-2xl">{{ t('main.listTables') }}</span>
            </div>
            <div class="flex-1 overflow-y-auto mt-4"> <!-- Используем flex-1 для заполнения свободного пространства -->
                <div v-for="table in tables" class="flex flex-row justify-between items-center mt-4 border p-2 rounded-lg">
                    <div class="text-xl">{{ table.number }}. {{ localizedTableName(table) }}</div>
                    <div class="flex flex-row gap-4">
                        <button @click="openModalEdit(table)"
                            class="bg-green-500 py-2 px-4 rounded-lg hover:bg-green-600 text-white">
                            {{ t('main.edit') }}
                        </button>
                        <button @click="openModalAccept(table.id)"
                            class="bg-red-500 py-2 px-4 rounded-lg hover:bg-red-600 text-white">{{
                                t('main.delete')
                            }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>

</style>
