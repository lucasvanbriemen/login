@props([
    'type' => 'text',
    'name' => '',
    'value' => '',
    'class' => 'input-field',
    'id' => null,
    'label' => null,
])

<div class="input-wrapper">
    <input type="{{ $type }}" name="{{ $name }}" value="{{ $value }}" class="{{ $class }}" id="{{ $id }}" />
    <label for="{{ $id }}">{{ $label }}</label>
</div>