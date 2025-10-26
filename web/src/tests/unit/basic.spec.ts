import { describe, it, expect } from 'vitest'

describe('基础测试示例', () => {
    it('应该正确执行加法运算', () => {
        expect(1 + 1).toBe(2)
    })

    it('应该正确执行字符串拼接', () => {
        expect('Hello' + ' ' + 'World').toBe('Hello World')
    })

    it('应该正确判断数组包含元素', () => {
        const arr = [1, 2, 3, 4, 5]
        expect(arr).toContain(3)
    })
})