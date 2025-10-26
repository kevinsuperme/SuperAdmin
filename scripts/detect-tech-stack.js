#!/usr/bin/env node

/**
 * 技术栈检测脚本
 * 用途: 自动检测项目使用的技术栈、版本号、依赖关系
 * 输出: JSON格式的技术栈数据
 */

const fs = require('fs')
const path = require('path')

// 配置
const config = {
  rootDir: path.join(__dirname, '..'),
  webDir: path.join(__dirname, '../web'),
  packageJsonPaths: [
    path.join(__dirname, '../web/package.json'),
  ],
}

/**
 * 读取 package.json
 */
function readPackageJson(filePath) {
  try {
    if (!fs.existsSync(filePath)) {
      return null
    }
    const content = fs.readFileSync(filePath, 'utf-8')
    return JSON.parse(content)
  } catch (error) {
    console.error(`Error reading ${filePath}:`, error.message)
    return null
  }
}

/**
 * 提取核心技术栈
 */
function extractCoreTech(packageJson) {
  if (!packageJson) return null

  const deps = packageJson.dependencies || {}
  const devDeps = packageJson.devDependencies || {}
  const allDeps = { ...deps, ...devDeps }

  const techStack = {
    name: packageJson.name,
    version: packageJson.version,
    license: packageJson.license,
    framework: {},
    buildTools: {},
    uiLibrary: {},
    stateManagement: {},
    router: {},
    utilities: {},
    testing: {},
    development: {},
  }

  // 框架
  if (allDeps['vue']) {
    techStack.framework.Vue = {
      version: allDeps['vue'],
      description: '渐进式JavaScript框架',
      category: 'Frontend Framework',
    }
  }

  if (allDeps['react']) {
    techStack.framework.React = {
      version: allDeps['react'],
      description: 'UI库',
      category: 'Frontend Framework',
    }
  }

  // 构建工具
  if (allDeps['vite']) {
    techStack.buildTools.Vite = {
      version: allDeps['vite'],
      description: '下一代前端构建工具',
      category: 'Build Tool',
    }
  }

  if (allDeps['webpack']) {
    techStack.buildTools.Webpack = {
      version: allDeps['webpack'],
      description: '模块打包工具',
      category: 'Build Tool',
    }
  }

  // TypeScript
  if (allDeps['typescript']) {
    techStack.framework.TypeScript = {
      version: allDeps['typescript'],
      description: 'JavaScript的超集，提供静态类型检查',
      category: 'Language',
    }
  }

  // UI库
  if (allDeps['element-plus']) {
    techStack.uiLibrary.ElementPlus = {
      version: allDeps['element-plus'],
      description: '基于 Vue 3 的组件库',
      category: 'UI Library',
    }
  }

  if (allDeps['ant-design-vue']) {
    techStack.uiLibrary.AntDesignVue = {
      version: allDeps['ant-design-vue'],
      description: 'Ant Design Vue组件库',
      category: 'UI Library',
    }
  }

  // 状态管理
  if (allDeps['pinia']) {
    techStack.stateManagement.Pinia = {
      version: allDeps['pinia'],
      description: 'Vue 的状态管理库',
      category: 'State Management',
    }
  }

  if (allDeps['vuex']) {
    techStack.stateManagement.Vuex = {
      version: allDeps['vuex'],
      description: 'Vue 的状态管理模式',
      category: 'State Management',
    }
  }

  // 路由
  if (allDeps['vue-router']) {
    techStack.router.VueRouter = {
      version: allDeps['vue-router'],
      description: 'Vue 的官方路由管理器',
      category: 'Router',
    }
  }

  // 工具库
  if (allDeps['axios']) {
    techStack.utilities.Axios = {
      version: allDeps['axios'],
      description: 'HTTP 客户端',
      category: 'HTTP',
    }
  }

  if (allDeps['lodash'] || allDeps['lodash-es']) {
    techStack.utilities.Lodash = {
      version: allDeps['lodash'] || allDeps['lodash-es'],
      description: 'JavaScript 工具库',
      category: 'Utility',
    }
  }

  if (allDeps['echarts']) {
    techStack.utilities.ECharts = {
      version: allDeps['echarts'],
      description: '数据可视化库',
      category: 'Visualization',
    }
  }

  if (allDeps['@vueuse/core']) {
    techStack.utilities.VueUse = {
      version: allDeps['@vueuse/core'],
      description: 'Vue 组合式 API 工具集',
      category: 'Utility',
    }
  }

  // 测试框架
  if (allDeps['vitest']) {
    techStack.testing.Vitest = {
      version: allDeps['vitest'],
      description: 'Vite 原生测试框架',
      category: 'Testing',
    }
  }

  if (allDeps['jest']) {
    techStack.testing.Jest = {
      version: allDeps['jest'],
      description: 'JavaScript 测试框架',
      category: 'Testing',
    }
  }

  if (allDeps['@vue/test-utils']) {
    techStack.testing.VueTestUtils = {
      version: allDeps['@vue/test-utils'],
      description: 'Vue 组件测试工具',
      category: 'Testing',
    }
  }

  // 开发工具
  if (allDeps['eslint']) {
    techStack.development.ESLint = {
      version: allDeps['eslint'],
      description: '代码质量检查工具',
      category: 'Linting',
    }
  }

  if (allDeps['prettier']) {
    techStack.development.Prettier = {
      version: allDeps['prettier'],
      description: '代码格式化工具',
      category: 'Formatting',
    }
  }

  if (allDeps['sass'] || allDeps['scss']) {
    techStack.development.Sass = {
      version: allDeps['sass'] || allDeps['scss'],
      description: 'CSS 预处理器',
      category: 'CSS',
    }
  }

  return techStack
}

/**
 * 检测项目配置文件
 */
function detectConfigFiles() {
  const configFiles = []

  const patterns = [
    { file: 'vite.config.js', type: 'Vite配置' },
    { file: 'vite.config.ts', type: 'Vite配置' },
    { file: 'tsconfig.json', type: 'TypeScript配置' },
    { file: 'eslint.config.js', type: 'ESLint配置' },
    { file: '.eslintrc.js', type: 'ESLint配置' },
    { file: '.prettierrc.js', type: 'Prettier配置' },
    { file: 'vitest.config.js', type: 'Vitest配置' },
    { file: 'vitest.config.ts', type: 'Vitest配置' },
  ]

  patterns.forEach(({ file, type }) => {
    const filePath = path.join(config.webDir, file)
    if (fs.existsSync(filePath)) {
      configFiles.push({ file, type, path: filePath })
    }
  })

  return configFiles
}

/**
 * 生成技术栈摘要
 */
function generateTechSummary(techStack) {
  const summary = {
    frontend: [],
    backend: [],
    buildTools: [],
    testing: [],
    development: [],
  }

  // 前端技术
  Object.keys(techStack.framework).forEach(key => {
    summary.frontend.push({
      name: key,
      version: techStack.framework[key].version,
      description: techStack.framework[key].description,
    })
  })

  Object.keys(techStack.uiLibrary).forEach(key => {
    summary.frontend.push({
      name: key,
      version: techStack.uiLibrary[key].version,
      description: techStack.uiLibrary[key].description,
    })
  })

  Object.keys(techStack.stateManagement).forEach(key => {
    summary.frontend.push({
      name: key,
      version: techStack.stateManagement[key].version,
      description: techStack.stateManagement[key].description,
    })
  })

  Object.keys(techStack.router).forEach(key => {
    summary.frontend.push({
      name: key,
      version: techStack.router[key].version,
      description: techStack.router[key].description,
    })
  })

  // 构建工具
  Object.keys(techStack.buildTools).forEach(key => {
    summary.buildTools.push({
      name: key,
      version: techStack.buildTools[key].version,
      description: techStack.buildTools[key].description,
    })
  })

  // 测试工具
  Object.keys(techStack.testing).forEach(key => {
    summary.testing.push({
      name: key,
      version: techStack.testing[key].version,
      description: techStack.testing[key].description,
    })
  })

  // 开发工具
  Object.keys(techStack.development).forEach(key => {
    summary.development.push({
      name: key,
      version: techStack.development[key].version,
      description: techStack.development[key].description,
    })
  })

  return summary
}

/**
 * 主函数
 */
function main() {
  console.log('🔍 检测技术栈...\n')

  try {
    const results = []

    // 读取所有 package.json
    config.packageJsonPaths.forEach(pkgPath => {
      const packageJson = readPackageJson(pkgPath)
      if (packageJson) {
        const techStack = extractCoreTech(packageJson)
        results.push({
          source: pkgPath,
          projectName: packageJson.name,
          projectVersion: packageJson.version,
          techStack,
        })
      }
    })

    // 检测配置文件
    const configFiles = detectConfigFiles()

    // 生成摘要
    const summary = results.length > 0 ? generateTechSummary(results[0].techStack) : {}

    const output = {
      generatedAt: new Date().toISOString(),
      projectVersion: results[0]?.projectVersion || 'unknown',
      results,
      configFiles,
      summary,
    }

    // 保存结果
    const outputPath = path.join(config.rootDir, 'tech-stack.json')
    fs.writeFileSync(outputPath, JSON.stringify(output, null, 2))

    console.log('✅ 技术栈检测完成')
    console.log(`📦 项目: ${results[0]?.projectName || 'unknown'}`)
    console.log(`🏷️  版本: ${results[0]?.projectVersion || 'unknown'}`)
    console.log(`\n📄 结果已保存至: ${outputPath}`)

    // 输出摘要
    console.log('\n🎯 核心技术栈:')
    if (summary.frontend && summary.frontend.length > 0) {
      console.log('\n  前端技术:')
      summary.frontend.forEach(tech => {
        console.log(`    - ${tech.name} ${tech.version}: ${tech.description}`)
      })
    }

    if (summary.buildTools && summary.buildTools.length > 0) {
      console.log('\n  构建工具:')
      summary.buildTools.forEach(tech => {
        console.log(`    - ${tech.name} ${tech.version}: ${tech.description}`)
      })
    }

    if (summary.testing && summary.testing.length > 0) {
      console.log('\n  测试框架:')
      summary.testing.forEach(tech => {
        console.log(`    - ${tech.name} ${tech.version}: ${tech.description}`)
      })
    }

    console.log(`\n⚙️  配置文件: ${configFiles.length} 个`)
    configFiles.forEach(cf => {
      console.log(`    - ${cf.file} (${cf.type})`)
    })

    return output
  } catch (error) {
    console.error('❌ 检测失败:', error.message)
    process.exit(1)
  }
}

// 运行主函数
if (require.main === module) {
  main()
}

module.exports = { extractCoreTech, detectConfigFiles, generateTechSummary }
