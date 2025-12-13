<?php
if (!defined('ABSPATH')) exit;

$data = Webhook_API::get_webhook();

$url        = $data['results']['url'] ?? '';
$created_at = $data['results']['created_at'] ?? '';
$updated_at = $data['results']['updated_at'] ?? '';

/**
 * Format datetime theo WordPress locale
 *
 * @param string $datetime ISO datetime
 * @return string
 */
function supership_format_datetime($datetime) {
    if (empty($datetime)) {
        return __('—', 'supership');
    }

    try {
        $dt = new DateTime($datetime);
        $dt->setTimezone(wp_timezone());

        return $dt->format(
            get_option('date_format') . ' ' . get_option('time_format')
        );
    } catch (Exception $e) {
        return __('Không xác định', 'supership');
    }
}
?>

<div class="wrap supership-wrap">

    <h2><?php esc_html_e('Webhook SuperShip', 'supership'); ?></h2>

 

    <table class="widefat striped" style="max-width:900px">
        <tbody>

            <tr>
                <th style="width:220px;">
                    <?php esc_html_e('Webhook URL', 'supership'); ?>
                </th>
                <td>
                    <?php if ($url): ?>
                        <code><?php echo esc_html($url); ?></code>
                    <?php else: ?>
                        <span style="color:#d63638;">
                            <?php esc_html_e('Chưa đăng ký webhook', 'supership'); ?>
                        </span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Thời gian cập nhật', 'supership'); ?></th>
                <td><?php echo esc_html(supership_format_datetime($updated_at)); ?></td>
            </tr>

        </tbody>
    </table>
     <div class="notice notice-warning inline">
        <p>
            <strong><?php esc_html_e('Lưu ý:', 'supership'); ?></strong>
            <?php esc_html_e(
                'Webhook được SuperShip tự động tạo hoặc cập nhật khi bạn lưu API Token.',
                'supership'
            ); ?>
        </p>
    </div>

</div>
