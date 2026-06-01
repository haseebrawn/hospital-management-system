@if (session('status'))
    <div class="card" style="border-left: 6px solid #10b981; margin-bottom: 14px;">
        <div style="font-weight: 600;">{{ session('status') }}</div>
    </div>
@endif

@if ($errors->any())
    <div class="card" style="border-left: 6px solid #ef4444; margin-bottom: 14px;">
        <div style="font-weight: 700; margin-bottom: 8px;">Please fix the following:</div>
        <ul style="margin: 0; padding-left: 16px;">
            @foreach ($errors->all() as $error)
                <li style="margin: 4px 0;">{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

