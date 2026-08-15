<?php
/* Đoạn mã xử lý PHP. */

define('TITLE', 'Xem tất cả các Trích dẫn');

require_once __DIR__ . '/../partials/header.php';
require_once __DIR__ . '/../partials/footer.php';

$has_access = ensure_admin_access();
$error_message = null;
$reason = null;
$quotes = [];

if ($has_access) {
    $error_message = 'Bạn không có quyền truy cập trang này';
}else{
    $query = 'select id, quote,source,favorite from quotes order by date_entered desc';
    try{
        $pdo = get_database_connection();
        $statement = $pdo->prepare($query);
        $statement->execute();
        $quotes = $statement->fetchALL();
    }catch(PDOException $e){
        $error_message = 'không tìm thấy dữ liệu';
        $reason = $e-> getMessage();
    }
}

?>

<!--
    Đoạn mã HTML trình bày nội dung trang web.
-->
<?php render_page_header(); ?>

<h2>Tất cả các Trích dẫn</h2>

<?php if (!empty($error_message)): ?>
    <?php include __DIR__ . '/../partials/show_error.php'; ?>
<?php endif; ?>

<?php if ($has_access): ?>
    <p>Trang đang được xây dựng...</p><?php endif; ?>
    <?php if ($has_access && empty($error_message)): ?>
        <?php if(!empty($quotes)): ?>
            <?php foreach($quotes as $quotes): ?>
                <div>
                    <blockquote><?= html_escape($quotes['quote']) ?></blockquote>
                    <p> <?= html_escape($quotes['source']) ?>
                    <?php if(!empty($quotes['favorite'])): ?>
                    <strong> | Yêu thích!</strong>
                    <?php endif; ?>
            </p>
            <p>
                <strong>Quản trị Trích dẫn:</strong>
                <a href="edit_quote.php?id=<?=urlencode($quotes['id'])?>">Sửa</a>
                <a href="delete_quote.php?id=<?= urldecode($quotes['id'])?>">Xóa</a>
            </p>
                </div>
                <br>
                <?php endforeach?>
                <?php else: ?>
                    <p>Chưa có trích dẫn nào.</p>
                    <?php endif; ?>
<?php endif; ?>

<?php render_page_footer(); ?>