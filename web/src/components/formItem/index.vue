<template>
    <div class="form-item-container">
        <el-form-item
            v-if="!field.render"
            :label="field.title"
            :prop="field.name"
            :rules="field.rules"
            :label-width="field.labelWidth"
            :required="field.required"
            :error="field.error"
            :show-message="field.showMessage"
            :inline-message="field.inlineMessage"
            :size="field.size"
        >
            <BaInput
                :model-value="modelValue[field.name]"
                :type="field.type"
                :attr="field.attr"
                :data="field.data"
                @update:model-value="onFieldUpdate"
            />
        </el-form-item>
        <component v-else :is="field.render" :field="field" :modelValue="modelValue" @update:modelValue="onUpdate" />
    </div>
</template>

<script lang="ts">
import { defineComponent, PropType } from 'vue'
import type { FormItemRule } from 'element-plus'
import BaInput from '/@/components/baInput/index.vue'
import type { ModelValueTypes } from '/@/components/baInput'

// 字段配置接口
export interface FormItemField {
    // 基础属性
    name: string
    title: string
    type: string

    // 表单项属性
    rules?: FormItemRule | FormItemRule[]
    labelWidth?: string | number
    required?: boolean
    error?: string
    showMessage?: boolean
    inlineMessage?: boolean
    size?: 'large' | 'default' | 'small'

    // 输入框属性
    attr?: Record<string, any>
    data?: Record<string, any>

    // 自定义渲染
    render?: any
}

export default defineComponent({
    name: 'FormItem',
    components: {
        BaInput,
    },
    props: {
        field: {
            type: Object as PropType<FormItemField>,
            required: true,
        },
        modelValue: {
            type: Object as PropType<Record<string, ModelValueTypes>>,
            required: true,
        },
    },
    emits: ['update:modelValue'],
    setup(props, { emit }) {
        const onFieldUpdate = (value: ModelValueTypes) => {
            emit('update:modelValue', {
                ...props.modelValue,
                [props.field.name]: value,
            })
        }

        const onUpdate = (value: Record<string, ModelValueTypes>) => {
            emit('update:modelValue', value)
        }

        return {
            onFieldUpdate,
            onUpdate,
        }
    },
})
</script>

<style scoped lang="scss">
.form-item-container {
    width: 100%;
}
</style>
