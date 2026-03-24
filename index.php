<?php

declare(strict_types=1);
require_once __DIR__ . '/config.php';
require __DIR__ . '/header.php';
?>
<main>
    <section class="card">
        <h2>Como usar</h2>
        <ol>
            <li>Cadastre os produtos.</li>
            <li>Cadastre as categorias.</li>
            <li>Cadastre as mensagens e associe às categorias.</li>
            <li>Associe categorias aos produtos.</li>
            <li>Abra a tela de apresentação e selecione um produto.</li>
        </ol>
        <p>A tela de apresentação guarda o produto selecionado na sessão e sorteia novamente as frases a cada atualização.</p>
    </section>
</main>
<?php require __DIR__ . '/footer.php'; ?>
