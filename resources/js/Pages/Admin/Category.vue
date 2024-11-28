<script setup>
import AdminLayout from "@/Layouts/AdminLayout.vue";
import {Head, router, useForm} from "@inertiajs/vue3";
import {useI18n} from "vue-i18n";
import ModalAccept from "@/Components/ModalAccept.vue";
import {ref} from "vue";
import ModalEditCategory from "@/Components/ModalEditCategory.vue";

const {t, locale} = useI18n();

const props = defineProps({
    categories: Array,
    flash: Object,
})

const isModalAcceptVisible = ref(false)
const isModalEditVisible = ref(false)
const currentId = ref(null)
const editedCategory = ref(null)

const localizedCategoryName = (category) => {
    return category[`name_${locale.value}`] || category.name_ru; // Фоллбек на name_ru
};

const form = useForm({
    name_kz: null,
    name_ru: null
})

// ADD CATEGORY
const addCategory = () => {
    form.post(route('addCategory'), {
        onSuccess: () => {
            form.reset();
        },
        onError: (errors) => {
            console.error(errors);
        }
    });
}

// DELETE CATEGORY
const openModalAccept = (id) => {
    isModalAcceptVisible.value = true;
    currentId.value = id
};

const handleModalAccept = (value) => {
    console.log('Accepted:', value);
    router.delete(route('deleteCategory', currentId.value))
    currentId.value = null
};

const handleModalAcceptClose = () => {
    isModalAcceptVisible.value = false;
};

// EDIT CATEGORY
const openModalEdit = (category) => {
    isModalEditVisible.value = true;
    editedCategory.value = category
};

const handleModalEdit = (updatedCategory) => {
    if (editedCategory.value) {
        router.patch(route('updateCategory', editedCategory.value.id), updatedCategory, {
            onSuccess: () => {
                console.log('Категория обновлена');
            },
            onError: (errors) => {
                console.error(errors);
            }
        });
    }
    editedCategory.value = null;
    isModalEditVisible.value = false;
};

const handleModalEditClose = () => {
    isModalEditVisible.value = false;
};

</script>

<template>
    <Head title="Категории"/>
    <AdminLayout>
        <div>
            <ModalAccept :show="isModalAcceptVisible"
                         :text="t('main.areYouSure')"
                         :action="t('main.delete')"
                         @accept="handleModalAccept"
                         @close="handleModalAcceptClose"/>

            <ModalEditCategory :show="isModalEditVisible"
                               :action="t('main.update')"
                               :category="editedCategory"
                               @accept="handleModalEdit"
                               @close="handleModalEditClose"
            />
        </div>
        <div class="flex flex-col h-full">
            <div>
                <div class="border-b-2 pb-4">
                    <span class="text-2xl">{{ t('main.creatingCategory') }}</span>
                </div>
                <form @submit.prevent="addCategory">
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <label class="block mb-2" for="grid-name-kz">
                            {{ t('main.categoryInKz') }}
                            <input
                                v-model="form.name_kz"
                                class="appearance-none block w-full bg-gray-200 text-gray-700 border rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                                id="grid-name-kz" type="text" placeholder="KZ">
                        </label>
                        <label class="block mb-2" for="grid-name-ru">
                            {{ t('main.categoryInRu') }}
                            <input
                                v-model="form.name_ru"
                                class="appearance-none block w-full bg-gray-200 text-gray-700 border rounded py-3 px-4 mb-3 leading-tight focus:outline-none focus:bg-white"
                                id="grid-name-ru" type="text" placeholder="RU">
                        </label>
                    </div>
                    <button
                        :disabled="form.processing"
                        class="bg-green-500 py-2 px-4 rounded-lg hover:bg-green-600 text-white">{{
                            t('main.add')
                        }}
                    </button>
                </form>
            </div>
            <div class="border-b-2 pb-4 mt-8">
                <span class="text-2xl">{{ t('main.categoryList') }}</span>
            </div>
            <div class="flex-1 overflow-y-auto mt-4"> <!-- Используем flex-1 для заполнения свободного пространства -->
                <div v-for="(category, index) in categories" :key="category.id"
                     class="flex flex-row justify-between items-center mt-4 border p-2 rounded-lg">
                    <div class="text-xl">{{ index+1 }}. {{ localizedCategoryName(category) }}</div>
                    <div class="flex flex-row gap-4">
                        <button @click="openModalEdit(category)"
                                class="bg-green-500 py-2 px-4 rounded-lg hover:bg-green-600 text-white">
                            {{ t('main.edit') }}
                        </button>
                        <button @click="openModalAccept(category.id)"
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
::-webkit-scrollbar { /* chrome based */
    width: 0px; /* ширина scrollbar'a */
    background: transparent; /* опционально */
}

html {
    -ms-overflow-style: none; /* IE 10+ */
    scrollbar-width: none; /* Firefox */
}
</style>
