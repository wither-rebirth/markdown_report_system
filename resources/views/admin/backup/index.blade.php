@extends('admin.layout')

@section('title', 'Backup Management')
@section('page-title', 'Backup Management')

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
        <h2 style="margin: 0; font-size: 1.25rem; font-weight: 600;">Backup Management</h2>
        <div style="display: flex; gap: 1rem;">
            <button onclick="showCreateBackupModal()" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Backup
            </button>
            <button onclick="showCleanupModal()" class="btn btn-warning">
                <i class="fas fa-broom"></i> Cleanup Old Backups
            </button>
        </div>
    </div>

    <!-- 统计信息 -->
    <div class="stats-cards">
        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div class="stat-content">
                <h3>Total Backups</h3>
                <div class="stat-number">{{ $stats['total_backups'] }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">💾</div>
            <div class="stat-content">
                <h3>Storage Used</h3>
                <div class="stat-number">{{ $stats['total_size'] }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">🗃️</div>
            <div class="stat-content">
                <h3>Database Backups</h3>
                <div class="stat-number">{{ $stats['database_backups'] }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon">📁</div>
            <div class="stat-content">
                <h3>File Backups</h3>
                <div class="stat-number">{{ $stats['file_backups'] }}</div>
            </div>
        </div>
    </div>

    <!-- 最新备份信息 -->
    @if(isset($stats['latest_backup']))
    <div class="latest-backup">
        <h3>Latest Backup</h3>
        <div class="backup-info">
            <span class="backup-name">{{ $stats['latest_backup']['filename'] }}</span>
            <span class="backup-date">{{ $stats['latest_backup']['created_at']->format('Y-m-d H:i:s') }}</span>
            <span class="backup-size">{{ $stats['latest_backup']['size_formatted'] }}</span>
        </div>
    </div>
    @endif

    <!-- 备份列表 -->
    <div class="backups-section">
        <h3>Backup Files List</h3>
        
        @if($backups->isEmpty())
            <div class="empty-state">
                <p>No backup files</p>
                <button onclick="showCreateBackupModal()" class="btn btn-primary">Create First Backup</button>
            </div>
        @else
            <div class="backups-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Filename</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Created</th>
                            <th>Age</th>
                            <th>Actions</th>
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
                                        {{ $backup['age_days'] }} days ago
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="{{ route('admin.backup.download', $backup['filename']) }}" 
                                           class="btn btn-sm btn-primary" title="Download">
                                            📥
                                        </a>
                                        <button data-filename="{{ $backup['filename'] }}" 
                                                onclick="deleteBackup(this.dataset.filename)"
                                                class="btn btn-sm btn-danger" title="Delete">
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
            <h4>Create Backup</h4>
            <button onclick="closeModal('createBackupModal')" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <div class="backup-options">
                <div class="backup-option">
                    <h5>Database Backup</h5>
                    <p>Backup database content only, including all tables and data</p>
                    <button onclick="createBackup('database')" class="btn btn-primary">Create Database Backup</button>
                </div>
                
                <div class="backup-option">
                    <h5>File Backup</h5>
                    <p>Backup blog posts, images, configuration files, etc.</p>
                    <button onclick="createBackup('files')" class="btn btn-primary">Create File Backup</button>
                </div>
                
                <div class="backup-option">
                    <h5>Full Backup</h5>
                    <p>Complete system backup including database and files</p>
                    <button onclick="createBackup('full')" class="btn btn-success">Create Full Backup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 清理备份模态框 -->
<div id="cleanupModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h4>Cleanup Old Backups</h4>
            <button onclick="closeModal('cleanupModal')" class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <p>Delete backup files older than specified days:</p>
            <div class="cleanup-options">
                <label>
                    <input type="radio" name="cleanup_days" value="7"> 7 days ago
                </label>
                <label>
                    <input type="radio" name="cleanup_days" value="30" checked> 30 days ago
                </label>
                <label>
                    <input type="radio" name="cleanup_days" value="90"> 90 days ago
                </label>
            </div>
            <div class="modal-actions">
                <button onclick="cleanupBackups()" class="btn btn-warning">Confirm Cleanup</button>
                <button onclick="closeModal('cleanupModal')" class="btn btn-secondary">Cancel</button>
            </div>
        </div>
    </div>
</div>




@endsection 