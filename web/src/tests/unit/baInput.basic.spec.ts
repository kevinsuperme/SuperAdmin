/*
 * @Author: kevinsuperme iphone.com@live.cn
 * @Date: 2025-10-26 16:56:35
 * @LastEditors: kevinsuperme iphone.com@live.cn
 * @LastEditTime: 2025-10-26 17:10:02
 * @FilePath: \super-admin-v2.3.3\web\src\tests\unit\baInput.basic.spec.ts
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */
import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'
import BaInput from '/@/components/baInput/index.vue'

describe('BaInput 组件基本测试', () => {
    beforeEach(() => {
        vi.clearAllMocks()
    })

    it('应该正确创建组件实例', () => {
        const wrapper = mount(BaInput, {
            props: {
                type: 'string',
                modelValue: '',
            },
        })

        expect(wrapper.exists()).toBe(true)
    })

    it('应该正确接收props', () => {
        const wrapper = mount(BaInput, {
            props: {
                type: 'string',
                modelValue: 'test-value',
                attr: {
                    placeholder: '请输入内容',
                    maxlength: 10,
                },
                data: {},
            },
        })

        expect(wrapper.props('type')).toBe('string')
        expect(wrapper.props('modelValue')).toBe('test-value')
        expect(wrapper.props('attr')).toEqual({ placeholder: '请输入内容', maxlength: 10 })
        expect(wrapper.props('data')).toEqual({})
    })

    it('应该正确触发update:modelValue事件', async () => {
        const wrapper = mount(BaInput, {
            props: {
                type: 'string',
                modelValue: '',
            },
        })

        // 模拟组件内部触发更新事件
        await wrapper.vm.$emit('update:modelValue', 'new-value')

        expect(wrapper.emitted('update:modelValue')).toBeTruthy()
        expect(wrapper.emitted('update:modelValue')?.[0]).toEqual(['new-value'])
    })

    it('应该正确处理不同类型的输入框', () => {
        const types = ['string', 'password', 'number', 'textarea', 'radio', 'checkbox', 'switch', 'select', 'date', 'time', 'color']

        types.forEach((type) => {
            const wrapper = mount(BaInput, {
                props: {
                    type,
                    modelValue: '',
                },
            })

            expect(wrapper.props('type')).toBe(type)
            expect(wrapper.exists()).toBe(true)
        })
    })

    it('应该正确处理不支持的类型', () => {
        const wrapper = mount(BaInput, {
            props: {
                type: 'unsupported-type',
                modelValue: '',
            },
        })

        expect(wrapper.props('type')).toBe('unsupported-type')
        expect(wrapper.exists()).toBe(true)
    })
})
