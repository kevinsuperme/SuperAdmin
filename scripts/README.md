# 自动化脚本说明 / Automation Scripts

本目录包含用于自动更新项目文档的脚本工具。

---

## 📁 脚本列表

| 脚本 | 用途 | 输出 |
|-----|------|------|
| `analyze-project-structure.js` | 分析项目结构 | `project-structure.json` |
| `detect-tech-stack.js` | 检测技术栈 | `tech-stack.json` |
| `update-readme.js` | 更新 README.md | `README.md`, `readme-update-report.json` |

---

## 🚀 快速开始

### 运行单个脚本

```bash
# 分析项目结构
node scripts/analyze-project-structure.js

# 检测技术栈
node scripts/detect-tech-stack.js

# 更新 README.md
node scripts/update-readme.js
```

### 一键运行全部

```bash
# 依次运行所有脚本
node scripts/analyze-project-structure.js && \
node scripts/detect-tech-stack.js && \
node scripts/update-readme.js
```

---

## 📊 输出文件

所有脚本的输出文件会保存在项目根目录：

- `project-structure.json` - 项目结构分析结果
- `tech-stack.json` - 技术栈检测结果
- `readme-update-report.json` - README 更新报告
- `README.backup.*.md` - README 备份文件

---

## 🤖 自动化执行

本项目配置了 GitHub Actions 工作流，会在以下情况自动运行：

1. **Push 触发**: `package.json` 或 `src/` 目录有变更
2. **定时触发**: 每周一早上 8:00 (UTC 0:00)
3. **手动触发**: GitHub UI 手动运行

详见：`.github/workflows/update-readme.yml`

---

## 📖 详细文档

完整的系统说明请参阅：
[15-README自动更新系统](../docs/15-README自动更新系统__README-Auto-Update-System.md)

---

## 🛠️ 开发说明

### 修改分析逻辑

编辑 `analyze-project-structure.js` 中的 `config` 对象：

```javascript
const config = {
  excludeDirs: ['node_modules', 'dist', ...],  // 排除目录
  excludeFiles: ['.DS_Store', ...],            // 排除文件
}
```

### 修改技术栈检测

编辑 `detect-tech-stack.js` 添加新的技术检测：

```javascript
if (allDeps['新技术包']) {
  techStack.utilities.NewTech = {
    version: allDeps['新技术包'],
    description: '新技术描述',
    category: 'Utility',
  }
}
```

### 修改 README 更新规则

编辑 `update-readme.js` 添加新的章节更新：

```javascript
function updateNewSection(content, data) {
  // 实现新的更新逻辑
  return content.replace(/旧内容/, newContent)
}
```

---

## 🐛 故障排查

### 脚本运行失败

```bash
# 检查 Node.js 版本
node --version  # 需要 v16+

# 重新安装依赖
cd web
npm ci
```

### README 未更新

检查 README 是否包含以下章节标题：

- `### 前端技术栈`
- `## 技术栈`
- `## 项目概述`

---

## 📝 维护日志

| 日期 | 变更 | 作者 |
|-----|------|------|
| 2025-10-26 | 初始版本 | Claude Code |

---

**维护者**: DevOps Team
