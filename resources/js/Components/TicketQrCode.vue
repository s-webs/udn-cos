<script setup>
import QRCode from 'qrcode.vue';

const props = defineProps({
    url: String
})
</script>

<template>
    <QRCode :value="url" :size="320" />
</template>

<style scoped>

</style>
