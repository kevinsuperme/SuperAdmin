import type { AxiosRequestConfig, Method } from 'axios'
import axios from 'axios'
import { ElLoading, ElNotification, type LoadingOptions } from 'element-plus'
import { refreshToken } from '/@/api/common'
import { i18n } from '/@/lang/index'
import router from '/@/router/index'
import adminBaseRoute from '/@/router/static/adminBase'
import { memberCenterBaseRoutePath } from '/@/router/static/memberCenterBase'
import { useAdminInfo } from '/@/stores/adminInfo'
import { useConfig } from '/@/stores/config'
import { SYSTEM_ZINDEX } from '/@/stores/constant/common'
import { useUserInfo } from '/@/stores/userInfo'
import { isAdminApp } from '/@/utils/common'
import { handleApiError, showSuccess } from '/@/composables/useError'

window.requests = []
window.tokenRefreshing = false
const pendingMap = new Map()
const loadingInstance: LoadingInstance = {
    target: null,
    count: 0,
}

/**
 * 根据运行环境获取基础请求URL
 */
export const getUrl = (): string => {
    const value: string = import.meta.env.VITE_AXIOS_BASE_URL as string
    return value == 'getCurrentDomain' ? window.location.protocol + '//' + window.location.host : value
}

/**
 * 根据运行环境获取基础请求URL的端口
 */
export const getUrlPort = (): string => {
    const url = getUrl()
    return new URL(url).port
}

/**
 * 创建`Axios`
 * 默认开启`reductDataFormat(简洁响应)`,返回类型为`ApiPromise`
 * 关闭`reductDataFormat`,返回类型则为`AxiosPromise`
 */
function createAxios<Data = any, T = ApiPromise<Data>>(axiosConfig: AxiosRequestConfig, options: Options = {}, loading: LoadingOptions = {}): T {
    const config = useConfig()
    const adminInfo = useAdminInfo()
    const userInfo = useUserInfo()

    const Axios = axios.create({
        baseURL: getUrl(),
        timeout: 1000 * 10,
        headers: {
            'think-lang': config.lang.defaultLang,
            server: true,
        },
        responseType: 'json',
    })

    // 自定义后台入口
    if (adminBaseRoute.path != '/admin' && isAdminApp() && /^\/admin\//.test(axiosConfig.url!)) {
        axiosConfig.url = axiosConfig.url!.replace(/^\/admin\//, adminBaseRoute.path + '.php/')
    }

    // 合并默认请求选项
    options = Object.assign(
        {
            cancelDuplicateRequest: true, // 是否开启取消重复请求, 默认为 true
            loading: false, // 是否开启loading层效果, 默认为false
            reductDataFormat: true, // 是否开启简洁的数据结构响应, 默认为true
            showErrorMessage: true, // 是否开启接口错误信息展示,默认为true
            showCodeMessage: true, // 是否开启code不为1时的信息提示, 默认为true
            showSuccessMessage: false, // 是否开启code为1时的信息提示, 默认为false
            anotherToken: '', // 当前请求使用另外的用户token
        },
        options
    )

    // 请求拦截
    Axios.interceptors.request.use(
        (config) => {
            removePending(config)
            options.cancelDuplicateRequest && addPending(config)
            // 创建loading实例
            if (options.loading) {
                loadingInstance.count++
                if (loadingInstance.count === 1) {
                    loadingInstance.target = ElLoading.service(loading)
                }
            }

            // 自动携带token
            if (config.headers) {
                const token = adminInfo.getToken()
                if (token) (config.headers as anyObj).batoken = token
                const userToken = options.anotherToken || userInfo.getToken()
                if (userToken) (config.headers as anyObj)['ba-user-token'] = userToken
            }

            return config
        },
        (error) => {
            return Promise.reject(error)
        }
    )

    // 响应拦截
    Axios.interceptors.response.use(
        (response) => {
            removePending(response.config)
            options.loading && closeLoading(options) // 关闭loading

            if (response.config.responseType == 'json') {
                if (response.data && response.data.code !== 1) {
                    if (response.data.code == 409) {
                        if (!window.tokenRefreshing) {
                            window.tokenRefreshing = true
                            return refreshToken()
                                .then(async (res) => {
                                    if (res.data.type == 'admin-refresh') {
                                        adminInfo.setToken(res.data.token, 'auth')
                                        response.headers.batoken = `${res.data.token}`
                                        window.requests.forEach((cb) => cb(res.data.token, 'admin-refresh'))
                                    } else if (res.data.type == 'user-refresh') {
                                        userInfo.setToken(res.data.token, 'auth')
                                        response.headers['ba-user-token'] = `${res.data.token}`
                                        window.requests.forEach((cb) => cb(res.data.token, 'user-refresh'))
                                    }
                                    window.requests = []
                                    return Axios(response.config)
                                })
                                .catch(async (err) => {
                                    if (isAdminApp()) {
                                        adminInfo.removeToken()
                                        if (router.currentRoute.value.name != 'adminLogin') {
                                            router.push({ name: 'adminLogin' })
                                            return Promise.reject(err)
                                        } else {
                                            response.headers.batoken = ''
                                            window.requests.forEach((cb) => cb('', 'admin-refresh'))
                                            window.requests = []
                                            return Axios(response.config)
                                        }
                                    } else {
                                        userInfo.removeToken()
                                        if (router.currentRoute.value.name != 'userLogin') {
                                            router.push({ name: 'userLogin' })
                                            return Promise.reject(err)
                                        } else {
                                            response.headers['ba-user-token'] = ''
                                            window.requests.forEach((cb) => cb('', 'user-refresh'))
                                            window.requests = []
                                            return Axios(response.config)
                                        }
                                    }
                                })
                                .finally(() => {
                                    window.tokenRefreshing = false
                                })
                        } else {
                            return new Promise((resolve) => {
                                // 用函数形式将 resolve 存入，等待刷新后再执行
                                window.requests.push((token: string, type: string) => {
                                    if (type == 'admin-refresh') {
                                        response.headers.batoken = `${token}`
                                    } else {
                                        response.headers['ba-user-token'] = `${token}`
                                    }
                                    resolve(Axios(response.config))
                                })
                            })
                        }
                    }
                    if (options.showCodeMessage) {
                        ElNotification({
                            type: 'error',
                            message: response.data.msg,
                            zIndex: SYSTEM_ZINDEX,
                        })
                    }
                    // 自动跳转到路由name或path
                    if (response.data.code == 302) {
                        router.push({ path: response.data.data.routePath ?? '', name: response.data.data.routeName ?? '' })
                    }
                    if (response.data.code == 303) {
                        const isAdminAppFlag = isAdminApp()
                        let routerPath = isAdminAppFlag ? adminBaseRoute.path : memberCenterBaseRoutePath

                        // 需要登录，清理 token，转到登录页
                        if (response.data.data.type == 'need login') {
                            if (isAdminAppFlag) {
                                adminInfo.removeToken()
                            } else {
                                userInfo.removeToken()
                            }
                            routerPath += '/login'
                        }
                        router.push({ path: routerPath })
                    }
                    // code不等于1, 页面then内的具体逻辑就不执行了
                    return Promise.reject(response.data)
                } else if (options.showSuccessMessage && response.data && response.data.code == 1) {
                    if (options.showSuccessMessage) {
                        showSuccess(response.data.msg ? response.data.msg : i18n.global.t('axios.Operation successful'))
                    }
                }
            }

            return options.reductDataFormat ? response.data : response
        },
        (error) => {
            error.config && removePending(error.config)
            options.loading && closeLoading(options) // 关闭loading
            options.showErrorMessage && handleApiError(error) // 使用新的错误处理函数
            return Promise.reject(error) // 错误继续返回给到具体页面
        }
    )
    return Axios(axiosConfig) as T
}

export default createAxios

/**
 * 关闭Loading层实例
 */
function closeLoading(options: Options) {
    if (options.loading && loadingInstance.count > 0) loadingInstance.count--
    if (loadingInstance.count === 0) {
        loadingInstance.target.close()
        loadingInstance.target = null
    }
}

/**
 * 储存每个请求的唯一cancel回调, 以此为标识
 */
function addPending(config: AxiosRequestConfig) {
    const pendingKey = getPendingKey(config)
    config.cancelToken =
        config.cancelToken ||
        new axios.CancelToken((cancel) => {
            if (!pendingMap.has(pendingKey)) {
                pendingMap.set(pendingKey, cancel)
            }
        })
}

/**
 * 删除重复的请求
 */
function removePending(config: AxiosRequestConfig) {
    const pendingKey = getPendingKey(config)
    if (pendingMap.has(pendingKey)) {
        const cancelToken = pendingMap.get(pendingKey)
        cancelToken(pendingKey)
        pendingMap.delete(pendingKey)
    }
}

/**
 * 生成每个请求的唯一key
 */
function getPendingKey(config: AxiosRequestConfig) {
    let { data } = config
    const { url, method, params, headers } = config
    if (typeof data === 'string') data = JSON.parse(data) // response里面返回的config.data是个字符串对象
    return [
        url,
        method,
        headers && (headers as anyObj).batoken ? (headers as anyObj).batoken : '',
        headers && (headers as anyObj)['ba-user-token'] ? (headers as anyObj)['ba-user-token'] : '',
        JSON.stringify(params),
        JSON.stringify(data),
    ].join('&')
}

/**
 * 根据请求方法组装请求数据/参数
 */
export function requestPayload(method: Method, data: anyObj) {
    if (method == 'GET') {
        return {
            params: data,
        }
    } else if (method == 'POST') {
        return {
            data: data,
        }
    }
}

interface LoadingInstance {
    target: any
    count: number
}
interface Options {
    // 是否开启取消重复请求, 默认为 true
    cancelDuplicateRequest?: boolean
    // 是否开启loading层效果, 默认为false
    loading?: boolean
    // 是否开启简洁的数据结构响应, 默认为true
    reductDataFormat?: boolean
    // 是否开启接口错误信息展示,默认为true
    showErrorMessage?: boolean
    // 是否开启code不为1时的信息提示, 默认为true
    showCodeMessage?: boolean
    // 是否开启code为1时的信息提示, 默认为false
    showSuccessMessage?: boolean
    // 当前请求使用另外的用户token
    anotherToken?: string
}

/*
 * 感谢掘金@橙某人提供的思路和分享
 * 本axios封装详细解释请参考：https://juejin.cn/post/6968630178163458084?share_token=7831c9e0-bea0-469e-8028-b587e13681a8#heading-27
 */
