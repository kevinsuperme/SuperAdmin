#!/usr/bin/env node

/**
 * 文档链接验证脚本
 * 用途: 验证所有文档中的内部链接是否有效
 * 输出: 无效链接报告
 */

const fs = require('fs')
const path = require('path')

// 配置
const config = {
  rootDir: path.join(__dirname, '..'),
  docsDir: path.join(__dirname, '../docs'),
  documentsToCheck: [
    'README.md',
    'DOCS.md',
    'DOCUMENTATION_INDEX.md',
    'PROJECT_OVERVIEW.md',
    'ARCHITECTURE.md',
  ],
}

/**
 * 提取Markdown文件中的所有链接
 */
function extractLinks(content, filePath) {
  const links = []
  const linkRegex = /\[([^\]]+)\]\(([^)]+)\)/g
  let match

  while ((match = linkRegex.exec(content)) !== null) {
    links.push({
      text: match[1],
      url: match[2],
      filePath: filePath,
    })
  }

  return links
}

/**
 * 检查链接是否有效
 */
function checkLink(link, baseDir) {
  const url = link.url

  // 跳过外部链接
  if (url.startsWith('http://') || url.startsWith('https://')) {
    return { valid: true, type: 'external' }
  }

  // 跳过锚点链接
  if (url.startsWith('#')) {
    return { valid: true, type: 'anchor' }
  }

  // 移除锚点部分
  const urlWithoutAnchor = url.split('#')[0]
  if (!urlWithoutAnchor) {
    return { valid: true, type: 'anchor' }
  }

  // 构建完整路径
  let fullPath
  if (url.startsWith('/')) {
    fullPath = path.join(config.rootDir, urlWithoutAnchor)
  } else {
    fullPath = path.join(path.dirname(link.filePath), urlWithoutAnchor)
  }

  // 规范化路径
  fullPath = path.normalize(fullPath)

  // 检查文件是否存在
  const exists = fs.existsSync(fullPath)

  return {
    valid: exists,
    type: 'internal',
    resolvedPath: fullPath,
  }
}

/**
 * 扫描单个文档
 */
function scanDocument(filePath) {
  try {
    const content = fs.readFileSync(filePath, 'utf-8')
    const links = extractLinks(content, filePath)

    const results = {
      file: filePath,
      totalLinks: links.length,
      validLinks: 0,
      invalidLinks: 0,
      externalLinks: 0,
      anchorLinks: 0,
      brokenLinks: [],
    }

    links.forEach(link => {
      const check = checkLink(link, path.dirname(filePath))

      if (check.type === 'external') {
        results.externalLinks++
        results.validLinks++
      } else if (check.type === 'anchor') {
        results.anchorLinks++
        results.validLinks++
      } else if (check.valid) {
        results.validLinks++
      } else {
        results.invalidLinks++
        results.brokenLinks.push({
          text: link.text,
          url: link.url,
          resolvedPath: check.resolvedPath,
        })
      }
    })

    return results
  } catch (error) {
    console.error(`Error scanning ${filePath}:`, error.message)
    return null
  }
}

/**
 * 扫描所有文档
 */
function scanAllDocuments() {
  const allResults = []

  // 扫描根目录文档
  config.documentsToCheck.forEach(doc => {
    const fullPath = path.join(config.rootDir, doc)
    if (fs.existsSync(fullPath)) {
      const result = scanDocument(fullPath)
      if (result) {
        allResults.push(result)
      }
    } else {
      console.warn(`⚠️  文档不存在: ${doc}`)
    }
  })

  // 扫描 docs 目录所有文档
  if (fs.existsSync(config.docsDir)) {
    const files = fs.readdirSync(config.docsDir)
    files.forEach(file => {
      if (file.endsWith('.md')) {
        const fullPath = path.join(config.docsDir, file)
        const result = scanDocument(fullPath)
        if (result) {
          allResults.push(result)
        }
      }
    })
  }

  return allResults
}

/**
 * 生成报告
 */
function generateReport(results) {
  const summary = {
    totalDocuments: results.length,
    totalLinks: 0,
    validLinks: 0,
    invalidLinks: 0,
    externalLinks: 0,
    anchorLinks: 0,
    documentsWithBrokenLinks: 0,
  }

  results.forEach(result => {
    summary.totalLinks += result.totalLinks
    summary.validLinks += result.validLinks
    summary.invalidLinks += result.invalidLinks
    summary.externalLinks += result.externalLinks
    summary.anchorLinks += result.anchorLinks
    if (result.brokenLinks.length > 0) {
      summary.documentsWithBrokenLinks++
    }
  })

  return summary
}

/**
 * 主函数
 */
function main() {
  console.log('🔍 开始验证文档链接...\n')

  const results = scanAllDocuments()
  const summary = generateReport(results)

  // 输出摘要
  console.log('📊 验证摘要:')
  console.log(`  文档总数: ${summary.totalDocuments}`)
  console.log(`  链接总数: ${summary.totalLinks}`)
  console.log(`  有效链接: ${summary.validLinks} (${((summary.validLinks / summary.totalLinks) * 100).toFixed(1)}%)`)
  console.log(`    - 内部链接: ${summary.validLinks - summary.externalLinks - summary.anchorLinks}`)
  console.log(`    - 外部链接: ${summary.externalLinks}`)
  console.log(`    - 锚点链接: ${summary.anchorLinks}`)
  console.log(`  无效链接: ${summary.invalidLinks}`)
  console.log(`  问题文档: ${summary.documentsWithBrokenLinks}`)

  // 输出详细错误
  if (summary.invalidLinks > 0) {
    console.log('\n❌ 发现无效链接:\n')
    results.forEach(result => {
      if (result.brokenLinks.length > 0) {
        console.log(`📄 ${path.relative(config.rootDir, result.file)}`)
        result.brokenLinks.forEach(link => {
          console.log(`  ❌ [${link.text}](${link.url})`)
          console.log(`     → ${link.resolvedPath}`)
        })
        console.log('')
      }
    })

    // 保存报告
    const reportPath = path.join(config.rootDir, 'doc-links-report.json')
    fs.writeFileSync(reportPath, JSON.stringify({ summary, results }, null, 2))
    console.log(`📄 详细报告已保存: ${reportPath}`)

    process.exit(1)
  } else {
    console.log('\n✅ 所有文档链接验证通过！')
    process.exit(0)
  }
}

// 运行主函数
if (require.main === module) {
  main()
}

module.exports = { extractLinks, checkLink, scanDocument, scanAllDocuments }
