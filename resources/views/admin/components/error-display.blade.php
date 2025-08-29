@props(['error', 'type' => 'danger', 'dismissible' => true])

@php
    $alertClass = 'alert-' . $type;
    $iconClass = match($type) {
        'danger' => 'bi-exclamation-triangle',
        'warning' => 'bi-exclamation-circle',
        'info' => 'bi-info-circle',
        'success' => 'bi-check-circle',
        default => 'bi-exclamation-circle'
    };
@endphp

<div class="alert {{ $alertClass }} alert-dismissible fade show error-alert" role="alert">
    <div class="d-flex align-items-start">
        <i class="bi {{ $iconClass }} fs-5 me-2 mt-1"></i>
        <div class="flex-grow-1">
            @if(is_string($error))
                {{ $error }}
            @elseif(is_array($error))
                @if(count($error) === 1)
                    {{ $error[0] }}
                @else
                    <ul class="mb-0">
                        @foreach($error as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                @endif
            @else
                {{ $error }}
            @endif
        </div>
    </div>
    
    @if($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>
    @endif
</div>


