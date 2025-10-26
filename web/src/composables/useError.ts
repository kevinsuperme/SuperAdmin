import { ElNotification, ElMessageBox } from 'element-plus'
import { i18n } from '/@/lang/index'

/**
 * 全局错误处理
 */
export interface ErrorInfo {
    code?: number | string
    message: string
    type?: 'error' | 'warning' | 'info'
    duration?: number
    showConfirm?: boolean
    confirmText?: string
    cancelText?: string
}

/**
 * 处理API错误
 * @param error 错误对象
 * @param customMessage 自定义错误消息
 */
export function handleApiError(error: any, customMessage?: string): void {
    console.error('API Error:', error)

    let message = customMessage || i18n.global.t('utils.Request failed')
    let code = 'UNKNOWN_ERROR'

    // 处理axios错误
    if (error.response) {
        // 服务器返回了错误状态码
        code = error.response.status
        switch (error.response.status) {
            case 400:
                message = error.response.data?.msg || i18n.global.t('utils.Incorrect parameter')
                break
            case 401:
                message = i18n.global.t('utils.You are not logged in or have expired')
                break
            case 403:
                message = i18n.global.t('utils.You do not have permission to operate')
                break
            case 404:
                message = i18n.global.t('utils.The requested resource does not exist')
                break
            case 422:
                message = error.response.data?.msg || i18n.global.t('utils.Data validation failed')
                break
            case 429:
                message = i18n.global.t('utils.Request too frequent, please try again later')
                break
            case 500:
                message = i18n.global.t('utils.Server internal error')
                break
            case 502:
                message = i18n.global.t('utils.Gateway error')
                break
            case 503:
                message = i18n.global.t('utils.Service unavailable')
                break
            case 504:
                message = i18n.global.t('utils.The service is temporarily unavailable Please try again later')
                break
            default:
                message = error.response.data?.msg || `${i18n.global.t('utils.Request failed')}: ${error.response.status}`
        }
    } else if (error.request) {
        // 请求已发出，但没有收到响应
        message = i18n.global.t('utils.Network error, please check your network connection')
        code = 'NETWORK_ERROR'
    } else if (error.code === 'ECONNABORTED' || error.message.includes('timeout')) {
        // 请求超时
        message = i18n.global.t('utils.Request timed out')
        code = 'TIMEOUT'
    } else if (error.message) {
        // 其他错误
        message = error.message
        code = 'CLIENT_ERROR'
    }

    // 显示错误通知
    ElNotification({
        type: 'error',
        message,
        duration: 4500,
    })
}

/**
 * 处理业务错误
 * @param code 错误码
 * @param message 错误消息
 * @param options 额外选项
 */
export function handleBusinessError(code: number | string, message: string, options: Partial<ErrorInfo> = {}): void {
    const errorInfo: ErrorInfo = {
        message,
        code,
        type: options.type || 'error',
        duration: options.duration || 4500,
        showConfirm: options.showConfirm || false,
        confirmText: options.confirmText,
        cancelText: options.cancelText,
    }

    if (errorInfo.showConfirm) {
        ElMessageBox.confirm(errorInfo.message, i18n.global.t('utils.Error'), {
            confirmButtonText: errorInfo.confirmText || i18n.global.t('utils.Confirm'),
            cancelButtonText: errorInfo.cancelText || i18n.global.t('utils.Cancel'),
            type: 'error',
        }).catch(() => {
            // 用户点击取消，不做任何操作
        })
    } else {
        ElNotification({
            type: errorInfo.type || 'error',
            message: errorInfo.message,
            duration: errorInfo.duration,
        })
    }
}

/**
 * 显示成功消息
 * @param message 消息内容
 * @param options 额外选项
 */
export function showSuccess(message: string, options: Partial<ErrorInfo> = {}): void {
    ElNotification({
        type: 'success',
        message,
        duration: options.duration || 3000,
    })
}

/**
 * 显示警告消息
 * @param message 消息内容
 * @param options 额外选项
 */
export function showWarning(message: string, options: Partial<ErrorInfo> = {}): void {
    ElNotification({
        type: 'warning',
        message,
        duration: options.duration || 4500,
    })
}

/**
 * 显示信息消息
 * @param message 消息内容
 * @param options 额外选项
 */
export function showInfo(message: string, options: Partial<ErrorInfo> = {}): void {
    ElNotification({
        type: 'info',
        message,
        duration: options.duration || 3000,
    })
}

/**
 * 确认对话框
 * @param message 确认消息
 * @param title 对话框标题
 * @param options 额外选项
 * @returns Promise<boolean> 用户是否确认
 */
export function showConfirm(
    message: string,
    title = i18n.global.t('utils.Confirm'),
    options: {
        confirmText?: string
        cancelText?: string
        type?: 'warning' | 'info' | 'success' | 'error'
    } = {}
): Promise<boolean> {
    return ElMessageBox.confirm(message, title, {
        confirmButtonText: options.confirmText || i18n.global.t('utils.Confirm'),
        cancelButtonText: options.cancelText || i18n.global.t('utils.Cancel'),
        type: options.type || 'warning',
    })
        .then(() => true)
        .catch(() => false)
}

/**
 * 全局错误处理函数
 * @param error 错误对象
 */
export function globalErrorHandler(error: any): void {
    console.error('Global Error Handler:', error)

    if (error.response) {
        // API错误
        handleApiError(error)
    } else if (error.message) {
        // 其他错误
        ElNotification({
            type: 'error',
            message: error.message,
            duration: 4500,
        })
    } else {
        // 未知错误
        ElNotification({
            type: 'error',
            message: i18n.global.t('utils.Unknown error'),
            duration: 4500,
        })
    }
}
