<?php if (!defined('ABSPATH')) exit; ?>

<?php
$res  = Warehouse_API::get_all();
$list = $res['results'] ?? [];
usort($list, function($a, $b) {
    // Nếu kho A là mặc định và B không phải => A lên trước
    if (($a['primary'] ?? 0) == 1 && ($b['primary'] ?? 0) == 0) {
        return -1;
    }
    // Nếu kho B mặc định => B lên trước
    if (($a['primary'] ?? 0) == 1 && ($b['primary'] ?? 0) == 1) {
        // nếu cả 2 đều là mặc định → so created_at
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    }
    if (($a['primary'] ?? 0) == 0 && ($b['primary'] ?? 0) == 1) {
        return 1;
    }

    // Cả 2 đều KHÔNG phải mặc định → sort theo created_at mới -> cũ
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});
/* ---- PHÂN TRANG ---- */
$per_page = 50;
$total = count($list);
$pages = ceil($total / $per_page);
$current = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;

$offset = ($current - 1) * $per_page;
$paged_list = array_slice($list, $offset, $per_page);
?>

<div class="wrap">
    <h1 class="wp-heading-inline">Kho hàng</h1>
    <buton href="#" class="page-title-action" id="wh-open-create-modal">Tạo kho mới</buton>
    <!-- <hr class="wp-header-end"> -->

    <table class="wp-list-table widefat fixed striped table-view-list">
        <thead>
        <tr>
            <th>Mã</th>
            <th>Tên kho</th>
            <th>Địa chỉ</th>
            <th>Mặc định</th>
            <th>Hành động</th>
        </tr>
        </thead>
        <tbody>

        <?php if (empty($paged_list)): ?>
            <tr><td colspan="5">Không có kho hàng</td></tr>
        <?php endif; ?>

        <?php foreach ($paged_list as $w): ?>
            <tr class="wh-row"
                data-code="<?= esc_attr($w['code']) ?>"
                data-name="<?= esc_attr($w['name']) ?>"
                data-phone="<?= esc_attr($w['phone'] ?? '') ?>"
                data-contact="<?= esc_attr($w['contact'] ?? '') ?>"
            >
                <td><?= esc_html($w['code']) ?></td>
                <td><?= esc_html($w['name']) ?></td>
                <td><?= esc_html($w['formatted_address']) ?></td>
                <td><?= $w['primary'] == 1 ? '<strong> Mặc định</strong>' : 'Kho Thường' ?></td>
                <td>
                    <a href="#" class="button button-small wh-edit-btn">Sửa</a>
                </td>
            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>

    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <?php
            echo paginate_links([
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => '« Trước',
                'next_text' => 'Tiếp »',
                'total' => $pages,
                'current' => $current
            ]);
            ?>
        </div>
    </div>
</div>

<?php include DIR . 'views/modals/warehouse-create.php'; ?>
<?php include DIR . 'views/modals/warehouse-edit.php'; ?>

<script src="<?= URL . 'assets/js/warehouse.js' ?>"></script>
