@php
    $cls = match ($status) {
        'pending' => 'bg-amber-100 text-amber-700',
        'confirmed' => 'bg-blue-100 text-blue-700',
        'in_progress' => 'bg-purple-100 text-purple-700',
        'completed' => 'bg-green-100 text-green-700',
        'cancelled' => 'bg-gray-100 text-gray-500',
        default => 'bg-gray-100 text-gray-500',
    };
    $label = match ($status) {
        'in_progress' => 'In progress',
        default => ucfirst($status),
    };
@endphp
<span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold {{ $cls }}">{{ $label }}</span>
