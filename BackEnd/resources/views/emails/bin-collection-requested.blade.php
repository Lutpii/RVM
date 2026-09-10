<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bin Collection Requested</title>
</head>
<body style="font-family: sans-serif; color: #1a202c; max-width: 600px; margin: 0 auto;">
    <h2 style="color: #22c55e;">♻️ RVM Bin Collection Request</h2>
    <p>The following {{ count($machineSummaries) }} machine(s) have at least one bin at 90% capacity or above and need collection:</p>

    @foreach ($machineSummaries as $machine)
        <div style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px; margin-bottom: 12px;">
            <strong>{{ $machine['name'] }}</strong> ({{ $machine['machine_code'] }})
            @if ($machine['location_name'])
                <br><span style="color: #718096;">📍 {{ $machine['location_name'] }}</span>
            @endif
            <ul>
                @foreach ($machine['full_materials'] as $material)
                    <li>{{ $material['label'] }}: {{ $material['level'] }}%</li>
                @endforeach
            </ul>
        </div>
    @endforeach

    <p style="color: #718096; font-size: 13px;">Sent automatically by the RVM Smart Recycling admin panel.</p>
</body>
</html>
