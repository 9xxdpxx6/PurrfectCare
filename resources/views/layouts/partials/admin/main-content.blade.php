<div class="main-content p-4">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"
             role="alert"
             id="auto-hide-alert"
             style="position: fixed; top: 24px; right: 24px; z-index: 1080; min-width: 320px; max-width: 90vw;">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"
             role="alert"
             style="position: fixed; top: 24px; right: 24px; z-index: 1080; min-width: 320px; max-width: 90vw;">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    @yield('content')
</div>
