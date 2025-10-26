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
import { defineComponent, PropType, computed } from 'vue'
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
        // field对象方式（优先）
        field: {
            type: Object as PropType<FormItemField>,
            required: false,
        },
        modelValue: {
            type: Object as PropType<Record<string, ModelValueTypes>>,
            required: true,
        },
        // 独立属性方式（备用）
        type: String,
        prop: String,
        label: String,
        placeholder: String,
        inputAttr: Object as PropType<Record<string, any>>,
        rules: [Object, Array] as PropType<FormItemRule | FormItemRule[]>,
    },
    emits: ['update:modelValue'],
    setup(props, { emit }) {
        // 计算最终的field对象
        const field = computed<FormItemField>(() => {
            if (props.field) {
                return props.field
            }
            // 从独立props构建field对象
            return {
                name: props.prop || '',
                title: props.label || '',
                type: props.type || 'string',
                attr: props.inputAttr,
                rules: props.rules,
            }
        })

        const onFieldUpdate = (value: ModelValueTypes) => {
            emit('update:modelValue', {
                ...props.modelValue,
                [field.value.name]: value,
            })
        }

        const onUpdate = (value: Record<string, ModelValueTypes>) => {
            emit('update:modelValue', value)
        }

        return {
            field,
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
