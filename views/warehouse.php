<?php if (!defined('ABSPATH')) exit; ?>

<?php
/**
 * ==============================
 * LOAD DATA (WITH CACHE)
 * ==============================
 */
$cache_key = 'supership_warehouses_list';
$list = get_transient($cache_key);
if ($list === false) {
    $res  = Warehouse_API::get_all();
    $list = $res['results'] ?? [];
    set_transient($cache_key, $list, 5 * MINUTE_IN_SECONDS);
}
/**
 * ==============================
 * PAGINATION
 * ==============================
 */
$per_page = 50;
$total    = count($list);
$pages    = max(1, ceil($total / $per_page));
$current  = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;

$offset     = ($current - 1) * $per_page;
$paged_list = array_slice($list, $offset, $per_page);
?>
<div class="wrap">
    <h1 style="margin-bottom:15px;">Warehouses</h1>

    <button
        id="wh-open-create-modal"
        class="button button-primary"
        style="margin-bottom:10px;"
    >
        <i style="margin-right:6px; "></i>
        Create Warehouse
    </button>


    <table class="wp-list-table widefat fixed striped table-view-list">
        <thead>
        <tr>
            <th width="120">Code</th>
            <th>Name</th>
            <th>Address</th>
            <th width="120">Primary</th>
            <th width="120">Actions</th>
        </tr>
        </thead>

        <tbody>
        <?php if (empty($paged_list)): ?>
            <tr>
                <td colspan="5" style="text-align:center; padding:20px;">
                    No warehouses found
                </td>
            </tr>
        <?php else: ?>
            <?php foreach ($paged_list as $w): ?>
                <tr class="wh-row"
                    data-code="<?= esc_attr($w['code'] ?? '') ?>"
                    data-name="<?= esc_attr($w['name'] ?? '') ?>"
                    data-phone="<?= esc_attr($w['phone'] ?? '') ?>"
                    data-contact="<?= esc_attr($w['contact'] ?? '') ?>"
                >
                    <td>
                        <strong><?= esc_html($w['code'] ?? '') ?></strong>
                    </td>

                    <td><?= esc_html($w['name'] ?? '') ?></td>

                    <td>
                        <?= esc_html($w['formatted_address'] ?? $w['address'] ?? '') ?>
                    </td>

                    <td>
                        <?php if (($w['primary'] ?? 0) == 1): ?>
                            <strong>Default</strong>
                        <?php else: ?>
                            Normal
                        <?php endif; ?>
                    </td>

                    <td>
                        <a
                            href="#"
                            class="button button-small wh-edit-btn"
                        >
                            <i style="margin-right:4px;"></i>
                            Edit
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
                    'prev_text' => '« Previous',
                    'next_text' => 'Next »',
                    'total'     => $pages,
                    'current'   => $current,
                ]);
                ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
include DIR . 'views/modals/warehouse-create.php';
include DIR . 'views/modals/warehouse-edit.php';
?>

<script src="<?= esc_url(URL . 'assets/js/warehouse.js') ?>"></script>
