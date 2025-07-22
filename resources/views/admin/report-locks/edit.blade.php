@extends('admin.layout')

@section('title', '编辑Report锁定')
@section('page-title', '编辑Report锁定')

@push('styles')
    @vite(['resources/css/admin/report-locks.css'])
@endpush

@section('content')
<div class="container">
    <div class="header-row">
        <h1>✏️ 编辑Report锁定</h1>
        <a href="{{ route('admin.report-locks.index') }}" class="btn btn-secondary">
            ← 返回列表
        </a>
    </div>

    <!-- 当前信息 -->
    <div class="current-info">
        <h3>当前锁定信息</h3>
        <div class="info-row">
            <span class="info-label">创建时间:</span>
            <span class="info-value">{{ $reportLock->created_at->format('Y-m-d H:i:s') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">锁定时间:</span>
            <span class="info-value">{{ $reportLock->locked_at->format('Y-m-d H:i:s') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">最后更新:</span>
            <span class="info-value">{{ $reportLock->updated_at->format('Y-m-d H:i:s') }}</span>
        </div>
    </div>

    <div class="form-container">
        <form action="{{ route('admin.report-locks.update', $reportLock) }}" method="POST">
            @csrf
            @method('PUT')
            
            <!-- Slug -->
            <div class="form-group">
                <label class="form-label required">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $reportLock->slug) }}" class="form-input" required>
                @error('slug')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                <div class="form-help">报告的唯一标识符</div>
            </div>

            <!-- 标题 -->
            <div class="form-group">
                <label class="form-label required">标题</label>
                <input type="text" name="title" value="{{ old('title', $reportLock->title) }}" class="form-input" required>
                @error('title')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <!-- 标签 -->
            <div class="form-group">
                <label class="form-label required">靶场标签</label>
                <select name="label" class="form-select" required>
                    @foreach($labels as $labelOption)
                        <option value="{{ $labelOption }}" {{ old('label', $reportLock->label) == $labelOption ? 'selected' : '' }}>
                            {{ ucfirst($labelOption) }}
                        </option>
                    @endforeach
                </select>
                @error('label')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                <div class="form-help">报告所属的靶场平台</div>
            </div>

            <!-- 密码 -->
            <div class="form-group">
                <label class="form-label required">密码</label>
                <textarea name="password" class="form-input password-input" required>{{ old('password', $reportLock->password) }}</textarea>
                @error('password')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                <div class="form-help">支持普通密码或长hash（如NTLM、shadow hash），原始存储不转义</div>
                
                <!-- Hash示例 -->
                <div class="hash-examples">
                    <div class="hash-example">
                        <div class="hash-example-label">NTLM Hash 示例:</div>
                        <div class="hash-example-value">5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8</div>
                    </div>
                    <div class="hash-example">
                        <div class="hash-example-label">Shadow Hash 示例:</div>
                        <div class="hash-example-value">$6$rounds=656000$YrHyMHW7lRnR4IG/$nF5pTp/vAw0k1LO.g1VKkaZvl/DqZOH3tKJq3Hm0d1xBk7jHu8A1wqkF7g3w4</div>
                    </div>
                </div>
            </div>

            <!-- 描述 -->
            <div class="form-group">
                <label class="form-label">密码描述</label>
                <textarea name="description" class="form-input form-textarea" placeholder="可选：添加密码提示或描述...">{{ old('description', $reportLock->description) }}</textarea>
                @error('description')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                <div class="form-help">例如：administrator的NTLM hash，或者其他提示信息</div>
            </div>

            <!-- 启用状态 -->
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" name="is_enabled" value="1" class="checkbox-input" {{ old('is_enabled', $reportLock->is_enabled) ? 'checked' : '' }}>
                    <label class="form-label">启用锁定</label>
                </div>
                <div class="form-help">取消选中将禁用锁定但保留记录</div>
            </div>

            <!-- 提交按钮 -->
            <div class="actions-row">
                <a href="{{ route('admin.report-locks.index') }}" class="btn btn-secondary">取消</a>
                <button type="submit" class="btn btn-primary">💾 保存更改</button>
            </div>
        </form>
    </div>
</div>
@endsection 