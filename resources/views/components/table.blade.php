<div class="table-container table-responsive" style="overflow-x: auto; background: var(--bg-nav); border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 1px solid var(--border-color); padding: 10px;">
    <table class="table-custom table sgi-mobile-cards" style="width: 100%; border-collapse: separate; border-spacing: 0;">
        @if(isset($head))
            <thead>
                <tr>
                    {{ $head }}
                </tr>
            </thead>
        @endif
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
