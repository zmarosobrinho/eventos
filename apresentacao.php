<?php

declare(strict_types=1);

require_once __DIR__ . '/seguranca.php';
exigir_login();

$produtos = $pdo->query('SELECT id, nome, descricao FROM produtos ORDER BY nome')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produtoId = (int) ($_POST['produto_id'] ?? 0);
    if ($produtoId > 0) {
        $_SESSION['produto_apresentacao_id'] = $produtoId;
        header('Location: apresentacao.php?produto_id=' . $produtoId);
        exit;
    }
}

if (isset($_GET['produto_id'])) {
    $produtoUrlId = (int) $_GET['produto_id'];
    if ($produtoUrlId > 0) {
        $_SESSION['produto_apresentacao_id'] = $produtoUrlId;
    }
}

$produtoSelecionadoId = (int) ($_SESSION['produto_apresentacao_id'] ?? ($produtos[0]['id'] ?? 0));

$produtoSelecionado = null;
foreach ($produtos as $produto) {
    if ((int) $produto['id'] === $produtoSelecionadoId) {
        $produtoSelecionado = $produto;
        break;
    }
}

$frases = [];
if ($produtoSelecionadoId > 0) {
    $stmt = $pdo->prepare(
        'SELECT DISTINCT m.id, m.titulo, m.texto
         FROM mensagens m
         INNER JOIN categoria_mensagens cm ON cm.mensagem_id = m.id
         INNER JOIN produto_categorias pc ON pc.categoria_id = cm.categoria_id
         WHERE pc.produto_id = :produto_id
         ORDER BY RAND()'
    );

    $stmt->execute(['produto_id' => $produtoSelecionadoId]);
    $frases = $stmt->fetchAll();
}

require __DIR__ . '/topo.php';
?>
<main>
    <section class="card">
        <h2>Apresentação de frases</h2>
        <form method="post" class="linha">
            <label for="produto_id">Produto</label>
            <select id="produto_id" name="produto_id">
                <?php foreach ($produtos as $produto): ?>
                    <option value="<?= (int) $produto['id'] ?>" <?= (int) $produto['id'] === $produtoSelecionadoId ? 'selected' : '' ?>>
                        <?= esc($produto['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Carregar frases</button>
            <?php if ($produtoSelecionadoId > 0): ?>
                <a class="botao-link" href="apresentacao.php?produto_id=<?= $produtoSelecionadoId ?>">Sortear novamente</a>
            <?php endif; ?>
        </form>
    </section>

    <?php if ($produtoSelecionado): ?>
        <section class="card">
            <h3><?= esc($produtoSelecionado['nome']) ?></h3>
            <p><?= nl2br(esc((string) $produtoSelecionado['descricao'])) ?></p>
        </section>
    <?php endif; ?>

    <section class="card">
        <h3>Frases relacionadas às categorias do produto</h3>

        <?php if ($frases === []): ?>
            <p>Nenhuma frase encontrada para este produto.</p>
        <?php else: ?>
            <div class="grade-frases">
                <?php foreach ($frases as $frase): ?>
                    <article class="frase-card">
                        <h4><?= esc($frase['titulo']) ?></h4>
                        <p><?= nl2br(esc($frase['texto'])) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>
<?php require __DIR__ . '/rodape.php'; ?>
