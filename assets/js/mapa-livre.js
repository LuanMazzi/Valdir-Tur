
document.addEventListener('DOMContentLoaded', function () {

    // Autocompletar de cidades (campos de origem e destino)
    configurarAutocomplete('cidadeOrigem');
    configurarAutocomplete('destino');

    function configurarAutocomplete(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;

        const latHidden = document.getElementById(inputId + 'Lat');
        const lonHidden = document.getElementById(inputId + 'Lon');

        // Caixa de sugestões abaixo do campo
        const lista = document.createElement('div');
        lista.className = 'list-group position-absolute w-100 shadow-sm';
        lista.style.zIndex = '1000';
        lista.style.maxHeight = '220px';
        lista.style.overflowY = 'auto';
        input.parentNode.appendChild(lista);

        let timer = null;

        input.addEventListener('input', function () {
            // Texto alterado: limpa as coordenadas antigas
            if (latHidden) latHidden.value = '';
            if (lonHidden) lonHidden.value = '';

            clearTimeout(timer);
            const termo = input.value.trim();
            lista.innerHTML = '';

            if (termo.length < 3) return;

            // Espera 0,5s sem digitar antes de buscar (limite do Nominatim)
            timer = setTimeout(function () {
                buscarLugares(termo, lista, function (lugar) {
                    input.value = lugar.display_name;
                    if (latHidden) latHidden.value = lugar.lat;
                    if (lonHidden) lonHidden.value = lugar.lon;
                    lista.innerHTML = '';
                    calcularDistancia();
                });
            }, 500);
        });

        document.addEventListener('click', function (e) {
            if (e.target !== input) lista.innerHTML = '';
        });
    }

    // Busca lugares na API Nominatim (OpenStreetMap)
    function buscarLugares(termo, lista, aoSelecionar) {
        const url = 'https://nominatim.openstreetmap.org/search?format=json&limit=5&countrycodes=br&q=' + encodeURIComponent(termo);

        fetch(url)
            .then(function (resposta) { return resposta.json(); })
            .then(function (resultados) {
                lista.innerHTML = '';
                resultados.forEach(function (lugar) {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'list-group-item list-group-item-action';
                    item.textContent = lugar.display_name;
                    item.addEventListener('click', function () { aoSelecionar(lugar); });
                    lista.appendChild(item);
                });
            })
            .catch(function () {
                lista.innerHTML = '';
            });
    }

    // Calcula a distância (ida e volta) pela API OSRM
    function calcularDistancia() {
        const latOrigem = document.getElementById('cidadeOrigemLat')?.value;
        const lonOrigem = document.getElementById('cidadeOrigemLon')?.value;
        const latDestino = document.getElementById('destinoLat')?.value;
        const lonDestino = document.getElementById('destinoLon')?.value;
        const campoKm = document.getElementById('qtdKm');

        // Só calcula com origem e destino escolhidos na lista
        if (!latOrigem || !lonOrigem || !latDestino || !lonDestino || !campoKm) return;

        const url = 'https://router.project-osrm.org/route/v1/driving/'
            + lonOrigem + ',' + latOrigem + ';' + lonDestino + ',' + latDestino
            + '?overview=false';

        fetch(url)
            .then(function (resposta) { return resposta.json(); })
            .then(function (dados) {
                if (dados.routes && dados.routes.length) {
                    const distanciaMetros = dados.routes[0].distance;
                    const idaEVoltaKm = (distanciaMetros / 1000) * 2;
                    campoKm.value = idaEVoltaKm.toFixed(1);
                }
            })
            .catch(function () {
                // Serviço fora do ar: o campo é preenchido manualmente
            });
    }
});
