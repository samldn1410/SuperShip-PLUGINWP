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
        'text' => __('Đã lưu API Token thành công!', 'supership')
    ];
}
?>

<?php if (!empty($msg)) : ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    Swal.fire({
        icon: '<?php echo esc_js($msg['type']); ?>',
        title: '<?php echo esc_js(__('Thành công', 'supership')); ?>',
        text: '<?php echo esc_js($msg['text']); ?>',
        confirmButtonText: 'OK'
    });
});
</script>
<?php endif; ?>

<div class="wrap">
    <h1 style="margin-bottom:20px;">
        <?php echo esc_html__('SuperShip – API Token', 'supership'); ?>
    </h1>

    <div style="
        max-width: 640px;
        margin: 40px auto;
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.06);
    ">
        <form method="post">
            <?php wp_nonce_field('save_token'); ?>

            <div style="margin-bottom:20px;">
                <label for="token" style="font-weight:600; display:block; margin-bottom:8px;">
                    <?php echo esc_html__('API Token', 'supership'); ?>
                </label>

                <div style="position:relative;">
                    <textarea
                        id="token"
                        name="token"
                        style="
                            width:100%;
                            height:140px;
                            padding:12px 42px 12px 12px;
                            font-family: monospace;
                            font-size:14px;
                            border-radius:6px;
                            border:1px solid #ccd0d4;
                        "
                        placeholder="<?php echo esc_attr__('Dán Access Token của SuperShip vào đây', 'supership'); ?>"
                        required
                    ><?php echo esc_textarea(Settings::get_token()); ?></textarea>

                    <!-- Eye icon -->
                    <i
                        id="toggleToken"
                        class="bi bi-eye"
                        style="
                            position:absolute;
                            top:12px;
                            right:12px;
                            cursor:pointer;
                            font-size:20px;
                            color:#555;
                        "
                        title="<?php echo esc_attr__('Hiện / Ẩn Token', 'supership'); ?>"
                    ></i>
                </div>

                <p style="margin-top:8px; color:#666;">
                    <?php echo esc_html__('Lấy API Token của bạn tại:', 'supership'); ?>
                    <a href="https://khachhang.supership.vn/apis" target="_blank">
                        https://khachhang.supership.vn/apis
                    </a>
                </p>
            </div>

            <div style="text-align:center;">
                <button
                    type="submit"
                    name="save_token"
                    class="button button-primary button-large"
                    style="padding:6px 28px;"
                >
                    <?php echo esc_html__('Lưu API Token', 'supership'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const textarea = document.getElementById('token');
    const toggle   = document.getElementById('toggleToken');

    if (!textarea || !toggle) return;

    let hidden = true;

    // Hide token initially
    textarea.style.webkitTextSecurity = 'disc';
    textarea.style.textSecurity = 'disc';

    toggle.addEventListener('click', function () {
        hidden = !hidden;

        if (hidden) {
            textarea.style.webkitTextSecurity = 'disc';
            textarea.style.textSecurity = 'disc';
            toggle.classList.remove('bi-eye-slash');
            toggle.classList.add('bi-eye');
        } else {
            textarea.style.webkitTextSecurity = 'none';
            textarea.style.textSecurity = 'none';
            toggle.classList.remove('bi-eye');
            toggle.classList.add('bi-eye-slash');
        }
    });
});
</script>
