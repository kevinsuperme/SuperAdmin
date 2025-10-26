import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import FormItem from '/@/components/formItem/index.vue'

// 模拟BaInput组件
vi.mock('/@/components/baInput/index.vue', () => ({
    default: {
        name: 'BaInput',
        props: ['type', 'modelValue', 'attr', 'data'],
        emits: ['update:modelValue'],
        template: '<div class="ba-input-mock"></div>',
    },
}))

// 模拟Element Plus组件
vi.mock('element-plus', async () => {
    const actual = await vi.importActual('element-plus')
    return {
        ...actual,
        ElFormItem: {
            name: 'ElFormItem',
            props: ['prop', 'label', 'required', 'rules'],
            template: '<div class="el-form-item"><slot></slot></div>',
        },
        ElTooltip: {
            name: 'ElTooltip',
            props: ['content', 'placement'],
            template: '<div class="el-tooltip"><slot></slot></div>',
        },
        ElIcon: {
            name: 'ElIcon',
            template: '<div class="el-icon"><slot></slot></div>',
        },
        ElInput: {
            name: 'ElInput',
            props: ['modelValue', 'type', 'placeholder', 'disabled', 'maxlength', 'rows'],
            emits: ['update:modelValue'],
            template:
                '<input :type="type" :value="modelValue" @input="$emit(\'update:modelValue\', $event.target.value)" :placeholder="placeholder" :disabled="disabled" :maxlength="maxlength" />',
        },
    }
})

// 模拟图标组件
vi.mock('/@/components/icon/index.vue', () => ({
    default: {
        name: 'Icon',
        props: ['name'],
        template: '<div class="icon-mock"></div>',
    },
}))

describe('FormItem 组件测试', () => {
    beforeEach(() => {
        vi.clearAllMocks()
    })

    it('应该正确渲染表单项', () => {
        const field = {
            name: 'username',
            title: '用户名',
            type: 'string',
            attr: {
                placeholder: '请输入用户名',
            },
        }

        const wrapper = mount(FormItem, {
            props: {
                field,
                modelValue: { username: '' },
            },
        })

        const formItem = wrapper.find('.el-form-item')
        expect(formItem.exists()).toBe(true)

        const baInput = wrapper.find('.ba-input-mock')
        expect(baInput.exists()).toBe(true)
    })

    it('应该正确传递属性', () => {
        const field = {
            name: 'email',
            title: '邮箱',
            type: 'string',
            attr: {
                placeholder: '请输入邮箱',
                type: 'email',
            },
        }

        const wrapper = mount(FormItem, {
            props: {
                field,
                modelValue: { email: '' },
            },
        })

        const formItem = wrapper.find('.el-form-item')
        expect(formItem.exists()).toBe(true)
    })

    it('应该正确处理值更新', async () => {
        const field = {
            name: 'password',
            title: '密码',
            type: 'password',
        }

        const wrapper = mount(FormItem, {
            props: {
                field,
                modelValue: { password: '' },
            },
        })

        const baInput = wrapper.findComponent({ name: 'BaInput' })
        await baInput.vm.$emit('update:modelValue', 'new-password')
        await nextTick()

        expect(wrapper.emitted('update:modelValue')).toBeTruthy()
        expect(wrapper.emitted('update:modelValue')?.[0]).toEqual([{ password: 'new-password' }])
    })

    it('应该正确处理自定义渲染组件', () => {
        const field = {
            name: 'custom',
            title: '自定义组件',
            type: 'custom',
            component: 'ElInput',
        }

        const wrapper = mount(FormItem, {
            props: {
                field,
                modelValue: { custom: '' },
            },
        })

        const formItem = wrapper.find('.el-form-item')
        expect(formItem.exists()).toBe(true)
    })

    it('应该正确应用验证规则', () => {
        const field = {
            name: 'required',
            title: '必填项',
            type: 'string',
            rule: [{ required: true, message: '此项必填', trigger: 'blur' }],
        }

        const wrapper = mount(FormItem, {
            props: {
                field,
                modelValue: { required: '' },
            },
        })

        const formItem = wrapper.find('.el-form-item')
        expect(formItem.exists()).toBe(true)
    })

    it('应该正确显示提示信息', () => {
        const field = {
            name: 'tip',
            title: '带提示',
            type: 'string',
            tip: '这是一个提示信息',
        }

        const wrapper = mount(FormItem, {
            props: {
                field,
                modelValue: { tip: '' },
            },
        })

        const tooltip = wrapper.find('.el-tooltip')
        expect(tooltip.exists()).toBe(true)
    })

    it('应该正确处理数组类型', () => {
        const field = {
            name: 'tags',
            title: '标签',
            type: 'array',
        }

        const wrapper = mount(FormItem, {
            props: {
                field,
                modelValue: { tags: [] },
            },
        })

        const formItem = wrapper.find('.el-form-item')
        expect(formItem.exists()).toBe(true)
    })

    it('应该正确处理选择器类型', () => {
        const field = {
            name: 'status',
            title: '状态',
            type: 'select',
            data: {
                content: [
                    { label: '启用', value: '1' },
                    { label: '禁用', value: '0' },
                ],
            },
        }

        const wrapper = mount(FormItem, {
            props: {
                field,
                modelValue: '1',
            },
        })

        const formItem = wrapper.find('.el-form-item')
        expect(formItem.exists()).toBe(true)
    })

    it('应该正确处理日期类型', () => {
        const field = {
            name: 'birthday',
            title: '生日',
            type: 'date',
        }

        const wrapper = mount(FormItem, {
            props: {
                field,
                modelValue: '',
            },
        })

        const formItem = wrapper.find('.el-form-item')
        expect(formItem.exists()).toBe(true)
    })

    it('应该正确处理文本域类型', () => {
        const field = {
            name: 'description',
            title: '描述',
            type: 'textarea',
        }

        const wrapper = mount(FormItem, {
            props: {
                field,
                modelValue: '',
            },
        })

        const formItem = wrapper.find('.el-form-item')
        expect(formItem.exists()).toBe(true)
    })

    it('应该正确处理数字类型', () => {
        const field = {
            name: 'age',
            title: '年龄',
            type: 'number',
        }

        const wrapper = mount(FormItem, {
            props: {
                field,
                modelValue: 0,
            },
        })

        const formItem = wrapper.find('.el-form-item')
        expect(formItem.exists()).toBe(true)
    })

    it('应该正确处理开关类型', () => {
        const field = {
            name: 'enabled',
            title: '启用',
            type: 'switch',
        }

        const wrapper = mount(FormItem, {
            props: {
                field,
                modelValue: false,
            },
        })

        const formItem = wrapper.find('.el-form-item')
        expect(formItem.exists()).toBe(true)
    })
})
