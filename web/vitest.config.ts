import { defineConfig } from 'vitest/config'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

const pathResolve = (dir: string): any => {
    return resolve(__dirname, '.', dir)
}

export default defineConfig({
    plugins: [vue()],
    resolve: {
        alias: {
            '/@': pathResolve('./src/'),
            assets: pathResolve('./src/assets'),
            'vue-i18n': 'vue-i18n/dist/vue-i18n.cjs.js',
        },
    },
    test: {
        globals: true,
        environment: 'jsdom',
        include: ['src/**/*.{test,spec}.{js,mjs,cjs,ts,mts,cts,jsx,tsx}'],
        exclude: ['node_modules', 'dist', 'src/utils/build.ts'],
        coverage: {
            provider: 'v8',
            reporter: ['text', 'json', 'html'],
            exclude: [
                'node_modules/',
                'src/utils/build.ts',
                '**/*.d.ts',
                'src/assets/',
                'src/styles/',
            ],
        },
    },
})