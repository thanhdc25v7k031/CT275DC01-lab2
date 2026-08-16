<?php

define('TITLE', 'Tìm kiếm Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$has_access = ensure_admin_access();

$error_message = null;
$reason = null;

$form_data = [
    'keyword' => trim($_GET['keyword'] ?? ''),
    'source'  => trim($_GET['source'] ?? '')
];

$sources = [];
$quotes = [];

if ($has_access) {

    try {

        $pdo = get_database_connection();

        $query = 'SELECT DISTINCT source
                  FROM quotes
                  ORDER BY source';

        $statement = $pdo->query($query);
        $sources = $statement->fetchAll();

        if ($_SERVER['REQUEST_METHOD'] === 'GET'
            && ($form_data['keyword'] !== '' || $form_data['source'] !== '')) {

            $query = 'SELECT id, quote, source, favorite
                      FROM quotes
                      WHERE 1 = 1';

            $parameters = [];

            if ($form_data['keyword'] !== '') {
                $query .= ' AND quote LIKE ?';
                $parameters[] = '%' . $form_data['keyword'] . '%';
            }

            if ($form_data['source'] !== '') {
                $query .= ' AND source = ?';
                $parameters[] = $form_data['source'];
            }

            $query .= ' ORDER BY id';

            $statement = $pdo->prepare($query);
            $statement->execute($parameters);

            $quotes = $statement->fetchAll();
        }

    } catch (PDOException $e) {

        $error_message = 'Không thể tìm kiếm trích dẫn';
        $reason = $e->getMessage();
    }

} else {

    $error_message = 'Bạn không có quyền truy cập trang này';
}

?>

<?php render_page_header(); ?>

<h2>Tìm kiếm Trích dẫn</h2>

<?php if (!empty($error_message)): ?>
    <?php include __DIR__ . '/../partials/show_error.php'; ?>
<?php endif; ?>

<?php if ($has_access): ?>

    <form action="search_quote.php" method="get">

        <p>
            <label>Từ khóa
                <input
                    type="text"
                    name="keyword"
                    value="<?= html_escape($form_data['keyword']) ?>">
            </label>
        </p>

        <p>
            <label>Nguồn

                <select name="source">

                    <option value="">-- Tất cả --</option>

                    <?php foreach ($sources as $item): ?>

                        <option
                            value="<?= html_escape($item['source']) ?>"
                            <?= ($form_data['source'] === $item['source']) ? 'selected' : '' ?>>

                            <?= html_escape($item['source']) ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </label>
        </p>

        <p>
            <input type="submit" value="Tìm kiếm">
        </p>

    </form>

    <?php if (!empty($quotes)): ?>

        <h3>Kết quả tìm kiếm</h3>

        <?php foreach ($quotes as $quote): ?>

            <blockquote>
                <?= html_escape($quote['quote']) ?>
            </blockquote>

            <p>
                <?= html_escape($quote['source']) ?>

                <?php if (!empty($quote['favorite'])): ?>
                    <strong>| Yêu thích!</strong>
                <?php endif; ?>
            </p>

        <?php endforeach; ?>

    <?php elseif ($_SERVER['REQUEST_METHOD'] === 'GET'
        && ($form_data['keyword'] !== '' || $form_data['source'] !== '')): ?>

        <p>Không tìm thấy trích dẫn phù hợp.</p>

    <?php endif; ?>

<?php endif; ?>

<?php render_page_footer(); ?>