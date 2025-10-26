# 前端自动化测试指南

本项目使用 Vitest 作为前端自动化测试框架，配合 @vue/test-utils 进行 Vue 组件测试。

## 安装依赖

测试相关依赖已安装在项目中：

- `vitest`: 测试框架
- `@vue/test-utils`: Vue 组件测试工具
- `jsdom`: DOM 环境
- `@vitest/ui`: 可视化测试界面
- `@vitest/coverage-v8`: 代码覆盖率工具

## 测试脚本

在 `package.json` 中已添加以下测试脚本：

- `pnpm test`: 运行所有测试
- `pnpm test:ui`: 启动可视化测试界面
- `pnpm test:coverage`: 生成测试覆盖率报告

## 测试文件结构

测试文件应放置在 `src/tests` 目录下，推荐结构：

```
src/tests/
├── unit/          # 单元测试
│   ├── basic.spec.ts
│   ├── components.spec.ts
│   └── utils.spec.ts
├── integration/   # 集成测试
└── e2e/          # 端到端测试
```

## 编写测试

### 基础测试示例

```typescript
import { describe, it, expect } from 'vitest'

describe('基础测试示例', () => {
    it('应该正确执行加法运算', () => {
        expect(1 + 1).toBe(2)
    })
})
```

### Vue 组件测试示例

```typescript
import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import MyComponent from '@/components/MyComponent.vue'

describe('MyComponent', () => {
    it('应该正确渲染组件', () => {
        const wrapper = mount(MyComponent, {
            props: {
                msg: 'Hello World',
            },
        })

        expect(wrapper.find('h1').text()).toBe('Hello World')
    })
})
```

### 工具函数测试示例

```typescript
import { describe, it, expect } from 'vitest'
import { formatDate } from '@/utils/date'

describe('formatDate', () => {
    it('应该正确格式化日期', () => {
        const date = new Date('2023-12-25')
        expect(formatDate(date)).toBe('2023-12-25')
    })
})
```

## 运行测试

### 命令行模式

```bash
# 运行所有测试
pnpm test

# 运行特定测试文件
pnpm test src/tests/unit/basic.spec.ts

# 监视模式（文件变化自动重新运行测试）
pnpm test --watch

# 生成覆盖率报告
pnpm test:coverage
```

### 可视化界面

```bash
# 启动可视化测试界面
pnpm test:ui
```

## 测试覆盖率

测试覆盖率报告生成在 `coverage` 目录下，打开 `coverage/index.html` 可查看详细报告。

## 最佳实践

1. **测试文件命名**: 使用 `.spec.ts` 或 `.test.ts` 后缀
2. **测试描述**: 使用清晰、描述性的测试名称
3. **测试隔离**: 确保每个测试独立，不依赖其他测试的状态
4. **断言**: 使用合适的断言方法，如 `toBe`、`toEqual`、`toContain` 等
5. **模拟**: 对于外部依赖，使用 `vi.mock()` 进行模拟

## 持续集成

测试已集成到 CI/CD 流程中，每次提交代码都会自动运行测试，确保代码质量。
