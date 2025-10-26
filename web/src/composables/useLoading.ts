import { ref, reactive, computed } from 'vue'
import { ElLoading } from 'element-plus'

/**
 * 加载状态管理
 */
export interface LoadingOptions {
    lock?: boolean
    text?: string
    background?: string
    customClass?: string
    target?: string | HTMLElement
}

/**
 * 全局加载状态管理
 */
export function useGlobalLoading() {
    const isLoading = ref(false)
    const loadingInstance = ref<any>(null)
    const loadingText = ref('Loading...')

    /**
     * 显示加载状态
     * @param options 加载选项
     */
    const showLoading = (options: LoadingOptions = {}) => {
        if (isLoading.value) return

        isLoading.value = true
        loadingText.value = options.text || 'Loading...'

        loadingInstance.value = ElLoading.service({
            lock: options.lock !== false,
            text: loadingText.value,
            background: options.background || 'rgba(0, 0, 0, 0.7)',
            customClass: options.customClass || '',
            target: options.target || document.body,
        })
    }

    /**
     * 隐藏加载状态
     */
    const hideLoading = () => {
        if (loadingInstance.value) {
            loadingInstance.value.close()
            loadingInstance.value = null
        }
        isLoading.value = false
    }

    /**
     * 更新加载文本
     * @param text 新的加载文本
     */
    const updateLoadingText = (text: string) => {
        loadingText.value = text
        if (loadingInstance.value && loadingInstance.value.setText) {
            loadingInstance.value.setText(text)
        }
    }

    return {
        isLoading: computed(() => isLoading.value),
        loadingText: computed(() => loadingText.value),
        showLoading,
        hideLoading,
        updateLoadingText,
    }
}

/**
 * 局部加载状态管理
 */
export function useLocalLoading() {
    const loadingStates = reactive<Record<string, boolean>>({})

    /**
     * 设置加载状态
     * @param key 加载状态的键
     * @param state 加载状态
     */
    const setLoading = (key: string, state: boolean) => {
        loadingStates[key] = state
    }

    /**
     * 获取加载状态
     * @param key 加载状态的键
     * @returns 加载状态
     */
    const getLoading = (key: string): boolean => {
        return loadingStates[key] || false
    }

    /**
     * 切换加载状态
     * @param key 加载状态的键
     */
    const toggleLoading = (key: string) => {
        loadingStates[key] = !loadingStates[key]
    }

    /**
     * 重置所有加载状态
     */
    const resetAllLoading = () => {
        Object.keys(loadingStates).forEach((key) => {
            loadingStates[key] = false
        })
    }

    /**
     * 检查是否有任何加载状态为true
     * @returns 是否有加载中的状态
     */
    const hasAnyLoading = computed(() => {
        return Object.values(loadingStates).some((state) => state)
    })

    return {
        loadingStates: computed(() => loadingStates),
        setLoading,
        getLoading,
        toggleLoading,
        resetAllLoading,
        hasAnyLoading,
    }
}

/**
 * 异步操作包装器，自动处理加载状态和错误
 * @param asyncFn 异步函数
 * @param loadingKey 加载状态的键（用于局部加载）
 * @param globalLoading 是否使用全局加载
 * @returns 包装后的异步函数
 */
export function withLoading<T extends (...args: any[]) => Promise<any>>(
    asyncFn: T,
    options: {
        loadingKey?: string
        globalLoading?: boolean
        loadingText?: string
        errorHandler?: (error: any) => void
    } = {}
): T {
    return (async (...args: Parameters<T>) => {
        const { loadingKey, globalLoading = false, loadingText, errorHandler } = options

        try {
            // 显示加载状态
            if (globalLoading) {
                const { showLoading } = useGlobalLoading()
                showLoading({ text: loadingText })
            } else if (loadingKey) {
                const { setLoading } = useLocalLoading()
                setLoading(loadingKey, true)
            }

            // 执行异步操作
            const result = await asyncFn(...args)

            return result
        } catch (error) {
            // 处理错误
            if (errorHandler) {
                errorHandler(error)
            } else {
                console.error('Async operation error:', error)
            }

            throw error
        } finally {
            // 隐藏加载状态
            if (globalLoading) {
                const { hideLoading } = useGlobalLoading()
                hideLoading()
            } else if (loadingKey) {
                const { setLoading } = useLocalLoading()
                setLoading(loadingKey, false)
            }
        }
    }) as T
}

/**
 * 表单提交包装器，自动处理加载状态和错误
 * @param submitFn 提交函数
 * @param options 选项
 * @returns 包装后的提交函数
 */
export function useFormSubmit<T extends (...args: any[]) => Promise<any>>(
    submitFn: T,
    options: {
        loadingText?: string
        successMessage?: string
        errorMessage?: string
        onSuccess?: (result: any) => void
        onError?: (error: any) => void
    } = {}
): T {
    return (async (...args: Parameters<T>) => {
        const { loadingText, successMessage, errorMessage, onSuccess, onError } = options

        try {
            // 显示加载状态
            const { showLoading } = useGlobalLoading()
            showLoading({ text: loadingText || 'Submitting...' })

            // 执行提交操作
            const result = await submitFn(...args)

            // 显示成功消息
            if (successMessage) {
                const { showSuccess } = await import('./useError')
                showSuccess(successMessage)
            }

            // 执行成功回调
            if (onSuccess) {
                onSuccess(result)
            }

            return result
        } catch (error) {
            // 处理错误
            if (onError) {
                onError(error)
            } else {
                const { handleApiError } = await import('./useError')
                handleApiError(error, errorMessage)
            }

            throw error
        } finally {
            // 隐藏加载状态
            const { hideLoading } = useGlobalLoading()
            hideLoading()
        }
    }) as T
}
