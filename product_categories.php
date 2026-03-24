<?php

declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = (int) ($_POST['product_id'] ?? 0);
    $categoryIds = array_map('intval', $_POST['category_ids'] ?? []);

    if ($productId > 0) {
        $pdo->beginTransaction();
        try {
            $delete = $pdo->prepare('DELETE FROM product_categories WHERE product_id = :product_id');
            $delete->execute(['product_id' => $productId]);

            if ($categoryIds !== []) {
                $insert = $pdo->prepare('INSERT INTO product_categories (product_id, category_id) VALUES (:product_id, :category_id)');
                foreach ($categoryIds as $categoryId) {
                    $insert->execute([
                        'product_id' => $productId,
                        'category_id' => $categoryId,
                    ]);
                }
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    redirect('product_categories.php?product_id=' . $productId);
}

$products = $pdo->query('SELECT id, name FROM products ORDER BY name')->fetchAll();
$categories = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();

$selectedProductId = (int) ($_GET['product_id'] ?? ($products[0]['id'] ?? 0));
$linkedCategories = [];
if ($selectedProductId > 0) {
    $stmt = $pdo->prepare('SELECT category_id FROM product_categories WHERE product_id = :product_id');
    $stmt->execute(['product_id' => $selectedProductId]);
    $linkedCategories = array_map(fn ($row) => (int) $row['category_id'], $stmt->fetchAll());
}

require __DIR__ . '/header.php';
?>
<main>
    <section class="card">
        <h2>Associar categorias ao produto</h2>
        <form method="get" class="inline-form">
            <label for="product_id">Produto</label>
            <select id="product_id" name="product_id" onchange="this.form.submit()">
                <?php foreach ($products as $product): ?>
                    <option value="<?= (int) $product['id'] ?>" <?= (int) $product['id'] === $selectedProductId ? 'selected' : '' ?>>
                        <?= h($product['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <form method="post">
            <input type="hidden" name="product_id" value="<?= $selectedProductId ?>">
            <fieldset>
                <legend>Categorias do produto selecionado</legend>
                <div class="checks">
                    <?php foreach ($categories as $category): ?>
                        <?php $checked = in_array((int) $category['id'], $linkedCategories, true); ?>
                        <label>
                            <input type="checkbox" name="category_ids[]" value="<?= (int) $category['id'] ?>" <?= $checked ? 'checked' : '' ?>>
                            <?= h($category['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
            <button type="submit">Salvar vínculos</button>
        </form>
    </section>
</main>
<?php require __DIR__ . '/footer.php'; ?>
