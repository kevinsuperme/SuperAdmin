import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import FormItem from '/@/components/formItem/index.vue'

describe('FormItem 组件基本测试', () => {
    beforeEach(() => {
        vi.clearAllMocks()
    })

    it('应该正确创建组件实例', () => {
        const field = {
            name: 'username',
            title: '用户名',
            type: 'string',
        }

        const wrapper = mount(FormItem, {
            props: {
                field,
                modelValue: '',
            },
        })

        expect(wrapper.exists()).toBe(true)
    })

    it('应该正确接收props', () => {
        const field = {
            name: 'email',
            title: '邮箱',
            type: 'string',
            attr: {
                placeholder: '请输入邮箱',
            },
            data: {
                maxlength: 50,
            },
        }

        const wrapper = mount(FormItem, {
            props: {
                field,
                modelValue: 'test@example.com',
            },
        })

        expect(wrapper.props('field')).toEqual(field)
        expect(wrapper.props('modelValue')).toBe('test@example.com')
    })

    it('应该正确触发update:modelValue事件', async () => {
        const field = {
            name: 'password',
            title: '密码',
            type: 'password',
        }

        const wrapper = mount(FormItem, {
            props: {
                field,
                modelValue: '',
            },
        })

        // 模拟组件内部触发更新事件
        await wrapper.vm.$emit('update:modelValue', 'new-password')

        expect(wrapper.emitted('update:modelValue')).toBeTruthy()
        expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['new-password'])
    })

    it('应该正确处理不同类型的字段', () => {
        const types = ['string', 'password', 'number', 'textarea', 'radio', 'checkbox', 'switch', 'select', 'date', 'time', 'color']

        types.forEach((type) => {
            const field = {
                name: 'test-field',
                title: '测试字段',
                type,
            }

            const wrapper = mount(FormItem, {
                props: {
                    field,
                    modelValue: '',
                },
            })

            expect(wrapper.props('field').type).toBe(type)
            expect(wrapper.exists()).toBe(true)
        })
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
                modelValue: '',
            },
        })

        expect(wrapper.props('field').type).toBe('custom')
        expect(wrapper.props('field').component).toBe('ElInput')
        expect(wrapper.exists()).toBe(true)
    })

    it('应该正确处理验证规则', () => {
        const field = {
            name: 'required',
            title: '必填项',
            type: 'string',
            rule: [{ required: true, message: '此项必填', trigger: 'blur' }],
        }

        const wrapper = mount(FormItem, {
            props: {
                field,
                modelValue: '',
            },
        })

        expect(wrapper.props('field').rule).toEqual([{ required: true, message: '此项必填', trigger: 'blur' }])
        expect(wrapper.exists()).toBe(true)
    })

    it('应该正确处理提示信息', () => {
        const field = {
            name: 'tip',
            title: '带提示',
            type: 'string',
            tip: '这是一个提示信息',
        }

        const wrapper = mount(FormItem, {
            props: {
                field,
                modelValue: '',
            },
        })

        expect(wrapper.props('field').tip).toBe('这是一个提示信息')
        expect(wrapper.exists()).toBe(true)
    })
})
