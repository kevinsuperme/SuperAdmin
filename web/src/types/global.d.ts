/**
 * 全局类型定义文件
 * 用于定义项目中常用的通用类型
 */

/**
 * 任意对象类型
 * 用于表示键为字符串,值为任意类型的对象
 */
declare type anyObj = Record<string, any>

/**
 * 任意函数类型
 */
declare type anyFunction = (...args: any[]) => any

/**
 * API 响应基础结构
 */
declare interface ApiResponse<T = any> {
    code: number
    msg: string
    data: T
    time: number
}

/**
 * API Promise 类型
 * 简化的 API 响应,直接返回 data 部分
 */
declare type ApiPromise<T = any> = Promise<ApiResponse<T>>

/**
 * 分页数据结构
 */
declare interface PageData<T = any> {
    list: T[]
    total: number
    per_page: number
    current_page: number
    last_page: number
}

/**
 * 表格列配置
 */
declare interface TableColumn {
    prop: string
    label: string
    width?: string | number
    minWidth?: string | number
    align?: 'left' | 'center' | 'right'
    sortable?: boolean | 'custom'
    fixed?: boolean | 'left' | 'right'
    showOverflowTooltip?: boolean
    [key: string]: any
}

/**
 * 表单项配置
 */
declare interface FormItem {
    prop: string
    label: string
    type?: string
    required?: boolean
    rules?: any[]
    placeholder?: string
    options?: any[]
    [key: string]: any
}

/**
 * 路由元信息
 */
declare interface RouteMeta {
    title?: string
    icon?: string
    hidden?: boolean
    keepAlive?: boolean
    affix?: boolean
    badge?: string | number
    [key: string]: any
}

/**
 * Window 对象扩展
 */
declare interface Window {
    /**
     * 请求队列,用于 token 刷新时暂存请求
     */
    requests: Array<(token: string, type: string) => void>

    /**
     * token 是否正在刷新中
     */
    tokenRefreshing: boolean

    /**
     * Pinia store 实例
     */
    $pinia?: any
}

/**
 * 导入元信息环境变量扩展
 */
interface ImportMetaEnv {
    readonly VITE_AXIOS_BASE_URL: string
    readonly VITE_PORT: string
    readonly VITE_OPEN: string
    readonly VITE_BASE_PATH: string
    readonly VITE_OUT_DIR: string
    readonly VITE_AXIOS_TIMEOUT: string
}

/**
 * Volar 支持
 */
declare module 'vue' {
    export interface ComponentCustomProperties {
        $t: (key: string, ...args: any[]) => string
    }
}
