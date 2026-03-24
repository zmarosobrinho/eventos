<?php

declare(strict_types=1);

require_once __DIR__ . '/seguranca.php';
exigir_login();

$produtos = $pdo->query('SELECT id, nome FROM produtos ORDER BY nome')->fetchAll();
$categorias = $pdo->query('SELECT id, nome FROM categorias ORDER BY nome')->fetchAll();

$produtoSelecionadoId = (int) ($_GET['produto_id'] ?? ($produtos[0]['id'] ?? 0));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $produtoSelecionadoId = (int) ($_POST['produto_id'] ?? 0);
    $categoriasIds = array_map('intval', $_POST['categorias_ids'] ?? []);

    if ($produtoSelecionadoId > 0) {
        $pdo->beginTransaction();

        try {
            $pdo->prepare('DELETE FROM produto_categorias WHERE produto_id = :produto_id')
                ->execute(['produto_id' => $produtoSelecionadoId]);

            if ($categoriasIds !== []) {
                $stmt = $pdo->prepare('INSERT INTO produto_categorias (produto_id, categoria_id) VALUES (:produto_id, :categoria_id)');

                foreach ($categoriasIds as $categoriaId) {
                    $stmt->execute([
                        'produto_id' => $produtoSelecionadoId,
                        'categoria_id' => $categoriaId,
                    ]);
                }
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    header('Location: produto_categorias.php?produto_id=' . $produtoSelecionadoId);
    exit;
}

$vinculadas = [];
if ($produtoSelecionadoId > 0) {
    $stmt = $pdo->prepare('SELECT categoria_id FROM produto_categorias WHERE produto_id = :produto_id');
    $stmt->execute(['produto_id' => $produtoSelecionadoId]);
    $vinculadas = array_map(static fn (array $row): int => (int) $row['categoria_id'], $stmt->fetchAll());
}

require __DIR__ . '/topo.php';
?>
<main>
    <section class="card">
        <h2>Vincular categorias ao produto</h2>

        <form method="get" class="linha">
            <label for="produto_id">Produto</label>
            <select id="produto_id" name="produto_id" onchange="this.form.submit()">
                <?php foreach ($produtos as $produto): ?>
                    <option value="<?= (int) $produto['id'] ?>" <?= (int) $produto['id'] === $produtoSelecionadoId ? 'selected' : '' ?>>
                        <?= esc($produto['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <form method="post">
            <input type="hidden" name="produto_id" value="<?= $produtoSelecionadoId ?>">

            <fieldset>
                <legend>Categorias do produto</legend>
                <div class="checks">
                    <?php foreach ($categorias as $categoria): ?>
                        <?php $marcado = in_array((int) $categoria['id'], $vinculadas, true); ?>
                        <label>
                            <input type="checkbox" name="categorias_ids[]" value="<?= (int) $categoria['id'] ?>" <?= $marcado ? 'checked' : '' ?>>
                            <?= esc($categoria['nome']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <button type="submit">Salvar vínculos</button>
        </form>
    </section>
</main>
<?php require __DIR__ . '/rodape.php'; ?>
