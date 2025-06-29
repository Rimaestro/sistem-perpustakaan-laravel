@props([
    'status' => 'default',
    'color' => 'gray',
    'size' => 'sm'
])

@php
    // Define color classes based on the color prop
    $colorClasses = [
        'gray' => 'bg-gray-100 text-gray-800',
        'red' => 'bg-red-100 text-red-800',
        'yellow' => 'bg-yellow-100 text-yellow-800',
        'green' => 'bg-green-100 text-green-800',
        'blue' => 'bg-blue-100 text-blue-800',
        'indigo' => 'bg-indigo-100 text-indigo-800',
        'purple' => 'bg-purple-100 text-purple-800',
        'pink' => 'bg-pink-100 text-pink-800',
    ];

    // Define size classes
    $sizeClasses = [
        'xs' => 'px-2 py-0.5 text-xs',
        'sm' => 'px-2.5 py-0.5 text-xs',
        'md' => 'px-3 py-1 text-sm',
        'lg' => 'px-4 py-1 text-sm',
    ];

    // Get the appropriate classes
    $colorClass = $colorClasses[$color] ?? $colorClasses['gray'];
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['sm'];
@endphp

<span {{ $attributes->merge([
    'class' => "inline-flex items-center rounded-full font-medium {$colorClass} {$sizeClass}"
]) }}>
    {{ $slot }}
</span>
