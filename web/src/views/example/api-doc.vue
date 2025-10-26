<template>
  <div class="api-doc-container">
    <el-card class="box-card">
      <template #header>
        <div class="card-header">
          <span>API文档</span>
          <div class="header-actions">
            <el-button type="primary" @click="refreshDoc">刷新文档</el-button>
            <el-button type="success" @click="downloadJson">下载JSON</el-button>
            <el-button type="info" @click="downloadYaml">下载YAML</el-button>
            <el-button type="warning" @click="openSwaggerUI">Swagger UI</el-button>
          </div>
        </div>
      </template>
      
      <div class="doc-info">
        <el-descriptions :column="2" border>
          <el-descriptions-item label="文档标题">{{ docInfo.title }}</el-descriptions-item>
          <el-descriptions-item label="版本">{{ docInfo.version }}</el-descriptions-item>
          <el-descriptions-item label="描述" :span="2">{{ docInfo.description }}</el-descriptions-item>
        </el-descriptions>
      </div>
      
      <div class="doc-content" v-loading="loading">
        <iframe
          v-if="showSwaggerUI"
          :src="swaggerUIUrl"
          frameborder="0"
          width="100%"
          height="800px"
        ></iframe>
        
        <div v-else class="api-list">
          <el-collapse v-model="activeNames" accordion>
            <el-collapse-item
              v-for="(tag, index) in apiTags"
              :key="index"
              :title="tag.name + ' (' + tag.paths.length + ')'"
              :name="index"
            >
              <div class="tag-description">{{ tag.description }}</div>
              <el-collapse>
                <el-collapse-item
                  v-for="(path, pathIndex) in tag.paths"
                  :key="pathIndex"
                  :title="path.method + ' ' + path.path"
                  :name="index + '-' + pathIndex"
                >
                  <div class="path-info">
                    <p><strong>描述:</strong> {{ path.summary }}</p>
                    <p><strong>详细说明:</strong> {{ path.description }}</p>
                    
                    <div v-if="path.parameters && path.parameters.length > 0">
                      <h4>参数</h4>
                      <el-table :data="path.parameters" border style="width: 100%">
                        <el-table-column prop="name" label="参数名" width="150"></el-table-column>
                        <el-table-column prop="in" label="位置" width="80"></el-table-column>
                        <el-table-column prop="required" label="必填" width="80">
                          <template #default="scope">
                            <el-tag :type="scope.row.required ? 'danger' : 'info'">
                              {{ scope.row.required ? '是' : '否' }}
                            </el-tag>
                          </template>
                        </el-table-column>
                        <el-table-column prop="type" label="类型" width="100"></el-table-column>
                        <el-table-column prop="description" label="描述"></el-table-column>
                      </el-table>
                    </div>
                    
                    <div v-if="path.responses">
                      <h4>响应</h4>
                      <el-tabs v-model="activeResponseTab">
                        <el-tab-pane
                          v-for="(response, responseCode) in path.responses"
                          :key="responseCode"
                          :label="responseCode + ' - ' + response.description"
                          :name="responseCode"
                        >
                          <pre class="response-example">{{ JSON.stringify(response.example, null, 2) }}</pre>
                        </el-tab-pane>
                      </el-tabs>
                    </div>
                  </div>
                </el-collapse-item>
              </el-collapse>
            </el-collapse-item>
          </el-collapse>
        </div>
      </div>
    </el-card>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { ElMessage } from 'element-plus'
import useApi from '@/api'

const api = useApi()

// 响应式数据
const loading = ref(false)
const showSwaggerUI = ref(false)
const swaggerUIUrl = ref('')
const activeNames = ref([])
const activeResponseTab = ref('200')

const docInfo = reactive({
  title: 'Fantastic Admin API文档',
  version: '2.3.3',
  description: '基于ThinkPHP 6和Vue 3的后台管理系统API接口文档'
})

const apiTags = ref([])

// 获取API文档信息
const getDocInfo = async () => {
  loading.value = true
  try {
    const response = await api.get('/admin/api_doc/index')
    if (response.code === 1) {
      Object.assign(docInfo, response.data)
      swaggerUIUrl.value = response.data.ui
    }
  } catch (error) {
    ElMessage.error('获取API文档信息失败')
  } finally {
    loading.value = false
  }
}

// 获取API文档内容
const getApiDoc = async () => {
  loading.value = true
  try {
    const response = await api.get('/admin/api_doc/json')
    if (response) {
      parseApiDoc(response)
    }
  } catch (error) {
    ElMessage.error('获取API文档内容失败')
  } finally {
    loading.value = false
  }
}

// 解析API文档
const parseApiDoc = (doc) => {
  const tags = []
  
  // 按标签分组
  if (doc.tags) {
    doc.tags.forEach(tag => {
      tags.push({
        name: tag.name,
        description: tag.description,
        paths: []
      })
    })
  }
  
  // 解析路径
  if (doc.paths) {
    Object.keys(doc.paths).forEach(path => {
      Object.keys(doc.paths[path]).forEach(method => {
        const pathItem = doc.paths[path][method]
        if (pathItem.tags && pathItem.tags.length > 0) {
          const tagName = pathItem.tags[0]
          const tagIndex = tags.findIndex(tag => tag.name === tagName)
          
          if (tagIndex !== -1) {
            tags[tagIndex].paths.push({
              path,
              method: method.toUpperCase(),
              summary: pathItem.summary || '',
              description: pathItem.description || '',
              parameters: parseParameters(pathItem.parameters),
              responses: parseResponses(pathItem.responses)
            })
          }
        }
      })
    })
  }
  
  apiTags.value = tags
}

// 解析参数
const parseParameters = (parameters) => {
  if (!parameters) return []
  
  return parameters.map(param => ({
    name: param.name,
    in: param.in,
    required: param.required || false,
    type: param.schema?.type || param.type || 'string',
    description: param.description || ''
  }))
}

// 解析响应
const parseResponses = (responses) => {
  if (!responses) return {}
  
  const parsed = {}
  Object.keys(responses).forEach(code => {
    const response = responses[code]
    parsed[code] = {
      description: response.description || '',
      example: response.content?.['application/json']?.schema?.example || 
               response.content?.['application/json']?.example || 
               { code: 1, msg: 'success', data: {} }
    }
  })
  return parsed
}

// 刷新文档
const refreshDoc = () => {
  getApiDoc()
}

// 下载JSON
const downloadJson = async () => {
  try {
    const response = await api.get('/admin/api_doc/json')
    const blob = new Blob([JSON.stringify(response, null, 2)], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = 'api-doc.json'
    link.click()
    URL.revokeObjectURL(url)
  } catch (error) {
    ElMessage.error('下载JSON文档失败')
  }
}

// 下载YAML
const downloadYaml = async () => {
  try {
    const response = await api.get('/admin/api_doc/yaml')
    const blob = new Blob([response], { type: 'text/yaml' })
    const url = URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = 'api-doc.yaml'
    link.click()
    URL.revokeObjectURL(url)
  } catch (error) {
    ElMessage.error('下载YAML文档失败')
  }
}

// 打开Swagger UI
const openSwaggerUI = () => {
  showSwaggerUI.value = true
}

// 组件挂载时获取数据
onMounted(() => {
  getDocInfo()
  getApiDoc()
})
</script>

<style scoped>
.api-doc-container {
  padding: 20px;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-actions {
  display: flex;
  gap: 10px;
}

.doc-info {
  margin-bottom: 20px;
}

.doc-content {
  margin-top: 20px;
}

.tag-description {
  margin-bottom: 10px;
  color: #666;
}

.path-info {
  padding: 10px 0;
}

.path-info h4 {
  margin: 15px 0 10px;
  color: #333;
}

.path-info p {
  margin: 5px 0;
}

.response-example {
  background-color: #f5f5f5;
  padding: 15px;
  border-radius: 4px;
  overflow-x: auto;
  font-family: 'Courier New', Courier, monospace;
  font-size: 14px;
}
</style>