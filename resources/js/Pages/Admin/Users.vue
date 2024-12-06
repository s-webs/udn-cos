<script setup>
import { ref, reactive } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {useI18n} from "vue-i18n";

const {t, locale} = useI18n();

// Из пропсов получаем список пользователей
const props = defineProps({
    users: Array,
});

const users = ref(props.users || []);

// Форма для добавления нового пользователя
const addForm = reactive({
    name: '',
    email: '',
    password: ''
});

// Модальное окно для редактирования пользователя
const isEditModalOpen = ref(false);
const editForm = reactive({
    id: null,
    name: '',
    email: '',
    password: ''
});

// Модальное окно для удаления пользователя
const isDeleteModalOpen = ref(false);
const deleteUserId = ref(null);

// Добавление пользователя
const addUser = () => {
    axios.post(route('users.store'), {
        name: addForm.name,
        email: addForm.email,
        password: addForm.password
    }).then(response => {
        users.value.push(response.data.user);
        addForm.name = '';
        addForm.email = '';
        addForm.password = '';
    }).catch(error => {
        console.error('Ошибка при добавлении пользователя:', error);
    });
};

// Открыть модалку редактирования
const openEditModal = (user) => {
    editForm.id = user.id;
    editForm.name = user.name;
    editForm.email = user.email;
    editForm.password = '';
    isEditModalOpen.value = true;
};

// Применить изменения при редактировании
const acceptEdit = () => {
    axios.patch(route('users.update', editForm.id), {
        name: editForm.name,
        email: editForm.email,
        password: editForm.password
    }).then(response => {
        const index = users.value.findIndex(u => u.id === editForm.id);
        if (index !== -1) {
            users.value[index] = response.data.user;
        }
        closeEditModal();
    }).catch(error => {
        console.error('Ошибка при редактировании пользователя:', error);
    });
};

// Закрыть модалку редактирования
const closeEditModal = () => {
    isEditModalOpen.value = false;
};

// Открыть модалку удаления
const openDeleteModal = (user) => {
    deleteUserId.value = user.id;
    isDeleteModalOpen.value = true;
};

// Принять удаление
const acceptDelete = () => {
    axios.delete(route('users.destroy', deleteUserId.value))
        .then(() => {
            users.value = users.value.filter(u => u.id !== deleteUserId.value);
            closeDeleteModal();
        })
        .catch(error => {
            console.error('Ошибка при удалении пользователя:', error);
        });
};

// Закрыть модалку удаления
const closeDeleteModal = () => {
    isDeleteModalOpen.value = false;
};
</script>

<template>
    <Head title="Пользователи"/>

    <AdminLayout>
        <h1 class="text-2xl font-bold mb-4">{{ t('main.usersManagement') }}</h1>
        <!-- Форма добавления пользователя -->
        <div class="mb-8">
            <form @submit.prevent="addUser">
                <div class="grid grid-cols-3 gap-4 mt-4">
                    <label class="block mb-2">
                        {{ t('main.name') }}
                        <input v-model="addForm.name"
                               class="appearance-none block w-full bg-gray-200 text-gray-700 border rounded py-3 px-4 mb-3"
                               type="text" placeholder="Имя" required>
                    </label>
                    <label class="block mb-2">
                        Email
                        <input v-model="addForm.email"
                               class="appearance-none block w-full bg-gray-200 text-gray-700 border rounded py-3 px-4 mb-3"
                               type="email" placeholder="Email" required>
                    </label>
                    <label class="block mb-2">
                        {{ t('main.password') }}
                        <input v-model="addForm.password"
                               class="appearance-none block w-full bg-gray-200 text-gray-700 border rounded py-3 px-4 mb-3"
                               type="password" placeholder="Пароль" required>
                    </label>
                </div>
                <button
                    class="bg-green-500 py-2 px-4 rounded-lg hover:bg-green-600 text-white">
                    {{ t('main.add') }}
                </button>
            </form>
        </div>

        <!-- Список пользователей -->
        <div class="mt-8">
            <div v-for="user in users" :key="user.id" class="flex justify-between items-center border p-2 rounded-lg mb-4">
                <div class="flex items-center gap-2">
                    <div>{{ user.name }}</div>
                    <div><span class="font-bold">Login:</span> {{ user.email }}</div>
                </div>
                <div class="flex gap-2">
                    <button @click="openEditModal(user)" class="px-2 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">{{ t('main.edit') }}</button>
                    <button @click="openDeleteModal(user)" class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600">{{ t('main.delete') }}</button>
                </div>
            </div>
        </div>

        <!-- Модалка редактирования -->
        <transition name="fade">
            <div v-if="isEditModalOpen">
                <div @click="closeEditModal" class="fixed bg-black opacity-70 inset-0 z-0"></div>
                <div class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-lg p-3 mx-auto my-auto rounded-xl shadow-lg bg-white z-50">
                    <div>
                        <label class="block mb-2">
                            {{ t('main.name') }}
                            <input v-model="editForm.name"
                                   class="appearance-none block w-full bg-gray-200 text-gray-700 border rounded py-3 px-4 mb-3"
                                   type="text" placeholder="Имя" required>
                        </label>
                        <label class="block mb-2">
                            Email
                            <input v-model="editForm.email"
                                   class="appearance-none block w-full bg-gray-200 text-gray-700 border rounded py-3 px-4 mb-3"
                                   type="email" placeholder="Email" required>
                        </label>
                        <label class="block mb-2">
                            {{ t('main.password') }} ({{ t('main.leaveEmpty') }})
                            <input v-model="editForm.password"
                                   class="appearance-none block w-full bg-gray-200 text-gray-700 border rounded py-3 px-4 mb-3"
                                   type="password" placeholder="Пароль">
                        </label>
                    </div>
                    <div class="p-3 mt-2 text-center space-x-4 md:block">
                        <button @click="acceptEdit"
                                class="mb-2 md:mb-0 bg-red-500 border border-red-500 px-5 py-2 text-sm text-white rounded-md hover:bg-red-600">
                            {{ t('main.save') }}
                        </button>
                        <button @click="closeEditModal"
                                class="mb-2 md:mb-0 bg-white px-5 py-2 text-sm border text-gray-600 rounded-md hover:bg-gray-100">
                            {{ t('main.cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>

        <!-- Модалка удаления -->
        <transition name="fade">
            <div v-if="isDeleteModalOpen">
                <div @click="closeDeleteModal" class="fixed bg-black opacity-70 inset-0 z-0"></div>
                <div class="fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-full max-w-lg p-3 mx-auto my-auto rounded-xl shadow-lg bg-white z-50">
                    <div class="text-center p-3 flex-auto justify-center leading-6">
                        <svg xmlns="http://www.w3.org/2000/svg"
                             class="w-16 h-16 text-red-600 mx-auto"
                             viewBox="0 0 20 20"
                             fill="currentColor">
                            <path fill-rule="evenodd"
                                  d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                  clip-rule="evenodd"/>
                        </svg>
                        <h2 class="text-2xl font-bold py-4">{{ t('main.confirmDeleteUser') }}</h2>
                    </div>
                    <div class="p-3 mt-2 text-center space-x-4 md:block">
                        <button @click="acceptDelete"
                                class="mb-2 md:mb-0 bg-red-500 border border-red-500 px-5 py-2 text-sm text-white rounded-md hover:bg-red-600">
                            {{ t('main.delete') }}
                        </button>
                        <button @click="closeDeleteModal"
                                class="mb-2 md:mb-0 bg-white px-5 py-2 text-sm border text-gray-600 rounded-md hover:bg-gray-100">
                            {{ t('main.cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </transition>
    </AdminLayout>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active {
    transition: opacity .3s;
}
.fade-enter-from, .fade-leave-to {
    opacity: 0;
}
</style>
