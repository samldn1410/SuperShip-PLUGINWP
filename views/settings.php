<div class="wrap">
    <h1>SuperShip – Cài đặt</h1>

    <div style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 5px; max-width: 600px;">
        <form method="post">
            <?php wp_nonce_field('ss_save_token'); ?>

            <table class="form-table">
                <tr>
                    <th style="width: 150px;">
                        <label for="ss_token">Access Token</label>
                    </th>
                    <td>
                        <input type="text" 
                               id="ss_token"
                               name="ss_token"
                               value="<?php echo esc_attr(SS_Settings::get_token()); ?>"
                               class="regular-text"
                               placeholder="Nhập Access Token từ SuperShip"
                               required>
                        <p class="description">
                            Lấy Access Token từ <a href="https://mysupership.vn" target="_blank">mysupership.vn</a>
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
    if (isset($_POST['save_token']) && wp_verify_nonce($_POST['_wpnonce'], 'ss_save_token')) {
        SS_Settings::save_token($_POST['ss_token']);
        echo '<div class="notice notice-success" style="margin-top: 20px;"><p> Đã lưu AccessToken thành công!</p></div>';
    }
    ?>
</div>