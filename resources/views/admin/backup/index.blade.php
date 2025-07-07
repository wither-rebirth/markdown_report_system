@extends('admin.layout')

@section('title', '备份管理')

@section('content')
<div class="backup-page">
    <div class="page-header">
        <h1>备份管理</h1>
        <div class="header-actions">
            <button onclick="showCreateBackupModal()" class="btn btn-primary">创建备份</button>
            <button onclick="showCleanupModal()" class="btn btn-warning">清理旧备份</button>
        </div>
    </div>

    <!-- 统计信息 -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div class="stat-content">
                <h3>总备份数</h3>
                <div class="stat-number">{{ $stats['total_backups'] }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">💾</div>
            <div class="stat-content">
                <h3>占用空间</h3>
                <div class="stat-number">{{ $stats['total_size'] }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🗃️</div>
            <div class="stat-content">
                <h3>数据库备份</h3>
                <div class="stat-number">{{ $stats['database_backups'] }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📁</div>
            <div class="stat-content">
                <h3>文件备份</h3>
                <div class="stat-number">{{ $stats['file_backups'] }}</div>
            </div>
        </div>
    </div>

    <!-- 最新备份信息 -->
    @if(isset($stats['latest_backup']))
    <div class="latest-backup">
        <h3>最新备份</h3>
        <div class="backup-info">
            <span class="backup-name">{{ $stats['latest_backup']['filename'] }}</span>
            <span class="backup-date">{{ $stats['latest_backup']['created_at']->format('Y-m-d H:i:s') }}</span>
            <span class="backup-size">{{ $stats['latest_backup']['size_formatted'] }}</span>
        </div>
    </div>
    @endif

    <!-- 备份列表 -->
    <div class="backups-section">
        <h3>备份文件列表</h3>
        
        @if($backups->isEmpty())
            <div class="empty-state">
                <p>暂无备份文件</p>
                <button onclick="showCreateBackupModal()" class="btn btn-primary">创建第一个备份</button>
            </div>
        @else
            <div class="backups-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>文件名</th>
                            <th>类型</th>
                            <th>大小</th>
                            <th>创建时间</th>
                            <th>备份年龄</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($backups as $backup)
                            <tr>
                                <td>
                                    <div class="backup-filename">
                                        <span class="filename">{{ $backup['filename'] }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="backup-type backup-type-{{ $backup['type'] }}">
                                        {{ ucfirst($backup['type']) }}
                                    </span>
                                </td>
                                <td>{{ $backup['size_formatted'] }}</td>
                                <td>{{ $backup['created_at']->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    <span class="backup-age">
                                        {{ $backup['age_days'] }} 天前
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.backup.download', $backup['filename']) }}" 
                                           class="btn btn-sm btn-primary" title="下载">
                                            📥
                                        </a>
                                        <button data-filename="{{ $backup['filename'] }}" 
                                                onclick="deleteBackup(this.dataset.filename)"
                                                class="btn btn-sm btn-danger" title="删除">
                                            🗑️
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- 创建备份模态框 -->
<div id="createBackupModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h4>创建备份</h4>
            <button onclick="closeModal('createBackupModal')" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="backup-options">
                <div class="backup-option">
                    <h5>数据库备份</h5>
                    <p>仅备份数据库内容，包括所有表和数据</p>
                    <button onclick="createBackup('database')" class="btn btn-primary">创建数据库备份</button>
                </div>
                
                <div class="backup-option">
                    <h5>文件备份</h5>
                    <p>备份博客文章、图片、配置文件等</p>
                    <button onclick="createBackup('files')" class="btn btn-primary">创建文件备份</button>
                </div>
                
                <div class="backup-option">
                    <h5>完整备份</h5>
                    <p>包含数据库和文件的完整系统备份</p>
                    <button onclick="createBackup('full')" class="btn btn-success">创建完整备份</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 清理备份模态框 -->
<div id="cleanupModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h4>清理旧备份</h4>
            <button onclick="closeModal('cleanupModal')" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p>删除超过指定天数的备份文件：</p>
            <div class="cleanup-options">
                <label>
                    <input type="radio" name="cleanup_days" value="7"> 7天前
                </label>
                <label>
                    <input type="radio" name="cleanup_days" value="30" checked> 30天前
                </label>
                <label>
                    <input type="radio" name="cleanup_days" value="90"> 90天前
                </label>
            </div>
            <div class="modal-actions">
                <button onclick="cleanupBackups()" class="btn btn-warning">确认清理</button>
                <button onclick="closeModal('cleanupModal')" class="btn btn-secondary">取消</button>
            </div>
        </div>
    </div>
</div>

<script>
// 显示创建备份模态框
function showCreateBackupModal() {
    document.getElementById('createBackupModal').style.display = 'block';
}

// 显示清理备份模态框
function showCleanupModal() {
    document.getElementById('cleanupModal').style.display = 'block';
}

// 关闭模态框
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// 创建备份
function createBackup(type) {
    const button = event.target;
    const originalText = button.textContent;
    
    button.textContent = '创建中...';
    button.disabled = true;
    
    let url = '';
    switch(type) {
        case 'database':
            url = '{{ route("admin.backup.database") }}';
            break;
        case 'files':
            url = '{{ route("admin.backup.files") }}';
            break;
        case 'full':
            url = '{{ route("admin.backup.full") }}';
            break;
    }
    
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('备份创建成功！文件：' + data.filename);
            location.reload();
        } else {
            alert('备份创建失败：' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('备份创建失败：网络错误');
    })
    .finally(() => {
        button.textContent = originalText;
        button.disabled = false;
    });
}

// 删除备份
function deleteBackup(filename) {
    if (!confirm('确定要删除备份文件 "' + filename + '" 吗？此操作不可恢复。')) {
        return;
    }
    
    fetch(`{{ route('admin.backup.delete', '') }}/${filename}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('备份文件删除成功');
            location.reload();
        } else {
            alert('删除失败：' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('删除失败：网络错误');
    });
}

// 清理旧备份
function cleanupBackups() {
    const days = document.querySelector('input[name="cleanup_days"]:checked').value;
    
    if (!confirm(`确定要删除所有 ${days} 天前的备份文件吗？`)) {
        return;
    }
    
    fetch('{{ route("admin.backup.cleanup") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            days: parseInt(days)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('清理失败：' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('清理失败：网络错误');
    });
}

// 点击模态框外部关闭
window.onclick = function(event) {
    const modals = document.getElementsByClassName('modal');
    for (let modal of modals) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    }
}
</script>

<style>
.backup-page {
    padding: 20px;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.header-actions {
    display: flex;
    gap: 10px;
}

.stats-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 15px;
}

.stat-icon {
    font-size: 2rem;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 50%;
}

.stat-content h3 {
    margin: 0 0 5px 0;
    font-size: 0.9rem;
    color: #666;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    color: #333;
}

.latest-backup {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.latest-backup h3 {
    margin: 0 0 15px 0;
    color: #333;
}

.backup-info {
    display: flex;
    gap: 20px;
    align-items: center;
}

.backup-name {
    font-weight: 600;
    color: #333;
}

.backup-date {
    color: #666;
    font-size: 0.9rem;
}

.backup-size {
    color: #666;
    font-size: 0.9rem;
}

.backups-section {
    background: white;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.backups-section h3 {
    margin: 0 0 20px 0;
    color: #333;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: #666;
}

.empty-state p {
    margin: 0 0 20px 0;
    font-size: 1.1rem;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.table th {
    background: #f8f9fa;
    font-weight: 600;
    color: #333;
}

.backup-filename {
    display: flex;
    align-items: center;
    gap: 8px;
}

.filename {
    font-family: monospace;
    font-size: 0.9rem;
}

.backup-type {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 500;
}

.backup-type-database {
    background: #e3f2fd;
    color: #1976d2;
}

.backup-type-files {
    background: #f3e5f5;
    color: #7b1fa2;
}

.backup-type-full {
    background: #e8f5e8;
    color: #2e7d32;
}

.backup-age {
    font-size: 0.9rem;
    color: #666;
}

.action-buttons {
    display: flex;
    gap: 5px;
}

.btn {
    padding: 8px 16px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: white;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    font-size: 0.9rem;
}

.btn-sm {
    padding: 4px 8px;
    font-size: 0.8rem;
}

.btn-primary {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.btn-success {
    background: #28a745;
    color: white;
    border-color: #28a745;
}

.btn-warning {
    background: #ffc107;
    color: #212529;
    border-color: #ffc107;
}

.btn-danger {
    background: #dc3545;
    color: white;
    border-color: #dc3545;
}

.btn-secondary {
    background: #6c757d;
    color: white;
    border-color: #6c757d;
}

.btn:hover {
    opacity: 0.9;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 10% auto;
    padding: 0;
    border-radius: 8px;
    width: 80%;
    max-width: 600px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.modal-header {
    padding: 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h4 {
    margin: 0;
    color: #333;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: #666;
}

.modal-body {
    padding: 20px;
}

.backup-options {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.backup-option {
    padding: 15px;
    border: 1px solid #eee;
    border-radius: 8px;
    background: #f8f9fa;
}

.backup-option h5 {
    margin: 0 0 8px 0;
    color: #333;
}

.backup-option p {
    margin: 0 0 15px 0;
    color: #666;
    font-size: 0.9rem;
}

.cleanup-options {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin: 20px 0;
}

.cleanup-options label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.modal-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}
</style>
@endsection 