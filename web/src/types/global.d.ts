import type { TableColumnCtx } from 'element-plus'

declare global {
    /**
     * 任意对象类型
     * 用于表示键为字符串,值为任意类型的对象
     */
    type anyObj = Record<string, any>

    /**
     * 任意函数类型
     */
    type anyFunction = (...args: any[]) => any

    type Writeable<T> = { -readonly [P in keyof T]: T[P] }

    /**
     * API 响应数据结构
     */
    interface ApiResponse<T = any> {
        code: number
        msg: string
        data: T
        time: number
    }

    /**
     * API Promise 包装
     * 简化的 API 响应,直接返回 data 数据
     */
    type ApiPromise<T = any> = Promise<ApiResponse<T>>

    /**
     * 分页数据结构
     */
    interface PageData<T = any> {
        list: T[]
        total: number
        per_page: number
        current_page: number
        last_page: number
    }

    /**
     * 表格列配置
     */
    interface TableColumn extends Record<string, any> {
        prop?: string
        label?: string
        type?: string
        width?: string | number
        minWidth?: string | number
        align?: string
        headerAlign?: string
        sortable?: boolean | 'custom' | string
        fixed?: boolean | 'left' | 'right' | string
        default?: any
        render?: string
        renderFormatter?: (row: TableRow, field: TableColumn, cellValue: any, column: TableColumnCtx<TableRow>, index: number) => any
        comSearchRender?: string
        buttons?: OptButton[]
        operator?: OperatorStr | false
        show?: boolean
        [key: string]: any
    }

    /**
     * 表单项配置
     */
    interface FormItem {
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
    interface RouteMeta {
        title?: string
        icon?: string
        hidden?: boolean
        keepAlive?: boolean
        affix?: boolean
        badge?: string | number
        [key: string]: any
    }

    /**
     * 表格默认返回数据结构
     */
    interface TableDefaultData {
        list: TableRow[]
        total: number
        remark: string | null
        filter: anyObj
    }

    /**
     * Window 对象扩展
     */
    interface Window {
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

        /**
         * 页面加载状态标识
         */
        existLoading?: boolean

        /**
         * 语言加载处理器
         */
        loadLangHandle?: any

        /**
         * 服务器推送事件源
         */
        eventSource?: EventSource

        /**
         * 延迟执行的定时器ID
         */
        lazy?: number

        /**
         * 全局唯一ID计数器
         */
        unique?: number
    }

    /**
     * 环境变量声明扩展
     */
    interface ImportMetaEnv {
        readonly VITE_AXIOS_BASE_URL: string
        readonly VITE_PORT: string
        readonly VITE_OPEN: string
        readonly VITE_BASE_PATH: string
        readonly VITE_OUT_DIR: string
        readonly VITE_AXIOS_TIMEOUT: string
    }
}

/**
 * Volar 组件实例扩展
 */
declare module '@vue/runtime-core' {
    interface ComponentCustomProperties {
        $t: (key: string, ...args: any[]) => string
        $router: import('vue-router').Router
        $route: import('vue-router').RouteLocationNormalizedLoaded
    }
}

export {}
