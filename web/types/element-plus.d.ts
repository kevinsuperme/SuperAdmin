import type { ComponentPublicInstance } from 'vue'

declare global {
    type FormItemRule = import('element-plus/es/components/form/src/types').FormItemRule
    type FormRules<T extends Record<string, any> | string = string> = import('element-plus/es/components/form/src/types').FormRules<T>
    type FormInstance = import('element-plus/es/components/form').FormInstance
    type FormItemInstance = import('element-plus/es/components/form').FormItemInstance

    type UploadProps = import('element-plus/es/components/upload').UploadProps
    type UploadInstance = import('element-plus/es/components/upload').UploadInstance
    type UploadUserFile = import('element-plus/es/components/upload/src/upload').UploadUserFile
    type UploadFiles = import('element-plus/es/components/upload/src/upload').UploadFiles
    type UploadRawFile = import('element-plus/es/components/upload/src/upload').UploadRawFile
    type UploadRequestHandler = import('element-plus/es/components/upload/src/upload').UploadRequestHandler

    type ElSelectInstance = InstanceType<(typeof import('element-plus/es'))['ElSelect']>
    type ElUploadInstance = ComponentPublicInstance & UploadInstance
}

export {}
