import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

// 获取当前文件的目录路径
const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)

// 需要检查的目录
const srcDir = path.join(__dirname, 'src')

// 读取文件内容
function readFileContent(filePath) {
    try {
        return fs.readFileSync(filePath, 'utf8')
    } catch (err) {
        console.error(`读取文件失败: ${filePath}`, err)
        return ''
    }
}

// 获取所有需要检查的文件
function getAllFiles(dir, fileList = []) {
    const files = fs.readdirSync(dir)

    files.forEach((file) => {
        const filePath = path.join(dir, file)
        const stat = fs.statSync(filePath)

        if (stat.isDirectory()) {
            getAllFiles(filePath, fileList)
        } else if (file.endsWith('.vue') || file.endsWith('.ts') || file.endsWith('.js')) {
            fileList.push(filePath)
        }
    })

    return fileList
}

// 检查单个文件的未使用导入和变量
function checkFile(filePath) {
    const content = readFileContent(filePath)
    const lines = content.split('\n')

    // 查找所有导入语句
    const imports = []
    const importRegex = /^import\s+(?:\{([^}]+)\}|([^{}\s]+))\s+from\s+['"]([^'"]+)['"]/

    lines.forEach((line, index) => {
        const match = line.match(importRegex)
        if (match) {
            if (match[1]) {
                // 命名导入 { a, b as c }
                const namedImports = match[1].split(',').map((s) => s.trim())
                namedImports.forEach((imp) => {
                    const parts = imp.split(' as ')
                    const name = parts[1] ? parts[1].trim() : parts[0].trim()
                    imports.push({
                        name,
                        line: index + 1,
                        type: 'named',
                        source: match[3],
                    })
                })
            } else if (match[2]) {
                // 默认导入
                imports.push({
                    name: match[2].trim(),
                    line: index + 1,
                    type: 'default',
                    source: match[3],
                })
            }
        }
    })

    // 查找所有变量声明
    const variables = []
    const varRegex = /^(?:const|let|var)\s+([^=;]+)/

    lines.forEach((line, index) => {
        const match = line.match(varRegex)
        if (match) {
            // 处理解构赋值
            if (match[1].includes('{')) {
                const destructured = match[1].match(/\{([^}]+)\}/)
                if (destructured) {
                    const props = destructured[1].split(',').map((s) => s.trim())
                    props.forEach((prop) => {
                        const parts = prop.split(':')
                        const name = parts[0].trim()
                        variables.push({
                            name,
                            line: index + 1,
                            type: 'destructured',
                        })
                    })
                }
            } else {
                // 普通变量
                const varNames = match[1].split(',').map((s) => s.trim())
                varNames.forEach((varName) => {
                    variables.push({
                        name: varName,
                        line: index + 1,
                        type: 'normal',
                    })
                })
            }
        }
    })

    // 检查导入是否被使用
    const unusedImports = []
    imports.forEach((imp) => {
        // 创建正则表达式来查找使用
        const usageRegex = new RegExp(`\\b${imp.name}\\b`, 'g')
        const matches = content.match(usageRegex)

        // 计算使用次数，排除导入语句本身
        let count = 0
        if (matches) {
            lines.forEach((line, index) => {
                if (line.match(importRegex)) return // 跳过导入行
                const lineMatches = line.match(usageRegex)
                if (lineMatches) count += lineMatches.length
            })
        }

        if (count === 0) {
            unusedImports.push(imp)
        }
    })

    // 检查变量是否被使用
    const unusedVariables = []
    variables.forEach((variable) => {
        // 创建正则表达式来查找使用
        const usageRegex = new RegExp(`\\b${variable.name}\\b`, 'g')
        const matches = content.match(usageRegex)

        // 计算使用次数，排除声明行
        let count = 0
        if (matches) {
            lines.forEach((line, index) => {
                if (line.match(varRegex)) return // 跳过声明行
                const lineMatches = line.match(usageRegex)
                if (lineMatches) count += lineMatches.length
            })
        }

        if (count === 0) {
            unusedVariables.push(variable)
        }
    })

    return {
        file: filePath,
        unusedImports,
        unusedVariables,
    }
}

// 主函数
function main() {
    const allFiles = getAllFiles(srcDir)
    const results = []

    allFiles.forEach((file) => {
        const result = checkFile(file)
        if (result.unusedImports.length > 0 || result.unusedVariables.length > 0) {
            results.push(result)
        }
    })

    // 输出结果
    results.forEach((result) => {
        console.log(`\n文件: ${result.file}`)

        if (result.unusedImports.length > 0) {
            console.log('  未使用的导入:')
            result.unusedImports.forEach((imp) => {
                console.log(`    第${imp.line}行: ${imp.name} 来自 ${imp.source}`)
            })
        }

        if (result.unusedVariables.length > 0) {
            console.log('  未使用的变量:')
            result.unusedVariables.forEach((variable) => {
                console.log(`    第${variable.line}行: ${variable.name}`)
            })
        }
    })

    if (results.length === 0) {
        console.log('没有发现未使用的导入或变量')
    } else {
        console.log(`\n共检查了 ${allFiles.length} 个文件，发现 ${results.length} 个文件存在未使用的导入或变量`)
    }
}

main()
