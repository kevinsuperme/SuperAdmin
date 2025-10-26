#!/usr/bin/env node

/**
 * README.md 自动更新脚本
 * 用途: 基于项目实际状态自动更新 README.md
 * 依赖: analyze-project-structure.js, detect-tech-stack.js
 */

const fs = require('fs')
const path = require('path')

// 导入分析脚本
const { generateProjectStats, identifyCoreModules } = require('./analyze-project-structure.js')
const { extractCoreTech, detectConfigFiles } = require('./detect-tech-stack.js')

// 配置
const config = {
  rootDir: path.join(__dirname, '..'),
  readmePath: path.join(__dirname, '../README.md'),
  packageJsonPath: path.join(__dirname, '../web/package.json'),
  backupEnabled: true,
}

/**
 * 读取 README.md
 */
function readReadme() {
  try {
    return fs.readFileSync(config.readmePath, 'utf-8')
  } catch (error) {
    console.error('Error reading README.md:', error.message)
    return null
  }
}

/**
 * 备份 README.md
 */
function backupReadme() {
  if (!config.backupEnabled) return

  const timestamp = new Date().toISOString().replace(/[:.]/g, '-')
  const backupPath = path.join(config.rootDir, `README.backup.${timestamp}.md`)

  try {
    const content = readReadme()
    if (content) {
      fs.writeFileSync(backupPath, content)
      console.log(`📦 已备份 README.md 至: ${backupPath}`)
    }
  } catch (error) {
    console.error('Error backing up README.md:', error.message)
  }
}

/**
 * 读取 package.json
 */
function readPackageJson() {
  try {
    const content = fs.readFileSync(config.packageJsonPath, 'utf-8')
    return JSON.parse(content)
  } catch (error) {
    console.error('Error reading package.json:', error.message)
    return null
  }
}

/**
 * 提取 README 章节
 */
function extractSection(content, sectionTitle) {
  const regex = new RegExp(`(##\\s+${sectionTitle}[\\s\\S]*?)(?=\\n##\\s|$)`, 'i')
  const match = content.match(regex)
  return match ? match[1] : null
}

/**
 * 更新技术栈章节
 */
function updateTechStackSection(content, techStack, packageJson) {
  const version = packageJson.version

  // 生成技术栈表格
  const techStackTable = `
### 前端技术栈

| 技术 | 版本 | 说明 |
|-----|------|------|
| **框架** | Vue ${techStack.framework.Vue?.version || 'N/A'} | 渐进式JavaScript框架 |
| **语言** | TypeScript ${techStack.framework.TypeScript?.version || 'N/A'} | JavaScript超集 |
| **构建工具** | Vite ${techStack.buildTools.Vite?.version || 'N/A'} | 下一代前端构建工具 |
| **UI框架** | Element Plus ${techStack.uiLibrary.ElementPlus?.version || 'N/A'} | Vue 3组件库 |
| **状态管理** | Pinia ${techStack.stateManagement.Pinia?.version || 'N/A'} | Vue状态管理库 |
| **路由管理** | Vue Router ${techStack.router.VueRouter?.version || 'N/A'} | Vue官方路由 |
| **HTTP客户端** | Axios ${techStack.utilities.Axios?.version || 'N/A'} | Promise based HTTP client |
| **图表库** | ECharts ${techStack.utilities.ECharts?.version || 'N/A'} | 数据可视化库 |
| **工具库** | Lodash-ES ${techStack.utilities.Lodash?.version || 'N/A'} | 现代化工具库 |
| **组合式API工具** | VueUse ${techStack.utilities.VueUse?.version || 'N/A'} | Vue组合式函数集合 |
`

  // 替换技术栈章节
  const regex = /### 前端技术栈[\s\S]*?(?=\n### |$)/
  if (regex.test(content)) {
    content = content.replace(regex, techStackTable.trim())
    console.log('✅ 更新技术栈章节')
  } else {
    console.warn('⚠️  未找到技术栈章节')
  }

  return content
}

/**
 * 更新项目概述章节
 */
function updateProjectOverviewSection(content, packageJson, techStack) {
  const vueVersion = techStack.framework.Vue?.version || '3.x'

  const overview = `SuperAdmin 是一个基于 Vue ${vueVersion} + ThinkPHP8 + TypeScript + Vite + Pinia + Element Plus 等流行技术栈的现代化后台管理系统。它支持常驻内存运行、可视化CRUD代码生成、自带WEB终端、自适应多端，同时提供Web、WebNuxt、Server端等多种部署方式。系统内置全局数据回收站和字段级数据修改保护、自动注册路由、无限子级权限管理等特性，无需授权即可免费商用。`

  // 查找并替换项目概述
  const regex = /(SuperAdmin 是一个基于.*?等特性，无需授权即可免费商用。)/s
  if (regex.test(content)) {
    content = content.replace(regex, overview)
    console.log('✅ 更新项目概述')
  }

  return content
}

/**
 * 更新标题版本号
 */
function updateTitleVersion(content, version) {
  const regex = /# SuperAdmin v\d+\.\d+\.\d+/
  if (regex.test(content)) {
    content = content.replace(regex, `# SuperAdmin v${version}`)
    console.log(`✅ 更新标题版本号: v${version}`)
  }

  return content
}

/**
 * 更新技术栈badges
 */
function updateTechBadges(content, techStack) {
  // 更新 Vue badge
  const vueVersion = techStack.framework.Vue?.version || '3.x'
  const vueBadgeRegex = /(<img src="https:\/\/img\.shields\.io\/badge\/Vue-)[^"]+/
  if (vueBadgeRegex.test(content)) {
    content = content.replace(vueBadgeRegex, `$1${vueVersion}-brightgreen?color=91aac3&labelColor=439EFD"`)
    console.log(`✅ 更新 Vue badge: ${vueVersion}`)
  }

  // 更新 TypeScript badge
  const tsVersion = techStack.framework.TypeScript?.version || '5.x'
  const tsBadgeRegex = /(<img src="https:\/\/img\.shields\.io\/badge\/TypeScript-)[^"]+/
  if (tsBadgeRegex.test(content)) {
    const tsMajorVersion = tsVersion.split('.')[0]
    content = content.replace(tsBadgeRegex, `$1%3E${tsMajorVersion}-blue?color=91aac3&labelColor=439EFD"`)
    console.log(`✅ 更新 TypeScript badge: ${tsMajorVersion}.x`)
  }

  // 更新 Vite badge
  const viteVersion = techStack.buildTools.Vite?.version || '6.x'
  const viteBadgeRegex = /(<img src="https:\/\/img\.shields\.io\/badge\/Vite-)[^"]+/
  if (viteBadgeRegex.test(content)) {
    const viteMajorVersion = viteVersion.split('.')[0]
    content = content.replace(viteBadgeRegex, `$1%3E${viteMajorVersion}-blue?color=91aac3&labelColor=439EFD"`)
    console.log(`✅ 更新 Vite badge: ${viteMajorVersion}.x`)
  }

  // 更新 Element Plus badge
  const epVersion = techStack.uiLibrary.ElementPlus?.version || '2.x'
  const epBadgeRegex = /(<img src="https:\/\/img\.shields\.io\/badge\/Element--Plus-)[^"]+/
  if (epBadgeRegex.test(content)) {
    const epMajorVersion = epVersion.split('.')[0]
    content = content.replace(epBadgeRegex, `$1%3E${epMajorVersion}-brightgreen?color=91aac3&labelColor=439EFD"`)
    console.log(`✅ 更新 Element Plus badge: ${epMajorVersion}.x`)
  }

  // 更新 Pinia badge
  const piniaVersion = techStack.stateManagement.Pinia?.version || '2.x'
  const piniaBadgeRegex = /(<img src="https:\/\/img\.shields\.io\/badge\/Pinia-)[^"]+/
  if (piniaBadgeRegex.test(content)) {
    const piniaMajorVersion = piniaVersion.split('.')[0]
    content = content.replace(piniaBadgeRegex, `$1%3E${piniaMajorVersion}-blue?color=91aac3&labelColor=439EFD"`)
    console.log(`✅ 更新 Pinia badge: ${piniaMajorVersion}.x`)
  }

  return content
}

/**
 * 更新目录结构章节
 */
function updateDirectoryStructure(content, projectStats) {
  const modules = projectStats.modules || []

  // 生成目录结构描述
  const structure = `
\`\`\`
web/
├── src/
${modules.map(m => `│   ├── ${m.name}/                # ${m.description} (${m.fileCount} files)`).join('\n')}
├── public/                 # 静态资源
├── tests/                  # 测试文件
└── package.json
\`\`\`
`

  // 查找并替换目录结构
  const regex = /```[\s\S]*?web\/[\s\S]*?```/
  if (regex.test(content)) {
    content = content.replace(regex, structure.trim())
    console.log('✅ 更新目录结构')
  }

  return content
}

/**
 * 生成更新摘要
 */
function generateUpdateSummary() {
  return `\n<!-- 自动更新信息 -->
<!-- 最后更新时间: ${new Date().toISOString()} -->
<!-- 更新工具: scripts/update-readme.js -->\n`
}

/**
 * 主函数
 */
function main() {
  console.log('🚀 开始更新 README.md...\n')

  try {
    // 1. 备份当前 README
    backupReadme()

    // 2. 读取当前 README
    let content = readReadme()
    if (!content) {
      console.error('❌ 无法读取 README.md')
      process.exit(1)
    }

    // 3. 读取 package.json
    const packageJson = readPackageJson()
    if (!packageJson) {
      console.error('❌ 无法读取 package.json')
      process.exit(1)
    }

    // 4. 分析项目结构
    console.log('📊 分析项目结构...')
    const projectStats = generateProjectStats()

    // 5. 检测技术栈
    console.log('🔍 检测技术栈...')
    const techStack = extractCoreTech(packageJson)

    // 6. 更新各个章节
    console.log('\n📝 更新 README 内容...\n')
    content = updateTitleVersion(content, packageJson.version)
    content = updateTechBadges(content, techStack)
    content = updateProjectOverviewSection(content, packageJson, techStack)
    content = updateTechStackSection(content, techStack, packageJson)
    content = updateDirectoryStructure(content, projectStats)

    // 7. 添加更新摘要（注释）
    const updateSummary = generateUpdateSummary()
    if (!content.includes('<!-- 自动更新信息 -->')) {
      content += updateSummary
    }

    // 8. 保存更新后的 README
    fs.writeFileSync(config.readmePath, content)

    console.log('\n✅ README.md 更新完成！')
    console.log(`📄 文件路径: ${config.readmePath}`)
    console.log(`🏷️  项目版本: v${packageJson.version}`)
    console.log(`🔄 更新时间: ${new Date().toLocaleString('zh-CN')}`)

    // 9. 生成更新报告
    const report = {
      timestamp: new Date().toISOString(),
      version: packageJson.version,
      updates: {
        title: true,
        badges: true,
        overview: true,
        techStack: true,
        structure: true,
      },
      techStack: {
        vue: techStack.framework.Vue?.version,
        typescript: techStack.framework.TypeScript?.version,
        vite: techStack.buildTools.Vite?.version,
        elementPlus: techStack.uiLibrary.ElementPlus?.version,
        pinia: techStack.stateManagement.Pinia?.version,
      },
    }

    const reportPath = path.join(config.rootDir, 'readme-update-report.json')
    fs.writeFileSync(reportPath, JSON.stringify(report, null, 2))
    console.log(`\n📊 更新报告已保存: ${reportPath}`)

    return report
  } catch (error) {
    console.error('\n❌ 更新失败:', error.message)
    console.error(error.stack)
    process.exit(1)
  }
}

// 运行主函数
if (require.main === module) {
  main()
}

module.exports = {
  updateTechStackSection,
  updateProjectOverviewSection,
  updateTitleVersion,
  updateTechBadges,
}
