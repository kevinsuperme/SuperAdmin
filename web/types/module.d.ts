/// <reference types="vite/client" />

declare module '*.vue' {
    import { DefineComponent } from 'vue'
    const component: DefineComponent<Record<string, any>, Record<string, any>, any>
    export default component
}

declare module 'vue' {
    export interface CSSProperties {
        [key: string]: string | number
    }

    export interface RenderFunction {
        (ctx: any): any
    }

    export const render: RenderFunction
}

declare module 'vue-i18n' {
    export * from 'vue-i18n/dist/vue-i18n'
}

declare module 'element-plus' {
    export * from 'element-plus'

    // Element Plus 上传组件类型声明
    export interface UploadProps {
        action?: string
        headers?: { [key: string]: any }
        data?: { [key: string]: any }
        multiple?: boolean
        name?: string
        withCredentials?: boolean
        showFileList?: boolean
        drag?: boolean
        accept?: string
        autoUpload?: boolean
        limit?: number
        fileSize?: number
        fileList?: UploadFile[]
        disabled?: boolean
        [key: string]: any
    }

    export interface UploadFile {
        name?: string
        url?: string
        status?: 'ready' | 'uploading' | 'success' | 'fail'
        size?: number
        percentage?: number
        uid?: number
        raw?: UploadRawFile
        response?: any
        [key: string]: any
    }

    export type UploadFiles = UploadFile[]

    export interface UploadRawFile extends File {
        uid?: number
    }

    export interface UploadUserFile {
        name?: string
        url?: string
        status?: 'ready' | 'uploading' | 'success' | 'fail'
        size?: number
        percentage?: number
        uid?: number
        raw?: UploadRawFile
        response?: any
        [key: string]: any
    }

    export interface UploadFileExt extends UploadUserFile {
        serverUrl?: string
    }

    // Element Plus 表格列类型声明
    export interface TableColumn {
        prop?: string
        type?: string
        label?: string
        width?: string | number
        minWidth?: string | number
        fixed?: boolean | string
        sortable?: boolean | string
        sortMethod?: (a: any, b: any) => number
        sortBy?: string | string[] | ((row: any) => string)
        resizable?: boolean
        formatter?: (row: any, column: any, cellValue: any, index: number) => string
        showOverflowTooltip?: boolean
        align?: 'left' | 'center' | 'right'
        headerAlign?: 'left' | 'center' | 'right'
        className?: string
        labelClassName?: string
        selectable?: (row: any, index: number) => boolean
        reserveSelection?: boolean
        filters?: { text: string; value: any }[]
        filterPlacement?: string
        filterMultiple?: boolean
        filterMethod?: (value: any, row: any, column: any) => boolean
        filteredValue?: any[]
        [key: string]: any
    }

    // Element Plus 表单项类型声明
    export interface FormItemProps {
        label?: string
        labelWidth?: string | number
        required?: boolean
        error?: string
        showMessage?: boolean
        inlineMessage?: boolean
        size?: 'large' | 'default' | 'small'
        for?: string
        prop?: string
        rules?: any
        [key: string]: any
    }

    // Element Plus 表单项属性类型声明
    export const formItemProps: FormItemProps

    // Element Plus 工具提示类型声明
    export interface ElTooltipProps {
        content?: string
        placement?: string
        effect?: 'dark' | 'light'
        disabled?: boolean
        offset?: number
        transition?: string
        visibleArrow?: boolean
        popperClass?: string
        popperOptions?: any
        enterable?: boolean
        showAfter?: number
        hideAfter?: number
        [key: string]: any
    }
}
