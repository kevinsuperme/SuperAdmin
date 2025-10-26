// Vue I18n 类型补充
import { Composer, I18n, I18nOptions } from 'vue-i18n'

declare module 'vue-i18n' {
    export interface Composer<Messages = Record<string, any>, DateTimeFormats = Record<string, any>, NumberFormats = Record<string, any>> {
        t(key: string, params?: Record<string, any>): string
        t(key: string, list: unknown[]): string
        t(key: string, named: Record<string, unknown>): string
        tc(key: string, choice?: number, values?: Record<string, any>): string
        te(key: string): boolean
        d(value: number | Date, key?: string): string
        n(value: number, key?: string): string
        tm(key: string): any
        rt(message: string): string
        getLocaleMessage(locale: string): Messages
        setLocaleMessage(locale: string, message: Messages): void
        mergeLocaleMessage(locale: string, message: Messages): void
        locale: {
            value: string
        }
        availableLocales: string[]
        fallbackLocale: {
            value: string | string[]
        }
        messages: {
            value: Messages
        }
    }

    export function useI18n<Messages = Record<string, any>>(): Composer<Messages>
    export function createI18n<Messages = Record<string, any>>(options: I18nOptions): I18n<Messages>
}

declare module '@vue/runtime-core' {
    export interface ComponentCustomProperties {
        $t: (key: string, params?: Record<string, any>) => string
        $tc: (key: string, choice?: number, values?: Record<string, any>) => string
        $te: (key: string) => boolean
        $d: (value: number | Date, key?: string) => string
        $n: (value: number, key?: string) => string
        $tm: (key: string) => any
        $rt: (message: string) => string
        $i18n: Composer
    }
}

export {}
