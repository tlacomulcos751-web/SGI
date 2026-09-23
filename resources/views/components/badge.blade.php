@props(['type' => 'primary'])

<span class="badge badge-{{ $type }}" style="display: inline-flex; align-items: center; padding: 4px 10px; font-size: 0.75rem; font-weight: 600; border-radius: 999px;">
    {{ $slot }}
</span>
