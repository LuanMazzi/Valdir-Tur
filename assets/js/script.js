document.addEventListener('DOMContentLoaded', function () {
    // Usado em: cadastro-veiculo.php e admin/editar/veiculo.php
    
    const tagContainer = document.getElementById('tagContainer');
    const tagInput = document.getElementById('tagInput');
    const tagsHidden = document.getElementById('tagsHidden');

    if (tagContainer && tagInput && tagsHidden) {

       
        const tags = tagsHidden.value
            ? tagsHidden.value.split(',').map(t => t.trim()).filter(Boolean)
            : [];

        function renderTags() {
            tagContainer.querySelectorAll('.tag-chip').forEach(chip => chip.remove());

            tags.forEach((tag, index) => {
                const chip = document.createElement('span');
                chip.className = 'tag-chip badge bg-secondary me-1 mb-1 d-inline-flex align-items-center';
                chip.style.gap = '6px';
                chip.textContent = tag;

                const remove = document.createElement('i');
                remove.className = 'bi bi-x';
                remove.style.cursor = 'pointer';
                remove.addEventListener('click', () => {
                    tags.splice(index, 1);
                    renderTags();
                });

                chip.appendChild(remove);
                tagContainer.insertBefore(chip, tagInput);
            });

            tagsHidden.value = tags.join(',');
        }

        
        if (tags.length) {
            renderTags();
        }

        tagInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ',') {
                e.preventDefault();
                const valor = tagInput.value.trim();
                if (valor && !tags.includes(valor)) {
                    tags.push(valor);
                    renderTags();
                }
                tagInput.value = '';
            } else if (e.key === 'Backspace' && tagInput.value === '' && tags.length) {
                tags.pop();
                renderTags();
            }
        });


        const form = tagContainer.closest('form');
        if (form) {
            form.addEventListener('submit', function () {
                const pendente = tagInput.value.trim();
                if (pendente && !tags.includes(pendente)) {
                    tags.push(pendente);
                    renderTags();
                }
            });
        }
    }

    // Usado em: cadastro-veiculo.php e admin/editar/veiculo.php

    const fileUpload = document.getElementById('fileUpload');
    const fileUploadLabel = document.getElementById('fileUploadLabel');

    if (fileUpload && fileUploadLabel) {
        fileUpload.addEventListener('change', function () {
            fileUploadLabel.textContent = fileUpload.files.length
                ? fileUpload.files[0].name
                : 'Clique para selecionar';
        });
    }

});