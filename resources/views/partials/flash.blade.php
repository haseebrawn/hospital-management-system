@if (session('status'))
    <div class="card flash-card--success">
        <div style="font-weight: 600;">{{ session('status') }}</div>
    </div>
@endif

@if ($errors->any())
    <div class="card flash-card--danger">
        <div style="font-weight: 700; margin-bottom: 8px;">Please fix the following:</div>
        <ul style="margin: 0; padding-left: 16px;">
            @foreach ($errors->all() as $error)
                <li style="margin: 4px 0;">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
