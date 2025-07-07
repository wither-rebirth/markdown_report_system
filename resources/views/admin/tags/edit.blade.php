@extends('admin.layout')

@section('title', '编辑标签')
@section('page-title', '编辑标签')

@section('content')
<div style="margin: 1.5rem;">
    <div style="max-width: 600px;">
        <form action="{{ route('admin.tags.update', $tag) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">标签信息</h3>
                        <a href="{{ route('admin.tags.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> 返回列表
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="name" class="form-label">标签名称 *</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            class="form-input {{ $errors->has('name') ? 'error' : '' }}"
                            value="{{ old('name', $tag->name) }}"
                            placeholder="请输入标签名称"
                            required
                            maxlength="50"
                        >
                        @if($errors->has('name'))
                            <div class="form-error">{{ $errors->first('name') }}</div>
                        @endif
                    </div>
                    
                    <div class="form-group">
                        <label for="slug" class="form-label">标签别名</label>
                        <input 
                            type="text" 
                            id="slug" 
                            name="slug" 
                            class="form-input {{ $errors->has('slug') ? 'error' : '' }}"
                            value="{{ old('slug', $tag->slug) }}"
                            placeholder="标签URL别名"
                            pattern="[a-z0-9\-]+"
                            maxlength="50"
                        >
                        <div class="form-help">只能包含小写字母、数字和连字符，用于生成标签URL</div>
                        @if($errors->has('slug'))
                            <div class="form-error">{{ $errors->first('slug') }}</div>
                        @endif
                    </div>
                    
                    <div class="form-group">
                        <label for="color" class="form-label">标签颜色</label>
                        <div style="display: flex; gap: 1rem; align-items: center;">
                            <input 
                                type="color" 
                                id="color" 
                                name="color" 
                                class="form-input {{ $errors->has('color') ? 'error' : '' }}"
                                value="{{ old('color', $tag->color ?: '#6366f1') }}"
                                style="width: 60px; height: 40px; padding: 4px; border-radius: 6px;"
                            >
                            <input 
                                type="text" 
                                id="color-text" 
                                class="form-input"
                                value="{{ old('color', $tag->color ?: '#6366f1') }}"
                                pattern="#[0-9a-fA-F]{6}"
                                maxlength="7"
                                style="width: 100px;"
                                placeholder="#6366f1"
                            >
                            <div style="display: flex; gap: 0.5rem;">
                                <button type="button" 
                                        class="btn btn-sm btn-outline color-preset" 
                                        data-color="#ef4444"
                                        style="background-color: #ef4444; width: 30px; height: 30px; padding: 0; border-radius: 50%;">
                                </button>
                                <button type="button" 
                                        class="btn btn-sm btn-outline color-preset" 
                                        data-color="#f97316"
                                        style="background-color: #f97316; width: 30px; height: 30px; padding: 0; border-radius: 50%;">
                                </button>
                                <button type="button" 
                                        class="btn btn-sm btn-outline color-preset" 
                                        data-color="#eab308"
                                        style="background-color: #eab308; width: 30px; height: 30px; padding: 0; border-radius: 50%;">
                                </button>
                                <button type="button" 
                                        class="btn btn-sm btn-outline color-preset" 
                                        data-color="#22c55e"
                                        style="background-color: #22c55e; width: 30px; height: 30px; padding: 0; border-radius: 50%;">
                                </button>
                                <button type="button" 
                                        class="btn btn-sm btn-outline color-preset" 
                                        data-color="#3b82f6"
                                        style="background-color: #3b82f6; width: 30px; height: 30px; padding: 0; border-radius: 50%;">
                                </button>
                                <button type="button" 
                                        class="btn btn-sm btn-outline color-preset" 
                                        data-color="#8b5cf6"
                                        style="background-color: #8b5cf6; width: 30px; height: 30px; padding: 0; border-radius: 50%;">
                                </button>
                                <button type="button" 
                                        class="btn btn-sm btn-outline color-preset" 
                                        data-color="#ec4899"
                                        style="background-color: #ec4899; width: 30px; height: 30px; padding: 0; border-radius: 50%;">
                                </button>
                            </div>
                        </div>
                        <div class="form-help">选择标签的显示颜色，或点击预设颜色快速选择</div>
                        @if($errors->has('color'))
                            <div class="form-error">{{ $errors->first('color') }}</div>
                        @endif
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input 
                                type="checkbox" 
                                name="is_active" 
                                value="1" 
                                {{ old('is_active', $tag->is_active) ? 'checked' : '' }}
                                style="margin-right: 0.5rem;"
                            >
                            启用此标签
                        </label>
                        <div class="form-help">禁用的标签不会在前台显示</div>
                    </div>
                </div>
            </div>
            
            <!-- 预览 -->
            <div class="card" style="margin-top: 1.5rem;">
                <div class="card-header">
                    <h3 class="card-title">预览效果</h3>
                </div>
                <div class="card-body">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <span>标签显示效果：</span>
                        <span id="tag-preview" 
                              class="tag-preview-display"
                              data-bg-color="{{ $tag->display_color }}"
                              style="padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 500; color: white;">
                            {{ $tag->name }}
                        </span>
                    </div>
                </div>
            </div>
            

            
            <div style="margin-top: 1.5rem; display: flex; gap: 1rem;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> 保存更改
                </button>
                <a href="{{ route('admin.tags.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> 取消
                </a>
                <div style="margin-left: auto;">
                    <form action="{{ route('admin.tags.destroy', $tag) }}" 
                          method="POST" 
                          style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="btn btn-danger" 
                                data-confirm="确定要删除标签「{{ $tag->name }}」吗？">
                            <i class="fas fa-trash"></i> 删除标签
                        </button>
                    </form>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// 自动生成slug（只在用户未手动修改时）
document.getElementById('name').addEventListener('input', function() {
    const slugInput = document.getElementById('slug');
    if (!slugInput.dataset.userModified) {
        slugInput.value = generateSlug(this.value);
    }
    updatePreview();
});

document.getElementById('slug').addEventListener('input', function() {
    this.dataset.userModified = 'true';
});

// 颜色选择器同步
document.getElementById('color').addEventListener('input', function() {
    document.getElementById('color-text').value = this.value;
    updatePreview();
});

document.getElementById('color-text').addEventListener('input', function() {
    if (this.value.match(/^#[0-9a-fA-F]{6}$/)) {
        document.getElementById('color').value = this.value;
        updatePreview();
    }
});

// 预设颜色
document.querySelectorAll('.color-preset').forEach(button => {
    button.addEventListener('click', function() {
        const color = this.dataset.color;
        document.getElementById('color').value = color;
        document.getElementById('color-text').value = color;
        updatePreview();
    });
});

function generateSlug(text) {
    return text
        .toLowerCase()
        .trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function updatePreview() {
    const name = document.getElementById('name').value || '示例标签';
    const color = document.getElementById('color').value;
    const preview = document.getElementById('tag-preview');
    
    preview.textContent = name;
    preview.style.backgroundColor = color;
    
    // 根据背景色自动调整文字颜色
    const rgb = hexToRgb(color);
    const brightness = (rgb.r * 299 + rgb.g * 587 + rgb.b * 114) / 1000;
    preview.style.color = brightness > 128 ? '#000000' : '#ffffff';
}

function hexToRgb(hex) {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
    } : null;
}

// 初始化预览
document.querySelector('.tag-preview-display').style.backgroundColor = document.querySelector('.tag-preview-display').dataset.bgColor;
updatePreview();
</script>
@endpush
@endsection 