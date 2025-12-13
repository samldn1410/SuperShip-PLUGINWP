<?php
if (!defined('ABSPATH')) exit;

$msg = null;

// Handle submit
if (isset($_POST['save_token']) && wp_verify_nonce($_POST['_wpnonce'], 'save_token')) {
    $result = Settings::save_token($_POST['token']);

    if ($result === 'need_address') {
        wp_safe_redirect(admin_url('admin.php?page=wc-settings&tab=general'));
        exit;
    }

    $msg = [
        'type' => 'success',
        'text' => __('Đã lưu API Token thành công.', 'supership')
    ];
}
?>

<?php if (!empty($msg)) : ?>
    <div class="notice notice-success is-dismissible">
        <p><?php echo esc_html($msg['text']); ?></p>
    </div>
<?php endif; ?>

<h2><?php esc_html_e('Cấu hình API Token', 'supership'); ?></h2>


<form method="post">
    <?php wp_nonce_field('save_token'); ?>

    <table class="form-table">
        <tbody>
            <tr>
                <th scope="row">
                    <label for="token"><?php esc_html_e('API Token', 'supership'); ?></label>
                </th>
                <td>
                    <textarea
                        id="token"
                        name="token"
                        rows="5"
                        cols="50"
                        class="large-text code"
                        placeholder="<?php esc_attr_e('Dán Access Token của SuperShip vào đây', 'supership'); ?>"
                        required
                    ><?php echo esc_textarea(Settings::get_token()); ?></textarea>

                    <p class="description">
                        <?php esc_html_e('Lấy API Token tại:', 'supership'); ?>
                        <a href="https://khachhang.supership.vn/apis" target="_blank">
                            https://khachhang.supership.vn/apis
                        </a>
                    </p>
                </td>
            </tr>
        </tbody>
    </table>

    <p class="submit">
        <button type="submit" name="save_token" class="button-primary">
            <?php esc_html_e('Lưu thay đổi', 'supership'); ?>
        </button>
    </p>
</form>
