#!/usr/bin/env node

/**
 * 项目结构分析脚本
 * 用途: 自动分析项目目录结构、文件统计、模块组织
 * 输出: JSON格式的项目结构数据
 */

const fs = require('fs')
const path = require('path')

// 配置
const config = {
  rootDir: path.join(__dirname, '..'),
  webDir: path.join(__dirname, '../web'),
  excludeDirs: [
    'node_modules',
    'dist',
    'coverage',
    '.git',
    '.vscode',
    '.idea',
    'temp',
    'tmp',
  ],
  excludeFiles: [
    '.DS_Store',
    'Thumbs.db',
    '.gitkeep',
  ],
}

/**
 * 递归统计目录
 */
function analyzeDirectory(dirPath, basePath = '') {
  const stats = {
    totalFiles: 0,
    totalDirs: 0,
    fileTypes: {},
    structure: {},
  }

  try {
    const items = fs.readdirSync(dirPath)

    for (const item of items) {
      const fullPath = path.join(dirPath, item)
      const relativePath = path.join(basePath, item)

      // 跳过排除的文件和目录
      if (config.excludeDirs.includes(item) || config.excludeFiles.includes(item)) {
        continue
      }

      const stat = fs.statSync(fullPath)

      if (stat.isDirectory()) {
        stats.totalDirs++
        const subStats = analyzeDirectory(fullPath, relativePath)
        stats.totalFiles += subStats.totalFiles
        stats.totalDirs += subStats.totalDirs

        // 合并文件类型统计
        Object.keys(subStats.fileTypes).forEach(ext => {
          stats.fileTypes[ext] = (stats.fileTypes[ext] || 0) + subStats.fileTypes[ext]
        })

        stats.structure[item] = subStats.structure
      } else if (stat.isFile()) {
        stats.totalFiles++
        const ext = path.extname(item)
        stats.fileTypes[ext] = (stats.fileTypes[ext] || 0) + 1

        if (!stats.structure.files) {
          stats.structure.files = []
        }
        stats.structure.files.push(item)
      }
    }
  } catch (error) {
    console.error(`Error analyzing directory ${dirPath}:`, error.message)
  }

  return stats
}

/**
 * 分析前端项目结构
 */
function analyzeWebStructure() {
  const webDir = config.webDir

  if (!fs.existsSync(webDir)) {
    console.warn('Web directory not found')
    return null
  }

  const structure = {
    src: {},
    public: {},
    tests: {},
    config: {},
  }

  // 分析 src 目录
  const srcDir = path.join(webDir, 'src')
  if (fs.existsSync(srcDir)) {
    const srcItems = fs.readdirSync(srcDir)
    structure.src = {
      directories: srcItems.filter(item => {
        const fullPath = path.join(srcDir, item)
        return fs.statSync(fullPath).isDirectory()
      }),
      files: srcItems.filter(item => {
        const fullPath = path.join(srcDir, item)
        return fs.statSync(fullPath).isFile()
      }),
    }
  }

  // 分析 public 目录
  const publicDir = path.join(webDir, 'public')
  if (fs.existsSync(publicDir)) {
    structure.public = analyzeDirectory(publicDir)
  }

  // 检测测试目录
  const testDirs = ['tests', 'test', '__tests__', 'spec']
  for (const dir of testDirs) {
    const testDir = path.join(webDir, dir)
    if (fs.existsSync(testDir)) {
      structure.tests[dir] = analyzeDirectory(testDir)
    }
  }

  return structure
}

/**
 * 识别项目核心模块
 */
function identifyCoreModules() {
  const srcDir = path.join(config.webDir, 'src')

  if (!fs.existsSync(srcDir)) {
    return []
  }

  const modules = []
  const items = fs.readdirSync(srcDir)

  const modulePatterns = {
    'views': '页面视图',
    'components': '组件库',
    'layouts': '布局组件',
    'router': '路由管理',
    'store': '状态管理',
    'api': 'API接口',
    'utils': '工具函数',
    'composables': '组合式函数',
    'hooks': 'Hooks',
    'directives': '自定义指令',
    'plugins': '插件',
    'middleware': '中间件',
    'assets': '静态资源',
    'styles': '样式文件',
    'types': '类型定义',
  }

  items.forEach(item => {
    const fullPath = path.join(srcDir, item)
    if (fs.statSync(fullPath).isDirectory()) {
      const moduleInfo = {
        name: item,
        path: `src/${item}`,
        description: modulePatterns[item] || '其他模块',
      }

      // 统计模块文件数
      const stats = analyzeDirectory(fullPath)
      moduleInfo.fileCount = stats.totalFiles
      moduleInfo.fileTypes = stats.fileTypes

      modules.push(moduleInfo)
    }
  })

  return modules
}

/**
 * 生成项目统计信息
 */
function generateProjectStats() {
  const stats = {
    timestamp: new Date().toISOString(),
    projectRoot: config.rootDir,
    directories: {},
    modules: [],
    summary: {},
  }

  // 分析 web 目录
  if (fs.existsSync(config.webDir)) {
    stats.directories.web = analyzeDirectory(config.webDir)
  }

  // 分析 docs 目录
  const docsDir = path.join(config.rootDir, 'docs')
  if (fs.existsSync(docsDir)) {
    stats.directories.docs = analyzeDirectory(docsDir)
  }

  // 识别核心模块
  stats.modules = identifyCoreModules()

  // 生成汇总
  const webStats = stats.directories.web || {}
  stats.summary = {
    totalFiles: webStats.totalFiles || 0,
    totalDirectories: webStats.totalDirs || 0,
    coreModules: stats.modules.length,
    documentFiles: (stats.directories.docs?.totalFiles || 0),
    fileTypes: webStats.fileTypes || {},
  }

  return stats
}

/**
 * 主函数
 */
function main() {
  console.log('🔍 分析项目结构...\n')

  try {
    const projectStats = generateProjectStats()
    const webStructure = analyzeWebStructure()

    const result = {
      generatedAt: projectStats.timestamp,
      projectStats,
      webStructure,
    }

    // 输出 JSON
    const outputPath = path.join(config.rootDir, 'project-structure.json')
    fs.writeFileSync(outputPath, JSON.stringify(result, null, 2))

    console.log('✅ 项目结构分析完成')
    console.log(`📊 总文件数: ${projectStats.summary.totalFiles}`)
    console.log(`📁 总目录数: ${projectStats.summary.totalDirectories}`)
    console.log(`🧩 核心模块: ${projectStats.summary.coreModules} 个`)
    console.log(`📝 文档文件: ${projectStats.summary.documentFiles} 个`)
    console.log(`\n📄 结果已保存至: ${outputPath}`)

    // 输出文件类型统计
    console.log('\n📋 文件类型统计:')
    const fileTypes = projectStats.summary.fileTypes
    Object.keys(fileTypes)
      .sort((a, b) => fileTypes[b] - fileTypes[a])
      .slice(0, 10)
      .forEach(ext => {
        console.log(`  ${ext || '(无扩展名)'}: ${fileTypes[ext]} 个`)
      })

    // 输出核心模块
    console.log('\n🧩 核心模块列表:')
    projectStats.modules
      .sort((a, b) => b.fileCount - a.fileCount)
      .slice(0, 10)
      .forEach(module => {
        console.log(`  ${module.name} (${module.fileCount} 个文件) - ${module.description}`)
      })

    return result
  } catch (error) {
    console.error('❌ 分析失败:', error.message)
    process.exit(1)
  }
}

// 运行主函数
if (require.main === module) {
  main()
}

module.exports = { analyzeDirectory, identifyCoreModules, generateProjectStats }
