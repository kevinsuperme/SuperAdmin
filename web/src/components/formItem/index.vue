<template>
    <div class="form-item-container">
        <el-form-item
            v-if="!computedField.render"
            :label="computedField.title"
            :prop="computedField.name"
            :rules="computedField.rules"
            :label-width="computedField.labelWidth"
            :required="computedField.required"
            :error="computedField.error"
            :show-message="computedField.showMessage"
            :inline-message="computedField.inlineMessage"
            :size="computedField.size"
        >
            <BaInput
                :model-value="currentValue"
                :type="computedField.type"
                :attr="computedField.attr"
                :data="computedField.data"
                @update:model-value="onFieldUpdate"
            />
        </el-form-item>
        <component v-else :is="computedField.render" :field="computedField" :modelValue="modelValue" @update:modelValue="onUpdate" />
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
            type: [Object, String, Number, Boolean, Array] as PropType<Record<string, ModelValueTypes> | ModelValueTypes | undefined>,
            required: false,
            default: undefined,
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
        const computedField = computed<FormItemField>(() => {
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

        // 判断是对象模式还是单值模式
        const isObjectMode = computed(() => {
            return (
                typeof props.modelValue === 'object' &&
                !Array.isArray(props.modelValue) &&
                props.modelValue !== null &&
                'constructor' in props.modelValue &&
                props.modelValue.constructor === Object
            )
        })

        // 获取当前值
        const currentValue = computed(() => {
            if (isObjectMode.value) {
                return (props.modelValue as Record<string, ModelValueTypes>)[computedField.value.name]
            }
            return props.modelValue as ModelValueTypes
        })

        const onFieldUpdate = (value: ModelValueTypes) => {
            if (isObjectMode.value) {
                // 对象模式：更新对象中的特定字段
                emit('update:modelValue', {
                    ...(props.modelValue as Record<string, ModelValueTypes>),
                    [computedField.value.name]: value,
                })
            } else {
                // 单值模式：直接更新值
                emit('update:modelValue', value)
            }
        }

        const onUpdate = (value: Record<string, ModelValueTypes> | ModelValueTypes) => {
            emit('update:modelValue', value)
        }

        return {
            computedField,
            currentValue,
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
