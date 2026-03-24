<?php

declare(strict_types=1);

require_once __DIR__ . '/seguranca.php';
exigir_login();

require __DIR__ . '/topo.php';
?>
<main>
    <section class="card">
        <h2>Fluxo rápido</h2>
        <ol>
            <li>Cadastre os produtos.</li>
            <li>Cadastre as categorias.</li>
            <li>Cadastre mensagens e vincule às categorias.</li>
            <li>Vincule categorias aos produtos.</li>
            <li>Abra a página de apresentação e selecione o produto.</li>
        </ol>
    </section>
</main>
<?php require __DIR__ . '/rodape.php'; ?>
