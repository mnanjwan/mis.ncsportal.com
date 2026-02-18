@props([
    'name' => 'vehicle_type',
    'id' => null,
    'selected' => null,
    'required' => false,
    'class' => 'kt-select w-full',
])
@php
    $id = $id ?? $name;
    $selected = $selected ?? old($name);
@endphp
<select name="{{ $name }}" id="{{ $id }}" class="{{ $class }}" {{ $required ? 'required' : '' }} {{ $attributes }}>
    <option value="">Select type</option>
    @foreach(config('fleet.vehicle_types', []) as $value => $label)
        <option value="{{ $value }}" @selected($selected === $value)>{{ $label }}</option>
    @endforeach
</select>
