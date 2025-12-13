<?php
if (!defined('ABSPATH')) exit;

/**
 * Lấy danh sách kho (có cache)
 */
$cache_key = 'supership_warehouses_list';
$list = get_transient($cache_key);

if ($list === false) {
    $res  = Warehouse_API::get_all();
    $list = $res['results'] ?? [];
    set_transient($cache_key, $list, 5 * MINUTE_IN_SECONDS);
}

$default_warehouse = null;
foreach ($list as $w) {
    if (($w['primary'] ?? 0) == 1) {
        $default_warehouse = $w;
        break;
    }
}
/**
 * Phân trang
 */
$per_page = 50;
$total    = count($list);
$pages    = max(1, ceil($total / $per_page));
$current  = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;

$offset     = ($current - 1) * $per_page;
$paged_list = array_slice($list, $offset, $per_page);
?>
<h2><?php esc_html_e('Kho hàng', 'supership'); ?></h2>

<p class="description">
    <?php esc_html_e(
        'Danh sách các kho hàng được đồng bộ từ hệ thống SuperShip.',
        'supership'
    ); ?>
</p>

<?php if ($default_warehouse): ?>
    <div class="notice notice-info inline" style="margin-top:10px;">
        <p>
            <strong style="color:#d63638;">*</strong>
            <strong><?php esc_html_e('Kho mặc định: ', 'supership'); ?></strong>
            <strong>
                <?php echo esc_html($default_warehouse['name'] ?? ''); ?> 
            </strong>
            <br>
            <span class="description">
                <?php echo esc_html(
                    $default_warehouse['formatted_address']
                    ?? $default_warehouse['address']
                    ?? ''
                ); ?>
            </span>
        </p>
    </div>
<?php endif; ?>


<div class="tablenav top">
    <div class="alignleft actions">
        <button
            id="wh-open-create-modal"
            class="button button-primary"
        >
            <?php esc_html_e('Tạo kho mới', 'supership'); ?>
        </button>
    </div>

    <?php if ($pages > 1): ?>
        <div class="tablenav-pages">
            <?php
            echo paginate_links([
                'base'      => add_query_arg('paged', '%#%'),
                'format'    => '',
                'prev_text' => esc_html__('« Trước', 'supership'),
                'next_text' => esc_html__('Sau »', 'supership'),
                'total'     => $pages,
                'current'   => $current,
            ]);
            ?>
        </div>
    <?php endif; ?>
</div>

<table class="wp-list-table widefat fixed striped table-view-list">
    <thead>
        <tr>
            <th style="width:140px;">
                <?php esc_html_e('Mã kho hàng', 'supership'); ?>
            </th>
            <th>
                <?php esc_html_e('Tên kho hàng', 'supership'); ?>
            </th>
            <th>
                <?php esc_html_e('Địa chỉ kho hàng', 'supership'); ?>
            </th>
            <th style="width:120px;">
                <?php esc_html_e('', 'supership'); ?>
            </th>
        </tr>
    </thead>

    <tbody>
        <?php if (empty($paged_list)): ?>
            <tr>
                <td colspan="4" style="text-align:center;">
                    <?php esc_html_e('Chưa có kho hàng nào.', 'supership'); ?>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($paged_list as $w): ?>
                <tr class="wh-row"
                    data-code="<?php echo esc_attr($w['code'] ?? ''); ?>"
                    data-name="<?php echo esc_attr($w['name'] ?? ''); ?>"
                    data-phone="<?php echo esc_attr($w['phone'] ?? ''); ?>"
                    data-contact="<?php echo esc_attr($w['contact'] ?? ''); ?>"
                >
                    <td>
                        <strong><?php echo esc_html($w['code'] ?? ''); ?></strong>
                    </td>

                    <td>
                        <?php
                        if (($w['primary'] ?? 0) == 1) {
                            echo '<span style="color:#d63638; font-weight:600;">*</span> ';
                        }
                        echo esc_html($w['name'] ?? '');
                        ?>
                    </td>

                    <td>
                        <?php
                        echo esc_html(
                            $w['formatted_address']
                            ?? $w['address']
                            ?? ''
                        );
                        ?>
                    </td>

                    <td>
                        <a href="#" class="button button-small wh-edit-btn">
                            <?php esc_html_e('Sửa', 'supership'); ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<?php if ($pages > 1): ?>
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <?php
            echo paginate_links([
                'base'      => add_query_arg('paged', '%#%'),
                'format'    => '',
                'prev_text' => esc_html__('« Trước', 'supership'),
                'next_text' => esc_html__('Sau »', 'supership'),
                'total'     => $pages,
                'current'   => $current,
            ]);
            ?>
        </div>
    </div>
<?php endif; ?>

<?php
// Modals
include DIR . 'views/modals/warehouse-create.php';
include DIR . 'views/modals/warehouse-edit.php';
?>

<script src="<?php echo esc_url(URL . 'assets/js/warehouse.js'); ?>"></script>
