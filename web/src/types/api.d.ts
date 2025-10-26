/**
 * API 接口相关类型定义
 */

/**
 * 用户登录请求参数
 */
export interface LoginParams {
    username: string
    password: string
    captcha?: string
    keep?: boolean
}

/**
 * 用户登录响应数据
 */
export interface LoginResponse {
    token: string
    refresh_token: string
    user: UserInfo
}

/**
 * 用户信息
 */
export interface UserInfo {
    id: number
    username: string
    nickname: string
    email: string
    mobile: string
    avatar: string
    gender: number
    birthday: string
    money: number
    score: number
    last_login_time: string
    last_login_ip: string
    join_time: string
    motto: string
}

/**
 * 管理员信息
 */
export interface AdminInfo {
    id: number
    username: string
    nickname: string
    avatar: string
    last_login_time: string
    token: string
    refresh_token: string
    super: boolean
}

/**
 * Token 刷新响应
 */
export interface RefreshTokenResponse {
    type: 'admin-refresh' | 'user-refresh'
    token: string
}

/**
 * 文件上传响应
 */
export interface UploadResponse {
    id: number
    name: string
    url: string
    size: number
    mimetype: string
    ext: string
    storage: string
}

/**
 * 列表查询参数
 */
export interface ListQueryParams {
    page?: number
    limit?: number
    order?: string
    sort?: 'asc' | 'desc'
    filter?: anyObj
    [key: string]: any
}

/**
 * CRUD 操作通用响应
 */
export interface CrudResponse<T = any> {
    row?: T
    rows?: T[]
}

/**
 * 菜单数据结构
 */
export interface MenuData {
    id: number
    pid: number
    name: string
    title: string
    path: string
    icon: string
    component: string
    keepalive: number
    menu_type: 'tab' | 'link' | 'iframe'
    extend: string
    remark: string
    type: 'menu_dir' | 'menu' | 'button'
    children?: MenuData[]
}

/**
 * 权限节点数据
 */
export interface AuthNode {
    path: string
    title: string
    children?: AuthNode[]
}

/**
 * 系统配置数据
 */
export interface SystemConfig {
    siteName: string
    version: string
    cdnUrl: string
    apiUrl: string
    upload: {
        mode: string
        [key: string]: any
    }
    recordNumber?: string
    cdnUrlParams: string
}
