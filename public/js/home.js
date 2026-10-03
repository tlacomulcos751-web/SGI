document.addEventListener('DOMContentLoaded', function () {
    const elements = document.querySelectorAll('.card, .btn');
    elements.forEach((el, index) => {
        setTimeout(() => {
            el.style.opacity = '0';
            el.style.animation = 'fadeIn 0.6s ease-out forwards';
            el.style.animationDelay = `${index * 0.1}s`;
        }, 0);
    });
});

function toggleProductosDropdown() {
    const dropdown = document.getElementById('productosDropdownMenu');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}

function toggleEmpresasDropdown() {
    const dropdown = document.getElementById('empresasDropdownMenu');
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
}

document.addEventListener('click', function (event) {
    const dropdowns = [
        { trigger: 'productosDropdown', menu: 'productosDropdownMenu' },
        { trigger: 'empresasDropdown', menu: 'empresasDropdownMenu' }
    ];

    dropdowns.forEach(({ trigger, menu }) => {
        const dropdown = document.getElementById(trigger);
        const dropdownMenu = document.getElementById(menu);
        if (dropdown && dropdownMenu && !dropdown.contains(event.target)) {
            dropdownMenu.style.display = 'none';
        }
    });
});
