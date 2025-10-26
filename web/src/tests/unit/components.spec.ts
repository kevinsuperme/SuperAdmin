import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import { ElButton } from 'element-plus'

describe('Element Plus 组件测试', () => {
    it('应该正确渲染ElButton组件', () => {
        const wrapper = mount(ElButton, {
            props: {
                type: 'primary',
            },
            slots: {
                default: '测试按钮',
            },
        })

        expect(wrapper.find('button').exists()).toBe(true)
        expect(wrapper.find('button').text()).toBe('测试按钮')
        expect(wrapper.find('button').classes()).toContain('el-button--primary')
    })

    it('应该正确响应点击事件', async () => {
        const wrapper = mount(ElButton, {
            props: {
                type: 'primary',
            },
            slots: {
                default: '点击我',
            },
        })

        await wrapper.find('button').trigger('click')
        
        // 验证组件是否正确响应点击事件
        expect(wrapper.emitted()).toHaveProperty('click')
    })
})