@extends('admin.layout')

@section('title', '添加Report锁定')
@section('page-title', '添加Report锁定')

@push('styles')
    @vite(['resources/css/admin/report-locks.css'])
@endpush

@section('content')
<div class="container">
    <div class="header-row">
        <h1>🔒 添加Report锁定</h1>
        <a href="{{ route('admin.report-locks.index') }}" class="btn btn-secondary">
            ← 返回列表
        </a>
    </div>

    <div class="form-container">
        <form action="{{ route('admin.report-locks.store') }}" method="POST">
            @csrf
            
            <!-- 选择报告 -->
            <div class="form-group">
                <label class="form-label required">选择报告</label>
                <div class="report-select-group">
                    <div>
                        <h4>手动输入</h4>
                        <div class="form-group">
                            <label class="form-label required">Slug</label>
                            <input type="text" name="slug" value="{{ old('slug') }}" class="form-input" placeholder="report-slug" required>
                            @error('slug')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                            <div class="form-help">报告的唯一标识符</div>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label required">标题</label>
                            <input type="text" name="title" value="{{ old('title') }}" class="form-input" placeholder="报告标题" required>
                            @error('title')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div>
                        <h4>可用报告列表</h4>
                        <div class="available-reports">
                            @forelse($availableReports as $report)
                                <div class="report-item" data-slug="{{ $report['slug'] }}" data-title="{{ $report['title'] }}" data-label="{{ $report['label'] }}">
                                    <input type="radio" name="selected_report" value="{{ $report['slug'] }}">
                                    <div>
                                        <div class="report-title">{{ $report['title'] }}</div>
                                        <div class="report-slug">{{ $report['slug'] }}</div>
                                        <span class="report-type">{{ $report['type'] }}</span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-secondary">没有找到可用的报告</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- 标签 -->
            <div class="form-group">
                <label class="form-label required">靶场标签</label>
                <select name="label" class="form-select" required>
                    @foreach($labels as $labelOption)
                        <option value="{{ $labelOption }}" {{ old('label', 'hackthebox') == $labelOption ? 'selected' : '' }}>
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
                <textarea name="password" class="form-input password-input" placeholder="输入密码，支持长hash..." required>{{ old('password') }}</textarea>
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
                <textarea name="description" class="form-input form-textarea" placeholder="可选：添加密码提示或描述...">{{ old('description') }}</textarea>
                @error('description')
                    <div class="form-error">{{ $message }}</div>
                @enderror
                <div class="form-help">例如：administrator的NTLM hash，或者其他提示信息</div>
            </div>

            <!-- 启用状态 -->
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" name="is_enabled" value="1" class="checkbox-input" {{ old('is_enabled', true) ? 'checked' : '' }}>
                    <label class="form-label">启用锁定</label>
                </div>
                <div class="form-help">取消选中将创建但不启用锁定</div>
            </div>

            <!-- 提交按钮 -->
            <div class="actions-row">
                <a href="{{ route('admin.report-locks.index') }}" class="btn btn-secondary">取消</a>
                <button type="submit" class="btn btn-primary">🔒 创建锁定</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/admin/report-locks.js'])
@endpush 