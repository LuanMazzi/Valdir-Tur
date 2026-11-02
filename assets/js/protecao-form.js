
document.addEventListener('DOMContentLoaded', function () {
    // Avisa ao sair da página com alterações não salvas
    const form = document.querySelector('form');
    if (!form) return;

    let formAlterado = false;

    form.querySelectorAll('input, textarea, select').forEach(function (campo) {
        campo.addEventListener('input', function () { formAlterado = true; });
        campo.addEventListener('change', function () { formAlterado = true; });
    });

    window.addEventListener('beforeunload', function (e) {
        if (formAlterado) {
            e.preventDefault();
            e.returnValue = ''; // necessário para o aviso aparecer em todos os navegadores
        }
    });

    // Ao salvar, não avisa
    form.addEventListener('submit', function () {
        formAlterado = false;
    });
});
