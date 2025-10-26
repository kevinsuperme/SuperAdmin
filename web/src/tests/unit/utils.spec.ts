import { describe, it, expect } from 'vitest'

// 模拟一个工具函数
const formatDate = (date: Date, format = 'YYYY-MM-DD'): string => {
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    
    return format
        .replace('YYYY', String(year))
        .replace('MM', month)
        .replace('DD', day)
}

describe('工具函数测试', () => {
    it('应该正确格式化日期', () => {
        const date = new Date('2023-12-25')
        expect(formatDate(date)).toBe('2023-12-25')
    })

    it('应该正确处理自定义格式', () => {
        const date = new Date('2023-05-09')
        expect(formatDate(date, 'MM/DD/YYYY')).toBe('05/09/2023')
    })

    it('应该正确处理单月和单日', () => {
        const date = new Date('2023-01-05')
        expect(formatDate(date)).toBe('2023-01-05')
    })
})