<?php

declare(strict_types=1);

require_once __DIR__ . '/seguranca.php';
exigir_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $texto = trim($_POST['texto'] ?? '');
    $categoriasIds = array_map('intval', $_POST['categorias_ids'] ?? []);

    if ($titulo !== '' && $texto !== '') {
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare('INSERT INTO mensagens (titulo, texto) VALUES (:titulo, :texto)');
            $stmt->execute([
                'titulo' => $titulo,
                'texto' => $texto,
            ]);

            $mensagemId = (int) $pdo->lastInsertId();

            if ($categoriasIds !== []) {
                $vinculo = $pdo->prepare('INSERT INTO categoria_mensagens (categoria_id, mensagem_id) VALUES (:categoria_id, :mensagem_id)');

                foreach ($categoriasIds as $categoriaId) {
                    $vinculo->execute([
                        'categoria_id' => $categoriaId,
                        'mensagem_id' => $mensagemId,
                    ]);
                }
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    header('Location: mensagens.php');
    exit;
}

$categorias = $pdo->query('SELECT id, nome FROM categorias ORDER BY nome')->fetchAll();
$mensagens = $pdo->query(
    'SELECT m.id, m.titulo, m.texto,
            COALESCE(GROUP_CONCAT(c.nome ORDER BY c.nome SEPARATOR ", "), "Sem categoria") AS categorias
     FROM mensagens m
     LEFT JOIN categoria_mensagens cm ON cm.mensagem_id = m.id
     LEFT JOIN categorias c ON c.id = cm.categoria_id
     GROUP BY m.id
     ORDER BY m.id DESC'
)->fetchAll();

require __DIR__ . '/topo.php';
?>
<main>
    <section class="card">
        <h2>Cadastro de mensagens</h2>
        <form method="post">
            <label for="titulo">Título</label>
            <input id="titulo" name="titulo" required>

            <label for="texto">Mensagem / frase</label>
            <textarea id="texto" name="texto" rows="4" required></textarea>

            <fieldset>
                <legend>Vincular categorias</legend>
                <div class="checks">
                    <?php foreach ($categorias as $categoria): ?>
                        <label>
                            <input type="checkbox" name="categorias_ids[]" value="<?= (int) $categoria['id'] ?>">
                            <?= esc($categoria['nome']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>

            <button type="submit">Salvar mensagem</button>
        </form>
    </section>

    <section class="card">
        <h2>Mensagens cadastradas</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Texto</th>
                    <th>Categorias</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mensagens as $mensagem): ?>
                    <tr>
                        <td><?= (int) $mensagem['id'] ?></td>
                        <td><?= esc($mensagem['titulo']) ?></td>
                        <td><?= nl2br(esc($mensagem['texto'])) ?></td>
                        <td><?= esc($mensagem['categorias']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
<?php require __DIR__ . '/rodape.php'; ?>
