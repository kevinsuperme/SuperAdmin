# 自动化部署脚本 (Windows PowerShell)

param(
    [Parameter(Mandatory=$true)]
    [string]$Version,
    
    [Parameter(Mandatory=$false)]
    [string]$Environment = "production",
    
    [Parameter(Mandatory=$false)]
    [string]$ProjectName = "fantastic-admin",
    
    [Parameter(Mandatory=$false)]
    [string]$DeployDir = "C:\inetpub\wwwroot\fantastic-admin",
    
    [Parameter(Mandatory=$false)]
    [string]$BackupDir = "C:\backups\fantastic-admin"
)

# 颜色输出函数
function Write-LogInfo {
    param([string]$Message)
    Write-Host "[INFO] $Message" -ForegroundColor Green
}

function Write-LogWarn {
    param([string]$Message)
    Write-Host "[WARN] $Message" -ForegroundColor Yellow
}

function Write-LogError {
    param([string]$Message)
    Write-Host "[ERROR] $Message" -ForegroundColor Red
}

Write-LogInfo "开始部署 $ProjectName 版本 $Version 到 $Environment 环境"

# 创建必要的目录
$ReleaseDir = Join-Path $DeployDir "releases"
$CurrentDir = Join-Path $DeployDir "current"
$SharedDir = Join-Path $DeployDir "shared"

New-Item -ItemType Directory -Force -Path $ReleaseDir | Out-Null
New-Item -ItemType Directory -Force -Path $BackupDir | Out-Null
New-Item -ItemType Directory -Force -Path (Join-Path $SharedDir "storage") | Out-Null
New-Item -ItemType Directory -Force -Path (Join-Path $SharedDir "logs") | Out-Null
New-Item -ItemType Directory -Force -Path (Join-Path $SharedDir "config") | Out-Null

# 检查版本文件是否存在
$VersionFile = "$ProjectName-$Version.zip"
if (-not (Test-Path $VersionFile)) {
    Write-LogError "版本文件 $VersionFile 不存在"
    exit 1
}

# 创建新版本目录
$NewReleaseDir = Join-Path $ReleaseDir $Version
New-Item -ItemType Directory -Force -Path $NewReleaseDir | Out-Null

# 解压版本文件
Write-LogInfo "解压版本文件 $VersionFile"
try {
    Expand-Archive -Path $VersionFile -DestinationPath $NewReleaseDir -Force
} catch {
    Write-LogError "解压文件失败: $_"
    exit 1
}

# 创建共享目录的软链接
Write-LogInfo "创建共享目录软链接"
try {
    # 删除可能存在的目录
    if (Test-Path (Join-Path $NewReleaseDir "runtime")) {
        Remove-Item -Path (Join-Path $NewReleaseDir "runtime") -Recurse -Force
    }
    if (Test-Path (Join-Path $NewReleaseDir "logs")) {
        Remove-Item -Path (Join-Path $NewReleaseDir "logs") -Recurse -Force
    }
    if (Test-Path (Join-Path $NewReleaseDir ".env")) {
        Remove-Item -Path (Join-Path $NewReleaseDir ".env") -Force
    }
    
    # 创建软链接
    cmd /c mklink /D "$(Join-Path $NewReleaseDir 'runtime')" "$(Join-Path $SharedDir 'storage')" | Out-Null
    cmd /c mklink /D "$(Join-Path $NewReleaseDir 'logs')" "$(Join-Path $SharedDir 'logs')" | Out-Null
    cmd /c "$(Join-Path $NewReleaseDir '.env')" "$(Join-Path $SharedDir 'config' '.env')" | Out-Null
} catch {
    Write-LogWarn "创建软链接失败，将复制文件: $_"
    Copy-Item -Path (Join-Path $SharedDir "storage") -Destination (Join-Path $NewReleaseDir "runtime") -Recurse -Force
    Copy-Item -Path (Join-Path $SharedDir "logs") -Destination (Join-Path $NewReleaseDir "logs") -Recurse -Force
    Copy-Item -Path (Join-Path $SharedDir "config" ".env") -Destination (Join-Path $NewReleaseDir ".env") -Force
}

# 安装PHP依赖
Write-LogInfo "安装PHP依赖"
Set-Location $NewReleaseDir
try {
    composer install --no-dev --optimize-autoloader
} catch {
    Write-LogError "安装PHP依赖失败: $_"
    exit 1
}

# 设置文件权限
Write-LogInfo "设置文件权限"
try {
    # Windows下设置IIS应用程序池权限
    $acl = Get-Acl $NewReleaseDir
    $accessRule = New-Object System.Security.AccessControl.FileSystemAccessRule("IIS_IUSRS","FullControl","ContainerInherit,ObjectInherit","None","Allow")
    $acl.SetAccessRule($accessRule)
    Set-Acl $NewReleaseDir $acl
    
    # 设置runtime和public目录权限
    $runtimeDir = Join-Path $NewReleaseDir "runtime"
    $publicDir = Join-Path $NewReleaseDir "public"
    
    $runtimeAcl = Get-Acl $runtimeDir
    $runtimeAcl.SetAccessRule($accessRule)
    Set-Acl $runtimeDir $runtimeAcl
    
    $publicAcl = Get-Acl $publicDir
    $publicAcl.SetAccessRule($accessRule)
    Set-Acl $publicDir $publicAcl
} catch {
    Write-LogWarn "设置文件权限失败: $_"
}

# 运行数据库迁移
Write-LogInfo "运行数据库迁移"
try {
    php think migrate:run
} catch {
    Write-LogError "数据库迁移失败: $_"
    exit 1
}

# 清理缓存
Write-LogInfo "清理缓存"
try {
    php think clear
} catch {
    Write-LogWarn "清理缓存失败: $_"
}

# 备份当前版本（如果存在）
if (Test-Path $CurrentDir) {
    $CurrentVersion = (Get-Item $CurrentDir).Target
    if ($CurrentVersion) {
        $CurrentVersionName = Split-Path $CurrentVersion -Leaf
        $Timestamp = Get-Date -Format "yyyyMMddHHmmss"
        $BackupVersionDir = Join-Path $BackupDir "$CurrentVersionName-$Timestamp"
        
        Write-LogInfo "备份当前版本 $CurrentVersionName"
        Copy-Item -Path $CurrentDir -Destination $BackupVersionDir -Recurse -Force
    }
}

# 切换到新版本
Write-LogInfo "切换到新版本 $Version"
try {
    if (Test-Path $CurrentDir) {
        Remove-Item $CurrentDir -Force
    }
    cmd /c mklink /D "$CurrentDir" "$NewReleaseDir" | Out-Null
} catch {
    Write-LogError "创建当前版本软链接失败: $_"
    exit 1
}

# 重启IIS应用程序池
Write-LogInfo "重启IIS应用程序池"
try {
    $AppPoolName = "DefaultAppPool"
    Import-Module WebAdministration
    Restart-WebAppPool -Name $AppPoolName
} catch {
    Write-LogWarn "重启IIS应用程序池失败，请手动重启: $_"
}

# 健康检查
Write-LogInfo "执行健康检查"
Start-Sleep -Seconds 5
try {
    $response = Invoke-WebRequest -Uri "http://localhost/api/health" -UseBasicParsing -TimeoutSec 10
    if ($response.StatusCode -eq 200) {
        Write-LogInfo "健康检查通过"
    } else {
        throw "HTTP状态码: $($response.StatusCode)"
    }
} catch {
    Write-LogError "健康检查失败: $_"
    # 回滚到上一个版本
    if ($CurrentVersion) {
        Write-LogInfo "回滚到上一个版本"
        try {
            if (Test-Path $CurrentDir) {
                Remove-Item $CurrentDir -Force
            }
            cmd /c mklink /D "$CurrentDir" "$CurrentVersion" | Out-Null
            Write-LogInfo "已回滚到版本 $CurrentVersionName"
        } catch {
            Write-LogError "回滚失败: $_"
        }
    }
    exit 1
}

# 清理旧版本（保留最近5个版本）
Write-LogInfo "清理旧版本"
try {
    $versions = Get-ChildItem -Path $ReleaseDir -Directory | Sort-Object CreationTime -Descending
    if ($versions.Count -gt 5) {
        $versionsToRemove = $versions | Select-Object -Skip 5
        foreach ($version in $versionsToRemove) {
            Write-LogInfo "删除旧版本: $($version.Name)"
            Remove-Item -Path $version.FullName -Recurse -Force
        }
    }
} catch {
    Write-LogWarn "清理旧版本失败: $_"
}

Write-LogInfo "部署完成！当前版本: $Version"