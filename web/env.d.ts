/*
 * @Author: kevinsuperme iphone.com@live.cn
 * @Date: 2025-10-26 16:18:01
 * @LastEditors: kevinsuperme iphone.com@live.cn
 * @LastEditTime: 2025-10-26 17:00:57
 * @FilePath: \super-admin-v2.3.3\web\env.d.ts
 * @Description: 这是默认设置,请设置`customMade`, 打开koroFileHeader查看配置 进行设置: https://github.com/OBKoro1/koro1FileHeader/wiki/%E9%85%8D%E7%BD%AE
 */
/// <reference types="vite/client" />

declare module '*.vue' {
    import type { DefineComponent } from 'vue'
    const component: DefineComponent<Record<string, unknown>, Record<string, unknown>, unknown>
    export default component
}

declare module 'element-plus/dist/locale/zh-cn.mjs'
declare module 'element-plus/dist/locale/en.mjs'

// 确保lodash-es类型被正确识别
declare module 'lodash-es' {
    export const isArray: (value: any) => value is any[]
    export const isEmpty: (value: any) => boolean
    export const isUndefined: (value: any) => value is undefined
    export const isString: (value: any) => value is string
    export const cloneDeep: <T>(value: T) => T
    export const debounce: <T extends (...args: any[]) => any>(func: T, wait?: number, options?: any) => T
    export const trim: (str: string, chars?: string) => string
    export const trimStart: (str: string, chars?: string) => string
    export const trimEnd: (str: string, chars?: string) => string
    export const uniq: <T>(array: T[]) => T[]
    export const compact: <T>(array: (T | null | undefined | false | 0 | '')[]) => T[]
    export const reverse: <T>(array: T[]) => T[]
    export const concat: <T>(...arrays: (T | T[])[]) => T[]
    export const range: (start: number, end?: number, step?: number) => number[]
    export const parseInt: (str: string, radix?: number) => number
    // 可以根据需要添加更多 lodash 函数的类型声明
}

interface ImportMetaEnv {
    readonly VITE_PORT: string
    readonly VITE_OPEN: string
    readonly VITE_BASE_PATH: string
    readonly VITE_OUT_DIR: string
    readonly VITE_AXIOS_TIMEOUT: string
}

interface ImportMeta {
    readonly env: ImportMetaEnv
}
