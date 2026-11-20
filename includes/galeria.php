<?php
// Galeria de fotos e vídeos das telas de pacote e de veículo:
// imagem grande (carrossel do Bootstrap) com miniaturas clicáveis embaixo
function galeriaMidia($lista, $alt, $id = 'galeria')
{
    $alt = htmlspecialchars($alt);

    // Sem mídia cadastrada: bloco com ícone no mesmo tamanho da galeria
    if (!$lista) { ?>
        <div class="galeria-palco d-flex align-items-center justify-content-center bg-light text-secondary rounded-3 border">
            <i class="bi bi-image" style="font-size: 4rem;"></i>
        </div>
    <?php return; }

    $varios = count($lista) > 1; ?>

    <!-- Imagem ou vídeo em destaque -->
    <div id="<?= $id ?>" class="carousel slide galeria-palco rounded-3 overflow-hidden bg-dark">
        <div class="carousel-inner h-100">
            <?php foreach ($lista as $i => $arquivo): ?>
                <div class="carousel-item h-100 <?= $i === 0 ? 'active' : '' ?>">
                    <?php if (midiaEhVideo($arquivo)): ?>
                        <video class="d-block w-100 h-100" style="object-fit: contain;" controls preload="metadata">
                            <source src="/ValdirTur/assets/uploads/<?= htmlspecialchars($arquivo) ?>">
                        </video>
                    <?php else: ?>
                        <img src="/ValdirTur/assets/uploads/<?= htmlspecialchars($arquivo) ?>" class="d-block w-100 h-100"
                            style="object-fit: cover;" alt="<?= $alt ?>">
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($varios): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#<?= $id ?>" data-bs-slide="prev" aria-label="Anterior">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#<?= $id ?>" data-bs-slide="next" aria-label="Próxima">
                <span class="carousel-control-next-icon"></span>
            </button>
        <?php endif; ?>
    </div>

    <?php if ($varios): ?>
        <!-- Miniaturas (clicar troca a imagem em destaque) -->
        <div class="d-flex gap-2 mt-2 overflow-auto pb-1" id="<?= $id ?>-miniaturas">
            <?php foreach ($lista as $i => $arquivo): ?>
                <button type="button" class="galeria-miniatura p-0 <?= $i === 0 ? 'ativa' : '' ?>"
                    data-bs-target="#<?= $id ?>" data-bs-slide-to="<?= $i ?>" aria-label="Mostrar mídia <?= $i + 1 ?>">
                    <?php if (midiaEhVideo($arquivo)): ?>
                        <span class="d-flex w-100 h-100 align-items-center justify-content-center bg-dark text-white">
                            <i class="bi bi-play-circle-fill fs-3"></i>
                        </span>
                    <?php else: ?>
                        <img src="/ValdirTur/assets/uploads/<?= htmlspecialchars($arquivo) ?>" alt="" class="w-100 h-100"
                            style="object-fit: cover;">
                    <?php endif; ?>
                </button>
            <?php endforeach; ?>
        </div>

        <script>
            // Marca a miniatura da imagem que está em destaque
            (function () {
                const carrossel = document.getElementById('<?= $id ?>');
                const miniaturas = document.querySelectorAll('#<?= $id ?>-miniaturas .galeria-miniatura');
                carrossel.addEventListener('slide.bs.carousel', function (e) {
                    miniaturas.forEach((m, i) => m.classList.toggle('ativa', i === e.to));
                });
            })();
        </script>
    <?php endif;
}
