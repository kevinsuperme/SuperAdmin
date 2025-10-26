<script lang="ts">
import { isArray, isString } from 'lodash-es'
import type { PropType, VNode } from 'vue'
import { createVNode, defineComponent, reactive, resolveComponent } from 'vue'
import { getArea } from '/@/api/common'
import type { anyObj, InputAttr, InputData, ModelValueTypes } from '/@/components/baInput'
import { inputTypes } from '/@/components/baInput'
import Array from '/@/components/baInput/components/array.vue'
import BaUpload from '/@/components/baInput/components/baUpload.vue'
import Editor from '/@/components/baInput/components/editor.vue'
import IconSelector from '/@/components/baInput/components/iconSelector.vue'
import RemoteSelect from '/@/components/baInput/components/remoteSelect.vue'

export default defineComponent({
    name: 'BaInput',
    props: {
        // 输入框类型
        type: {
            type: String,
            required: true,
            validator: (value: string) => {
                return inputTypes.includes(value)
            },
        },
        // 双向绑定值
        modelValue: {
            required: true,
        },
        // 输入框的附加属性
        attr: {
            type: Object as PropType<InputAttr>,
            default: () => ({}),
        },
        // 额外数据
        data: {
            type: Object as PropType<InputData>,
            default: () => ({}),
        },
    },
    emits: ['update:modelValue'],
    setup(props, { emit }) {
        const state = reactive({
            areaData: [],
        })

        // 获取地区数据
        const getAreaData = async () => {
            if (props.type === 'city' && state.areaData.length === 0) {
                const { data } = await getArea([])
                state.areaData = data
            }
        }
        getAreaData()

        // 更新值
        const onInput = (value: ModelValueTypes) => {
            emit('update:modelValue', value)
        }

        // 渲染输入组件
        const renderInput = () => {
            const { type, modelValue, attr, data } = props
            const commonProps = {
                modelValue,
                ...attr,
                ...data,
                'onUpdate:modelValue': onInput,
            }

            // 根据类型渲染不同的组件
            switch (type) {
                case 'string':
                case 'password':
                case 'number':
                case 'textarea':
                    return createVNode(resolveComponent('el-input'), {
                        type: type === 'password' ? 'password' : type === 'number' ? 'number' : 'text',
                        ...commonProps,
                        ...(type === 'textarea' ? { type: 'textarea', rows: attr.rows || 3 } : {}),
                    })

                case 'radio':
                case 'checkbox':
                    return createVNode(resolveComponent(type === 'radio' ? 'el-radio-group' : 'el-checkbox-group'), commonProps, () => {
                        if (isArray(data.content)) {
                            return data.content.map((item: any) => {
                                return createVNode(
                                    resolveComponent(type === 'radio' ? 'el-radio' : 'el-checkbox'),
                                    {
                                        label: item.value,
                                        ...(attr.childrenAttr || {}),
                                    },
                                    () => item.label
                                )
                            })
                        } else if (isString(data.content)) {
                            return createVNode(resolveComponent(type === 'radio' ? 'el-radio' : 'el-checkbox'), {
                                label: data.content,
                                ...(attr.childrenAttr || {}),
                            })
                        }
                        return []
                    })

                case 'switch':
                    return createVNode(resolveComponent('el-switch'), commonProps)

                case 'array':
                    return createVNode(Array, commonProps)

                case 'datetime':
                case 'date':
                case 'time':
                case 'year':
                    return createVNode(resolveComponent('el-date-picker'), {
                        type: type === 'datetime' ? 'datetime' : type,
                        ...commonProps,
                    })

                case 'select':
                case 'selects':
                    return createVNode(
                        resolveComponent('el-select'),
                        {
                            ...commonProps,
                            multiple: type === 'selects',
                        },
                        () => {
                            if (isArray(data.content)) {
                                return data.content.map((item: any) => {
                                    return createVNode(resolveComponent('el-option'), {
                                        key: item.value,
                                        label: item.label,
                                        value: item.value,
                                        disabled: item.disabled || false,
                                    })
                                })
                            } else if (isString(data.content)) {
                                return createVNode(resolveComponent('el-option'), {
                                    label: data.content,
                                    value: data.content,
                                })
                            }
                            return []
                        }
                    )

                case 'remoteSelect':
                case 'remoteSelects':
                    return createVNode(RemoteSelect, {
                        ...commonProps,
                        multiple: type === 'remoteSelects',
                    })

                case 'editor':
                    return createVNode(Editor, commonProps)

                case 'city':
                    return createVNode(resolveComponent('el-cascader'), {
                        ...commonProps,
                        options: state.areaData,
                        props: {
                            value: 'code',
                            label: 'name',
                            children: 'children',
                        },
                    })

                case 'image':
                case 'images':
                case 'file':
                case 'files':
                    return createVNode(BaUpload, {
                        ...commonProps,
                        multiple: type === 'images' || type === 'files',
                        accept: type === 'image' || type === 'images' ? 'image/*' : '*',
                    })

                case 'icon':
                    return createVNode(IconSelector, commonProps)

                case 'color':
                    return createVNode(resolveComponent('el-color-picker'), commonProps)

                default:
                    return createVNode('div', { class: 'ba-input-not-support' }, `不支持的输入框类型: ${type}`)
            }
        }

        return () => renderInput()
    },
})
</script>

<style scoped lang="scss">
.ba-input-not-support {
    color: var(--el-color-danger);
    padding: 10px;
    border: 1px dashed var(--el-color-danger);
    border-radius: 4px;
    text-align: center;
}
</style>
