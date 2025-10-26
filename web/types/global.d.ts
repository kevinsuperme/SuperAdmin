// 全局类型声明
import type { Ref, ComputedRef } from 'vue'

// Vue 相关类型补充
declare module '@vue/reactivity' {
    export interface WritableComputedRef<T> extends Ref<T> {
        readonly value: T
    }
}

// 确保 WritableComputedRef 在全局可用
declare global {
    type WritableComputedRef<T> = import('vue').WritableComputedRef<T>
}

// 导出空对象以使此文件成为模块
export {}
