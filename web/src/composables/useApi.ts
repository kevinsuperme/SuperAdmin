import { ref, reactive, computed } from 'vue'
import { createAxios } from '/@/utils/axios'
import { handleApiError, showSuccess } from './useError'
import { withLoading, useFormSubmit, useLocalLoading } from './useLoading'

/**
 * API请求状态
 */
export interface ApiState<T = any> {
    data: T | null
    loading: boolean
    error: any
    success: boolean
}

/**
 * API请求选项
 */
export interface ApiOptions {
    immediate?: boolean
    loadingKey?: string
    globalLoading?: boolean
    loadingText?: string
    successMessage?: string
    errorMessage?: string
    onSuccess?: (data: any) => void
    onError?: (error: any) => void
}

/**
 * API请求composable
 * @param url 请求URL
 * @param options 请求选项
 * @returns API状态和方法
 */
export function useApi<T = any>(url: string, options: ApiOptions = {}) {
    const { immediate = false, loadingKey, globalLoading = false, loadingText, successMessage, errorMessage, onSuccess, onError } = options

    // 状态管理
    const state = reactive<ApiState<T>>({
        data: null,
        loading: false,
        error: null,
        success: false,
    })

    // 局部加载状态管理
    const { setLoading, getLoading } = useLocalLoading()

    // 重置状态
    const reset = () => {
        state.data = null
        state.error = null
        state.success = false
    }

    // 通用请求处理
    const request = async (method: 'get' | 'post' | 'put' | 'delete', data?: any, customUrl?: string, params?: any) => {
        reset()
        state.loading = true

        if (loadingKey) {
            setLoading(loadingKey, true)
        }

        try {
            let response

            switch (method) {
                case 'get':
                    response = await createAxios({ url: customUrl || url, method: 'get', params })
                    break
                case 'post':
                    response = await createAxios({ url: customUrl || url, method: 'post', data })
                    break
                case 'put':
                    response = await createAxios({ url: customUrl || url, method: 'put', data })
                    break
                case 'delete':
                    response = await createAxios({ url: customUrl || url, method: 'delete' })
                    break
            }

            state.data = response.data
            state.success = true

            if (successMessage) {
                showSuccess(successMessage)
            }

            if (onSuccess) {
                onSuccess(response.data)
            }

            return response.data
        } catch (error) {
            state.error = error

            if (onError) {
                onError(error)
            } else {
                handleApiError(error, errorMessage)
            }

            throw error
        } finally {
            state.loading = false

            if (loadingKey) {
                setLoading(loadingKey, false)
            }
        }
    }

    // GET请求
    const get = async (params?: any, customUrl?: string) => {
        return request('get', undefined, customUrl, params)
    }

    // POST请求
    const post = async (data?: any, customUrl?: string) => {
        return request('post', data, customUrl)
    }

    // PUT请求
    const put = async (data?: any, customUrl?: string) => {
        return request('put', data, customUrl)
    }

    // DELETE请求
    const del = async (customUrl?: string) => {
        return request('delete', undefined, customUrl)
    }

    // 如果需要立即执行
    if (immediate) {
        get()
    }

    return {
        // 状态
        state: computed(() => state),
        data: computed(() => state.data),
        loading: computed(() => state.loading || (loadingKey ? getLoading(loadingKey) : false)),
        error: computed(() => state.error),
        success: computed(() => state.success),

        // 方法
        get,
        post,
        put,
        delete: del,
        reset,
    }
}

/**
 * 表单提交API composable
 * @param url 提交URL
 * @param options 提交选项
 * @returns 表单提交状态和方法
 */
export function useFormApi<T = any>(url: string, options: ApiOptions = {}) {
    const {
        loadingKey,
        globalLoading = true,
        loadingText = 'Submitting...',
        successMessage = 'Submitted successfully',
        errorMessage,
        onSuccess,
        onError,
    } = options

    // 状态管理
    const state = reactive<ApiState<T>>({
        data: null,
        loading: false,
        error: null,
        success: false,
    })

    // 局部加载状态管理
    const { setLoading, getLoading } = useLocalLoading()

    // 重置状态
    const reset = () => {
        state.data = null
        state.error = null
        state.success = false
    }

    // 通用请求处理
    const request = async (method: 'post' | 'put' | 'patch', data?: any, customUrl?: string) => {
        reset()
        state.loading = true

        if (loadingKey) {
            setLoading(loadingKey, true)
        }

        try {
            let response

            switch (method) {
                case 'post':
                    response = await createAxios({ url: customUrl || url, method: 'post', data })
                    break
                case 'put':
                    response = await createAxios({ url: customUrl || url, method: 'put', data })
                    break
                case 'patch':
                    response = await createAxios({ url: customUrl || url, method: 'patch', data })
                    break
            }

            state.data = response.data
            state.success = true

            if (successMessage) {
                showSuccess(successMessage)
            }

            if (onSuccess) {
                onSuccess(response.data)
            }

            return response.data
        } catch (error) {
            state.error = error

            if (onError) {
                onError(error)
            } else {
                handleApiError(error, errorMessage)
            }

            throw error
        } finally {
            state.loading = false

            if (loadingKey) {
                setLoading(loadingKey, false)
            }
        }
    }

    // 表单提交
    const submit = async (data?: any, method: 'post' | 'put' | 'patch' = 'post', customUrl?: string) => {
        return request(method, data, customUrl)
    }

    return {
        // 状态
        state: computed(() => state),
        data: computed(() => state.data),
        loading: computed(() => state.loading || (loadingKey ? getLoading(loadingKey) : false)),
        error: computed(() => state.error),
        success: computed(() => state.success),

        // 方法
        submit,
        reset,
    }
}

/**
 * 文件上传API composable
 * @param url 上传URL
 * @param options 上传选项
 * @returns 上传状态和方法
 */
export function useUploadApi(url: string, options: ApiOptions = {}) {
    const {
        loadingKey,
        globalLoading = true,
        loadingText = 'Uploading...',
        successMessage = 'Uploaded successfully',
        errorMessage,
        onSuccess,
        onError,
    } = options

    // 状态管理
    const state = reactive<ApiState & { progress: number }>({
        data: null,
        loading: false,
        error: null,
        success: false,
        progress: 0,
    })

    // 局部加载状态管理
    const { setLoading, getLoading } = useLocalLoading()

    // 重置状态
    const reset = () => {
        state.data = null
        state.error = null
        state.success = false
        state.progress = 0
    }

    // 文件上传
    const upload = async (file: File, formData?: Record<string, any>, customUrl?: string) => {
        reset()
        state.loading = true

        if (loadingKey) {
            setLoading(loadingKey, true)
        }

        try {
            const data = new FormData()
            data.append('file', file)

            if (formData) {
                Object.keys(formData).forEach((key) => {
                    data.append(key, formData[key])
                })
            }

            const response = await createAxios({
                url: customUrl || url,
                method: 'post',
                data,
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
                onUploadProgress: (progressEvent: any) => {
                    if (progressEvent.total) {
                        state.progress = Math.round((progressEvent.loaded * 100) / progressEvent.total)
                    }
                },
            })

            state.data = response.data
            state.success = true
            state.progress = 100

            if (successMessage) {
                showSuccess(successMessage)
            }

            if (onSuccess) {
                onSuccess(response.data)
            }

            return response.data
        } catch (error) {
            state.error = error

            if (onError) {
                onError(error)
            } else {
                handleApiError(error, errorMessage)
            }

            throw error
        } finally {
            state.loading = false

            if (loadingKey) {
                setLoading(loadingKey, false)
            }
        }
    }

    return {
        // 状态
        state: computed(() => state),
        data: computed(() => state.data),
        loading: computed(() => state.loading || (loadingKey ? getLoading(loadingKey) : false)),
        error: computed(() => state.error),
        success: computed(() => state.success),
        progress: computed(() => state.progress),

        // 方法
        upload,
        reset,
    }
}
