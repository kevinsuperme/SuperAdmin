/// <reference types="vite/client" />

declare module '*.vue' {
    import type { DefineComponent } from 'vue'
    const component: DefineComponent<Record<string, unknown>, Record<string, unknown>, any>
    export default component
}

declare module '@vue/runtime-dom' {
    interface CSSProperties {
        [key: string]: string | number
    }
}

declare module 'vue-i18n' {
    export * from 'vue-i18n/dist/vue-i18n'
}

export {}
