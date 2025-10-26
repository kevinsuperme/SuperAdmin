import type { RouteRecordRaw } from 'vue-router'
import { adminBaseRoutePath } from '/@/router/static/adminBase'

const pageTitle = (name: string): string => {
    return `pagesTitle.${name}`
}

const apiDocRoute: RouteRecordRaw = {
    path: adminBaseRoutePath + '/example/api-doc',
    name: 'adminApiDoc',
    component: () => import('/@/views/example/api-doc.vue'),
    meta: {
        title: pageTitle('apiDoc'),
        breadcrumb: true,
    },
}

export default apiDocRoute