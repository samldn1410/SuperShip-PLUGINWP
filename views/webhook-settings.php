<?php
if (!defined('ABSPATH')) exit;

// 1️⃣ Tự tạo URL webhook mặc định từ website
$webhook_url = home_url('/wp-json/supership/v1/webhook');

// 2️⃣ Gọi API lấy thông tin webhook hiện tại
$current = Webhook_API::get_webhook();

$current_url    = $current['results']['url'] ?? '';
$current_created = $current['results']['created_at'] ?? '';
$current_updated = $current['results']['updated_at'] ?? '';
$current_status = $current['status'] ?? 'Unknown';

// 3️⃣ Xử lý người dùng nhấn nút cập nhật webhook
if (isset($_POST['update_webhook'])) {
    check_admin_referer('update_webhook_nonce');

    // luôn dùng URL auto → không cho user nhập
    $res = Webhook_API::create_webhook($webhook_url);

    if ($res['status'] === 'Success') {
        $msg = [
            'type' => 'success',
            'text' => 'Cập nhật webhook thành công!'
        ];

        // cập nhật UI
        $current_url     = $res['results']['url'];
        $current_created = $res['results']['created_at'];
        $current_updated = $res['results']['updated_at'];
    } else {
        $msg = [
            'type' => 'error',
            'text' => 'Lỗi cập nhật webhook: ' . $res['message']
        ];
    }
}
?>

<div class="wrap">
    <h1>⚙️ Cấu Hình Webhook SuperShip</h1>

    <?php if (!empty($msg)): ?>
        <div class="notice notice-<?php echo $msg['type']; ?> is-dismissible">
            <p><?php echo esc_html($msg['text']); ?></p>
        </div>
    <?php endif; ?>

    <!-- Webhook hiện tại -->
    <h2>🔌 Webhook đang sử dụng</h2>

    <table class="widefat striped" style="max-width: 800px;">
        <tr>
            <th>URL hiện tại</th>
            <td>
                <?php if ($current_url): ?>
                    <code style="font-size:14px;"><?php echo esc_html($current_url); ?></code>
                <?php else: ?>
                    <span style="color:red;">Chưa đăng ký webhook</span>
                <?php endif; ?>
            </td>
        </tr>

        <?php if ($current_url): ?>
        <tr>
            <th>Created at</th>
            <td><?php echo esc_html($current_created); ?></td>
        </tr>
        <tr>
            <th>Updated at</th>
            <td><?php echo esc_html($current_updated); ?></td>
        </tr>
        <?php endif; ?>

        <tr>
            <th>Trạng thái API</th>
            <td><?php echo esc_html($current_status); ?></td>
        </tr>
    </table>

    <br><hr><br>

    <!-- Nút cập nhật webhook -->
    <h2>🛠 Cập nhật webhook</h2>

    <p>
        SuperShip sẽ gửi trạng thái đơn hàng về URL sau:
    </p>

    <p>
        <code style="font-size:16px; color:#0073aa;">
            <?php echo esc_html($webhook_url); ?>
        </code>
    </p>

    <p>
        Nhấn nút bên dưới để đăng ký / cập nhật webhook với SuperShip.
    </p>

    <form method="post">
        <?php wp_nonce_field('update_webhook_nonce'); ?>

        <button type="submit" name="update_webhook" class="button button-primary button-large">
            🔄 Cập nhật Webhook
        </button>
    </form>

    <br><br>
</div>
