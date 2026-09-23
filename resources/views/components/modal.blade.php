@props(['id', 'title' => null])

<div id="{{ $id }}" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                @if($title)
                    <h5 class="modal-title fw-bold">{{ $title }}</h5>
                @endif
                <button type="button" class="btn-close" onclick="closeModal('{{ $id }}')" aria-label="Cerrar"></button>
            </div>
            
            <div class="modal-body">
                {{ $slot }}
            </div>
            
            @if(isset($footer))
                <div class="modal-footer">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
