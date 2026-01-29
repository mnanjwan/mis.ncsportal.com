@php
    $isImage = str_starts_with($doc->mime_type ?? '', 'image/');
    $fileUrl = $doc->file_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($doc->file_path) : null;
    $fileExists = $doc->file_path ? \Illuminate\Support\Facades\Storage::disk('public')->exists($doc->file_path) : false;
@endphp
<div class="border border-border rounded-lg p-3 hover:border-primary/50 transition-colors {{ !$fileExists ? 'border-danger/50 bg-danger/5' : '' }}">
    @if($isImage && $fileUrl && $fileExists)
        <img src="{{ $fileUrl }}" 
             alt="{{ $doc->file_name ?? 'Document' }}"
             class="w-full h-32 object-cover rounded mb-2 cursor-pointer document-preview-btn"
             data-url="{{ $fileUrl }}"
             data-name="{{ $doc->file_name }}"
             data-is-image="1">
    @else
        <div class="w-full h-32 flex items-center justify-center bg-muted rounded mb-2 cursor-pointer document-preview-btn {{ !$fileExists ? 'bg-danger/10' : '' }}"
             data-url="{{ $fileUrl }}"
             data-name="{{ $doc->file_name }}"
             data-is-image="0">
            <i class="ki-filled ki-file text-primary text-3xl"></i>
        </div>
    @endif
    
    <div class="text-xs font-medium text-foreground truncate mb-1" title="{{ $doc->file_name }}">
        {{ $doc->file_name ?? 'Document' }}
    </div>
    
    @if($doc->file_size)
    <div class="text-xs text-secondary-foreground mb-2">
        {{ number_format($doc->file_size / 1024, 2) }} KB
    </div>
    @endif
    
    @if(!$fileExists)
    <div class="text-xs text-danger mb-2">
        <i class="ki-filled ki-information"></i> File not found
    </div>
    @endif
    
    <div class="flex gap-2">
        @if($fileExists)
            <a href="{{ route('hrd.uploads.download', $doc->id) }}" 
               class="kt-btn kt-btn-xs kt-btn-primary flex-1"
               title="Download">
                <i class="ki-filled ki-download"></i> Download
            </a>
            <button type="button" 
                    class="kt-btn kt-btn-xs kt-btn-secondary document-preview-btn"
                    data-url="{{ $fileUrl }}"
                    data-name="{{ $doc->file_name }}"
                    data-is-image="{{ $isImage ? '1' : '0' }}"
                    title="Preview">
                <i class="ki-filled ki-eye"></i>
            </button>
        @else
            <span class="kt-btn kt-btn-xs kt-btn-ghost flex-1 opacity-50 cursor-not-allowed">
                <i class="ki-filled ki-download"></i> Unavailable
            </span>
        @endif
    </div>
</div>
