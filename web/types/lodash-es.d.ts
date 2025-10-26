// lodash-es 类型声明
declare module 'lodash-es' {
    export function isArray(value: any): value is any[]
    export function isEmpty(value: any): boolean
    export function isUndefined(value: any): value is undefined
    // 可以根据需要添加更多 lodash 函数的类型声明
}
