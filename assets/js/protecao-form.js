
document.addEventListener('DOMContentLoaded', function () {
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
            e.returnValue = ''; // obrigatório pra funcionar em todo navegador, o texto em si é ignorado
        }
    });

    // Ao clicar em Salvar, o formulário está sendo enviado de propósito — não avisa nesse caso
    form.addEventListener('submit', function () {
        formAlterado = false;
    });
});
