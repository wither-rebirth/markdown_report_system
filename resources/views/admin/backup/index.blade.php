@extends('admin.layout')

@section('title', '备份管理')
@section('page-title', '备份管理')

@push('styles')
@vite(['resources/css/admin/backup.css'])
@endpush

@push('scripts')
@vite(['resources/js/admin/backup.js'])
@endpush

@section('content')
<div class="backup-page">

    <!-- 控制面板 -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; padding: 1rem; background: var(--bg-primary); border: 1px solid var(--gray-200); border-radius: var(--radius-lg);">
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 600;">备份管理</h2>
        <div style="display: flex; gap: 1rem;">
            <button onclick="showCreateBackupModal()" class="btn btn-primary">
                <i class="fas fa-plus"></i> 创建备份
            </button>
            <button onclick="showCleanupModal()" class="btn btn-warning">
                <i class="fas fa-broom"></i> 清理旧备份
            </button>
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




@endsection 