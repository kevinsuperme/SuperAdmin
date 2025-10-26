// Element Plus 类型补充
import type { ComponentPublicInstance, VNode } from 'vue'

declare module 'element-plus' {
    // 表单验证规则类型
    export type FormItemRule = {
        type?:
            | 'string'
            | 'number'
            | 'boolean'
            | 'method'
            | 'regexp'
            | 'integer'
            | 'float'
            | 'array'
            | 'object'
            | 'enum'
            | 'date'
            | 'url'
            | 'hex'
            | 'email'
        required?: boolean
        message?: string | ((rule: any, value: any, callback: any) => void)
        trigger?: string | string[]
        min?: number
        max?: number
        len?: number
        enum?: any[]
        pattern?: RegExp
        validator?: (rule: any, value: any, callback: (error?: Error) => void) => void
        asyncValidator?: (rule: any, value: any, callback: (error?: Error) => void) => Promise<void>
        transform?: (value: any) => any
        whitespace?: boolean
        fields?: Record<string, FormItemRule | FormItemRule[]>
        defaultField?: FormItemRule | FormItemRule[]
        [key: string]: any
    }

    export type FormRules = Record<string, FormItemRule | FormItemRule[]>

    // FormInstance 类型
    export interface FormInstance {
        validate: (callback?: (valid: boolean, fields?: Record<string, any>) => void) => Promise<boolean>
        validateField: (props: string | string[], callback?: (valid: boolean, invalidFields?: Record<string, any>) => void) => Promise<boolean>
        resetFields: () => void
        clearValidate: (props?: string | string[]) => void
        scrollToField: (prop: string) => void
        fields: any[]
    }

    // FormItemInstance 类型
    export interface FormItemInstance {
        validate: (trigger: string, callback?: (valid: boolean) => void) => Promise<boolean>
        resetField: () => void
        clearValidate: () => void
        prop?: string
        labelWidth?: string | number
    }

    // UploadProps 类型
    export interface UploadProps {
        action?: string
        headers?: Record<string, any>
        method?: string
        multiple?: boolean
        data?: Record<string, any>
        name?: string
        withCredentials?: boolean
        showFileList?: boolean
        drag?: boolean
        accept?: string
        onPreview?: (file: any) => void
        onRemove?: (file: any, fileList: any[]) => void
        onSuccess?: (response: any, file: any, fileList: any[]) => void
        onError?: (error: any, file: any, fileList: any[]) => void
        onProgress?: (event: any, file: any, fileList: any[]) => void
        onChange?: (file: any, fileList: any[]) => void
        onExceed?: (files: any[], fileList: any[]) => void
        beforeUpload?: (file: any) => boolean | Promise<any>
        beforeRemove?: (file: any, fileList: any[]) => boolean | Promise<any>
        fileList?: any[]
        listType?: 'text' | 'picture' | 'picture-card'
        autoUpload?: boolean
        disabled?: boolean
        limit?: number
        httpRequest?: (options: any) => void
    }

    // Upload 实例类型
    export interface UploadInstance {
        abort: () => void
        submit: () => void
        clearFiles: () => void
        handleStart: (file: any) => void
        handleProgress: (event: any, file: any) => void
        handleSuccess: (response: any, file: any) => void
        handleError: (error: any, file: any) => void
        handleRemove: (file: any) => void
        $el: HTMLElement
    }

    // ElSelect 实例类型
    export interface ElSelectInstance {
        focus: () => void
        blur: () => void
        handleClear: () => void
        handleClose: () => void
        toggleMenu: () => void
        visible: boolean
        selected: any
        selectedLabel: string
        query: string
        $el: HTMLElement
    }

    export type ElSelect = ComponentPublicInstance & ElSelectInstance
    export type ElUpload = ComponentPublicInstance & UploadInstance

    // ElMessageBox 配置类型
    export interface MessageBoxOptions {
        title?: string
        message?: string | VNode
        type?: 'success' | 'warning' | 'info' | 'error'
        iconClass?: string
        customClass?: string
        callback?: (action: string, instance: any) => void
        showClose?: boolean
        beforeClose?: (action: string, instance: any, done: () => void) => void
        distinguishCancelAndClose?: boolean
        lockScroll?: boolean
        showCancelButton?: boolean
        showConfirmButton?: boolean
        cancelButtonText?: string
        confirmButtonText?: string
        cancelButtonClass?: string
        confirmButtonClass?: string
        closeOnClickModal?: boolean
        closeOnPressEscape?: boolean
        closeOnHashChange?: boolean
        showInput?: boolean
        inputPlaceholder?: string
        inputType?: string
        inputValue?: string
        inputPattern?: RegExp
        inputValidator?: (value: string) => boolean | string
        inputErrorMessage?: string
        center?: boolean
        dangerouslyUseHTMLString?: boolean
        roundButton?: boolean
        buttonSize?: 'large' | 'default' | 'small'
        zIndex?: number
    }

    // ElMessageBox 函数类型
    export interface ElMessageBox {
        (options: MessageBoxOptions | string): Promise<any>
        alert: (message: string, title?: string, options?: MessageBoxOptions) => Promise<any>
        confirm: (message: string, title?: string, options?: MessageBoxOptions) => Promise<any>
        prompt: (message: string, title?: string, options?: MessageBoxOptions) => Promise<any>
        close: () => void
    }

    // ElMessage 配置类型
    export interface MessageOptions {
        message: string | VNode
        type?: 'success' | 'warning' | 'info' | 'error'
        duration?: number
        showClose?: boolean
        center?: boolean
        dangerouslyUseHTMLString?: boolean
        offset?: number
        onClose?: () => void
        customClass?: string
        grouping?: boolean
    }

    // ElMessage 函数类型
    export interface ElMessage {
        (options: MessageOptions | string): {
            close: () => void
        }
        success: (message: string | MessageOptions) => void
        warning: (message: string | MessageOptions) => void
        info: (message: string | MessageOptions) => void
        error: (message: string | MessageOptions) => void
        closeAll: () => void
    }

    // ElNotification 配置类型
    export interface NotificationOptions {
        title?: string
        message: string | VNode
        type?: 'success' | 'warning' | 'info' | 'error'
        duration?: number
        showClose?: boolean
        position?: 'top-right' | 'top-left' | 'bottom-right' | 'bottom-left'
        offset?: number
        onClose?: () => void
        onClick?: () => void
        customClass?: string
        dangerouslyUseHTMLString?: boolean
        zIndex?: number
    }

    // ElNotification 函数类型
    export interface ElNotification {
        (options: NotificationOptions | string): {
            close: () => void
        }
        success: (message: string | NotificationOptions) => void
        warning: (message: string | NotificationOptions) => void
        info: (message: string | NotificationOptions) => void
        error: (message: string | NotificationOptions) => void
        closeAll: () => void
    }

    // 工具类型
    export type Writeable<T> = {
        -readonly [P in keyof T]: T[P]
    }

    // 工具函数
    export function genFileId(): string

    // 导出常用组件的实例类型
    export type ElFormInstance = ComponentPublicInstance & FormInstance
    export type ElFormItemInstance = ComponentPublicInstance & FormItemInstance

    // 导出全局函数
    export const ElMessageBox: ElMessageBox
    export const ElMessage: ElMessage
    export const ElNotification: ElNotification
}

// 确保类型在全局可用
declare global {
    type FormItemRule = import('element-plus').FormItemRule
    type FormRules = import('element-plus').FormRules
    type FormInstance = import('element-plus').FormInstance
    type FormItemInstance = import('element-plus').FormItemInstance
    type ElSelectInstance = import('element-plus').ElSelectInstance
    type UploadProps = import('element-plus').UploadProps
    type Writeable<T> = import('element-plus').Writeable<T>
}

export {}
