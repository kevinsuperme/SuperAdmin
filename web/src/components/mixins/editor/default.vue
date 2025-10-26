<template>
    <div class="tips">{{ t('utils.Please install editor') }}</div>
</template>

<script setup lang="ts">
import { i18n } from '/@/lang'
const { t } = i18n.global
</script>

<style scoped lang="scss">
.tips {
    color: var(--el-text-color-placeholder);
}
</style>
