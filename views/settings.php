<div class="wrap">
    <h1>SuperShip – Cài đặt</h1>

    <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; max-width: 600px;">
        <form method="post">
            <?php wp_nonce_field('save_token'); ?>

            <table class="form-table">
                <tr>
                    <th style="width: 150px;">
                        <label for="token">API Token</label>
                    </th>
                    <td>
                        <textarea id="token"
                            name="token"
                            class="regular-text"
                            style="width: 450px; height: 120px; padding: 8px;"
                            placeholder="Nhập Access Token từ SuperShip"
                            required><?php echo esc_attr(Settings::get_token()); ?></textarea>
                    <p class="description">
                        Lấy API Token từ <a href="https://khachhang.supership.vn/apis" target="_blank">https://khachhang.supership.vn/apis</a>
                    </p>
                    </td>
                </tr>
            </table>

            <p>
                <button type="submit" name="save_token" class="button button-primary">Lưu Token</button>
            </p>
        </form>
    </div>

    <?php
    if (isset($_POST['save_token']) && wp_verify_nonce($_POST['_wpnonce'], 'save_token')) {
        Settings::save_token($_POST['token']);
        echo '<div class="notice notice-success" style="margin-top: 20px;"><p> Đã lưu API Token thành công!</p></div>';
    }
    ?>
</div>