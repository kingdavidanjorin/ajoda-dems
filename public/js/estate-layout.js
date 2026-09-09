document.addEventListener('DOMContentLoaded', () => {
    const markers = document.querySelectorAll(
        '[data-layout-house]',
    );

    const fields = {
        name: document.getElementById('layout-property-name'),
        code: document.getElementById('layout-property-code'),
        status: document.getElementById('layout-property-status'),
        phase: document.getElementById('layout-property-phase'),
        type: document.getElementById('layout-property-type'),
        price: document.getElementById('layout-property-price'),
        link: document.getElementById('layout-property-link'),
    };

    markers.forEach((marker) => {
        marker.addEventListener('click', () => {
            fields.name.textContent = marker.dataset.name;
            fields.code.textContent = marker.dataset.code;
            fields.status.textContent = marker.dataset.status;
            fields.phase.textContent = marker.dataset.phase;
            fields.type.textContent = marker.dataset.type;
            fields.price.textContent = marker.dataset.price;
            fields.link.href = marker.dataset.url;
        });
    });
});