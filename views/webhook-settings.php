<?php
if (!defined('ABSPATH')) exit;

$webhook_url = home_url('/wp-json/supership/v1/webhook');
// $webhook_url = 'https://moho.com.vn/';

// 2️⃣ Gọi API lấy thông tin webhook hiện tại
$current = Webhook_API::get_webhook();

$current_url     = $current['results']['url'] ?? '';
$current_created = $current['results']['created_at'] ?? '';
$current_updated = $current['results']['updated_at'] ?? '';
$current_status  = $current['status'] ?? __('Không xác định', 'supership');

if (isset($_POST['update_webhook'])) {
    check_admin_referer('update_webhook_nonce');

    $res = Webhook_API::create_webhook($webhook_url);

    if ($res['status'] === 'Success') {
        $msg = [
            'type' => 'success',
            'text' => __('Cập nhật webhook thành công!', 'supership')
        ];

        // cập nhật UI
        $current_url     = $res['results']['url'] ?? '';
        $current_created = $res['results']['created_at'] ?? '';
        $current_updated = $res['results']['updated_at'] ?? '';
    } else {
        $msg = [
            'type' => 'error',
            'text' => sprintf(
                __('Lỗi cập nhật webhook: %s', 'supership'),
                $res['message'] ?? ''
            )
        ];
    }
}
?>

<?php if (!empty($msg)) : ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: '<?php echo esc_js($msg['type']); ?>',
        title: '<?php echo esc_js($msg['type'] === 'success'
            ? __('Thành công', 'supership')
            : __('Lỗi', 'supership')); ?>',
        text: '<?php echo esc_js($msg['text']); ?>',
        confirmButtonText: '<?php echo esc_js(__('OK', 'supership')); ?>'
    });
});
</script>
<?php endif; ?>

<div class="wrap supership-wrap">

    <h1 class="supership-title">
        <i class="bi bi-plug-fill"></i>
        <?php esc_html_e('Cấu Hình Webhook SuperShip', 'supership'); ?>
    </h1>

    <!-- CURRENT WEBHOOK -->
    <div class="supership-card">
        <h2>
            <i class="bi bi-link-45deg"></i>
            <?php esc_html_e('Webhook Hiện Tại', 'supership'); ?>
        </h2>

        <table class="widefat striped supership-table">
            <tr>
                <th><?php esc_html_e('Webhook URL', 'supership'); ?></th>
                <td>
                    <?php if ($current_url): ?>
                        <code><?php echo esc_html($current_url); ?></code>
                    <?php else: ?>
                        <span class="supership-badge danger">
                            <i class="bi bi-x-circle"></i>
                            <?php esc_html_e('Chưa đăng ký', 'supership'); ?>
                        </span>
                    <?php endif; ?>
                </td>
            </tr>

            <?php if ($current_url): ?>
            <tr>
                <th><?php esc_html_e('Thời Gian Tạo', 'supership'); ?></th>
                <td><?php echo esc_html($current_created); ?></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Thời Gian Cập Nhật', 'supership'); ?></th>
                <td><?php echo esc_html($current_updated); ?></td>
            </tr>
            <?php endif; ?>

            <tr>
                <th><?php esc_html_e('Trạng Thái API', 'supership'); ?></th>
                <td>
                    <span class="supership-badge success">
                        <i class="bi bi-check-circle"></i>
                        <?php echo esc_html($current_status); ?>
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <!-- UPDATE WEBHOOK -->
    <div class="supership-card">
        <h2>
            <i class="bi bi-arrow-repeat"></i>
            <?php esc_html_e('Cập Nhật Webhook', 'supership'); ?>
        </h2>

        <p>
            <?php esc_html_e('SuperShip sẽ gửi cập nhật đơn hàng về địa chỉ:', 'supership'); ?>
        </p>

        <p class="supership-url">
            <i class="bi bi-globe"></i>
            <?php echo esc_html($webhook_url); ?>
        </p>

        <form method="post">
            <?php wp_nonce_field('update_webhook_nonce'); ?>

            <button
                type="submit"
                name="update_webhook"
                class="button button-primary button-large supership-btn"
            >
                <i class="bi bi-cloud-arrow-up-fill"></i>
                <?php esc_html_e('Cập Nhật Webhook', 'supership'); ?>
            </button>
        </form>
    </div>

</div>
