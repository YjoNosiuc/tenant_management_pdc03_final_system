<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8"/>
    <title>Lease Contract</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1e293b; padding: 40px; }
        .header { text-align: center; margin-bottom: 32px; padding-bottom: 20px; border-bottom: 3px solid #4F46E5; }
        .header .property-name { font-size: 20px; font-weight: bold; color: #4F46E5; }
        .header .subtitle { font-size: 13px; color: #64748b; margin-top: 4px; }
        .header .powered { font-size: 9px; color: #94a3b8; margin-top: 8px; }
        .contract-title { text-align: center; font-size: 16px; font-weight: bold; color: #1e293b; margin: 24px 0; text-transform: uppercase; letter-spacing: 2px; }
        .section { margin-bottom: 20px; }
        .section-title { font-size: 12px; font-weight: bold; color: #4F46E5; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; padding-bottom: 4px; border-bottom: 1px solid #e2e8f0; }
        .info-grid { display: table; width: 100%; }
        .info-row { display: table-row; }
        .info-label { display: table-cell; width: 40%; font-weight: bold; color: #64748b; padding: 5px 0; font-size: 10px; }
        .info-value { display: table-cell; color: #1e293b; padding: 5px 0; font-size: 11px; }
        .highlight { color: #4F46E5; font-weight: bold; font-size: 14px; }
        .inclusions { margin-top: 8px; }
        .inclusion-badge { display: inline-block; background: #eef2ff; color: #4F46E5; padding: 4px 10px; border-radius: 20px; font-size: 10px; font-weight: bold; margin: 4px 4px 0 0; }
        .notes-box { background: #f8faff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; font-size: 10px; color: #475569; line-height: 1.6; margin-top: 8px; }
        .signature-section { margin-top: 48px; }
        .signature-grid { display: table; width: 100%; }
        .signature-col { display: table-cell; width: 45%; padding: 0 20px 0 0; vertical-align: top; }
        .signature-line { border-bottom: 1px solid #1e293b; margin-bottom: 6px; height: 40px; }
        .signature-label { font-size: 10px; color: #64748b; }
        .signature-name { font-size: 10px; font-weight: bold; color: #1e293b; margin-top: 2px; }
        .divider { width: 10%; display: table-cell; }
        .footer { margin-top: 40px; padding-top: 12px; border-top: 1px solid #e2e8f0; font-size: 9px; color: #94a3b8; text-align: center; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 10px; font-weight: bold; }
        .status-active { background: #f0fdf4; color: #16a34a; }
    </style>
</head>
<body>

@php
    $unitTypeLabel = $lease->unit?->unit_type
        ? str($lease->unit->unit_type)->replace('_', ' ')->title()->toString()
        : '—';
@endphp

<div class="header">
    <div class="property-name">{{ $lease->unit?->property?->name }}</div>
    <div class="subtitle">
        {{ $lease->unit?->property?->address_line1 }},
        {{ $lease->unit?->property?->barangay }},
        {{ $lease->unit?->property?->city }},
        {{ $lease->unit?->property?->province }}
    </div>
    <div class="powered">Powered by RentTrack</div>
</div>

<div class="contract-title">Rental Lease Agreement</div>

<div class="section">
    <div class="section-title">Parties Involved</div>
    <div class="info-grid">
        <div class="info-row">
            <div class="info-label">Property Owner:</div>
            <div class="info-value">{{ $owner->name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tenant Name:</div>
            <div class="info-value">{{ $lease->tenant?->user?->name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tenant Email:</div>
            <div class="info-value">{{ $lease->tenant?->user?->email }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Tenant Phone:</div>
            <div class="info-value">{{ $lease->tenant?->phone_number ?? '—' }}</div>
        </div>
        @if($lease->tenant?->emergency_contact_name)
        <div class="info-row">
            <div class="info-label">Emergency Contact:</div>
            <div class="info-value">
                {{ $lease->tenant->emergency_contact_name }}
                ({{ $lease->tenant->emergency_contact_number }})
            </div>
        </div>
        @endif
    </div>
</div>

<div class="section">
    <div class="section-title">Unit Details</div>
    <div class="info-grid">
        <div class="info-row">
            <div class="info-label">Unit Number:</div>
            <div class="info-value"><strong>Unit {{ $lease->unit?->unit_number }}</strong></div>
        </div>
        <div class="info-row">
            <div class="info-label">Unit Type:</div>
            <div class="info-value">{{ $unitTypeLabel }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Property:</div>
            <div class="info-value">{{ $lease->unit?->property?->name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Address:</div>
            <div class="info-value">
                {{ $lease->unit?->property?->address_line1 }},
                Brgy. {{ $lease->unit?->property?->barangay }},
                {{ $lease->unit?->property?->city }},
                {{ $lease->unit?->property?->province }}
            </div>
        </div>
    </div>
</div>

<div class="section">
    <div class="section-title">Lease Terms</div>
    <div class="info-grid">
        <div class="info-row">
            <div class="info-label">Lease Start Date:</div>
            <div class="info-value">{{ $lease->start_date?->format('F d, Y') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Lease End Date:</div>
            <div class="info-value">{{ $lease->end_date?->format('F d, Y') }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Monthly Rent:</div>
            <div class="info-value highlight">₱{{ number_format((float) $lease->monthly_rent, 2) }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Security Deposit:</div>
            <div class="info-value">
                ₱{{ number_format((float) ($lease->deposit_amount ?? 0), 2) }}
                ({{ ucfirst($lease->deposit_status) }})
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Late Fee Policy:</div>
            <div class="info-value">
                @if($lease->unit?->property?->late_fee_type === 'percentage')
                    {{ $lease->unit->property->late_fee_value }}% of monthly rent per late payment
                @else
                    ₱{{ number_format((float) ($lease->unit?->property?->late_fee_value ?? 0), 2) }} fixed fee per late payment
                @endif
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Lease Status:</div>
            <div class="info-value">
                <span class="status-badge status-active">{{ ucfirst($lease->status) }}</span>
            </div>
        </div>
    </div>
</div>

@if($lease->inclusions && count($lease->inclusions) > 0)
<div class="section">
    <div class="section-title">Inclusions (Included in Monthly Rent)</div>
    <div class="inclusions">
        @foreach($lease->inclusions as $inclusion)
            <span class="inclusion-badge">✓ {{ $inclusion }}</span>
        @endforeach
    </div>
</div>
@endif

@if($lease->notes)
<div class="section">
    <div class="section-title">Special Agreements &amp; Notes</div>
    <div class="notes-box">{{ $lease->notes }}</div>
</div>
@endif

<div class="section">
    <div class="section-title">Standard Terms and Conditions</div>
    <div class="notes-box" style="line-height: 1.8;">
        1. Rent is due on the same date each month as specified in this agreement.<br/>
        2. Late payment fees will apply as stated in the Late Fee Policy above.<br/>
        3. The tenant agrees to maintain the unit in good condition.<br/>
        4. Any damage beyond normal wear and tear will be charged to the tenant.<br/>
        5. The security deposit will be returned within 30 days after move-out.<br/>
        6. Tenant must give 30 days written notice before vacating the unit.<br/>
        7. Subletting without written permission from the owner is prohibited.<br/>
        8. This agreement is governed by Philippine law.
    </div>
</div>

<div class="signature-section">
    <div class="section-title">Signatures</div>
    <p style="font-size:10px; color:#64748b; margin-bottom:24px;">
        By signing below, both parties agree to the terms and conditions stated in this lease agreement.
    </p>
    <div class="signature-grid">
        <div class="signature-col">
            <div class="signature-line"></div>
            <div class="signature-label">Property Owner Signature</div>
            <div class="signature-name">{{ $owner->name }}</div>
            <div class="signature-label" style="margin-top:8px;">Date: ___________________</div>
        </div>
        <div class="divider"></div>
        <div class="signature-col">
            <div class="signature-line"></div>
            <div class="signature-label">Tenant Signature</div>
            <div class="signature-name">{{ $lease->tenant?->user?->name }}</div>
            <div class="signature-label" style="margin-top:8px;">Date: ___________________</div>
        </div>
    </div>
</div>

<div class="footer">
    This document was generated by RentTrack on {{ now('Asia/Manila')->format('F d, Y') }}.
    Lease Contract #{{ $lease->id }} | Unit {{ $lease->unit?->unit_number }} | {{ $lease->unit?->property?->name }}
</div>

</body>
</html>
