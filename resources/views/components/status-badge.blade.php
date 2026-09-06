@props([
    'value' => null,
    'label' => null,
])
@php
$statusColors = [
    'pending_payment' => 'bg-gray-100 text-gray-800',
    'payment_submitted' => 'bg-yellow-100 text-yellow-800',
    'paid' => 'bg-blue-100 text-blue-800',
    'fulfilling' => 'bg-indigo-100 text-indigo-800',
    'completed' => 'bg-green-100 text-green-800',
    'needs_review' => 'bg-red-100 text-red-800',
    'rejected' => 'bg-rose-100 text-rose-800',
    'cancelled' => 'bg-gray-200 text-gray-700',
    'expired' => 'bg-gray-200 text-gray-600',
    'refunded' => 'bg-purple-100 text-purple-800',

    'pending' => 'bg-gray-100 text-gray-800',
    'processing' => 'bg-indigo-100 text-indigo-800',
    'success' => 'bg-green-100 text-green-800',
    'failed' => 'bg-red-100 text-red-800',
    'needs_review' => 'bg-red-100 text-red-800',

    'submitted' => 'bg-yellow-100 text-yellow-800',
    'verified' => 'bg-green-100 text-green-800',
    'rejected' => 'bg-rose-100 text-rose-800',
    'approved' => 'bg-green-100 text-green-800',

    'unverified' => 'bg-yellow-100 text-yellow-800',
    'verifying' => 'bg-blue-100 text-blue-800',
    'verified' => 'bg-green-100 text-green-800',
    'banned' => 'bg-red-100 text-red-800',

    'open' => 'bg-orange-100 text-orange-800',
    'resolved' => 'bg-green-100 text-green-800',
    'archived' => 'bg-gray-100 text-gray-700',

    'active' => 'bg-green-100 text-green-800',
    'inactive' => 'bg-gray-200 text-gray-700',
];
$enumValue = is_object($value) && method_exists($value, 'value') ? $value->value : (string) $value;
$classes = $statusColors[$enumValue] ?? 'bg-gray-100 text-gray-800';
@endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {$classes}"]) }}>
    {{ $label ?? (is_object($value) && method_exists($value, 'label') ? $value->label() : ucwords(str_replace(['_', '-'], ' ', $enumValue))) }}
</span>
