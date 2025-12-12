<div id="wh-modal-create" class="wh-modal hidden">
    <div class="wh-modal-content">
        <h2>
            <i class="bi bi-plus-circle"></i>
            <?php esc_html_e('Tạo Kho Hàng', 'supership'); ?>
        </h2>

        <table class="form-table">
            <tr>
                <th><?php esc_html_e('Tên Kho Hàng', 'supership'); ?></th>
                <td>
                    <input
                        type="text"
                        id="wh_name"
                        class="regular-text"
                        placeholder="<?php esc_attr_e('Nhập tên kho hàng', 'supership'); ?>"
                    >
                </td>
            </tr>

            <tr>
                <th><?php esc_html_e('Số Điện Thoại', 'supership'); ?></th>
                <td>
                    <input
                        type="text"
                        id="wh_phone"
                        class="regular-text"
                        placeholder="<?php esc_attr_e('Nhập số điện thoại', 'supership'); ?>"
                    >
                </td>
            </tr>

            <tr>
                <th><?php esc_html_e('Tên Người Liên Lạc', 'supership'); ?></th>
                <td>
                    <input
                        type="text"
                        id="wh_contact"
                        class="regular-text"
                        placeholder="<?php esc_attr_e('Nhập tên người liên lạc', 'supership'); ?>"
                    >
                </td>
            </tr>

            <tr>
                <th><?php esc_html_e('Địa Chỉ Kho hàng', 'supership'); ?></th>
                <td>
                    <input
                        type="text"
                        id="wh_address"
                        class="regular-text"
                        placeholder="<?php esc_attr_e('Nhập địa chỉ kho', 'supership'); ?>"
                    >
                </td>
            </tr>

            <tr>
                <th><?php esc_html_e('Tỉnh / Thành Phố', 'supership'); ?></th>
                <td>
                    <select id="wh_province" class="regular-text"></select>
                </td>
            </tr>

            <tr>
                <th><?php esc_html_e('Quận / Huyện', 'supership'); ?></th>
                <td>
                    <select id="wh_district" class="regular-text"></select>
                </td>
            </tr>

            <tr>
                <th><?php esc_html_e('Phường / Xã', 'supership'); ?></th>
                <td>
                    <select id="wh_commune" class="regular-text"></select>
                </td>
            </tr>

            <tr>
                <th><?php esc_html_e('Kho Mặc Định', 'supership'); ?></th>
                <td>
                    <select id="wh_primary" class="regular-text">
                        <option value="1">
                            <?php esc_html_e('Kho Mặc định', 'supership'); ?>
                        </option>
                        <option value="2" selected>
                            <?php esc_html_e('Kho thường', 'supership'); ?>
                        </option>
                    </select>
                </td>
            </tr>
        </table>

        <div style="text-align:right; margin-top:20px;">
            <button class="button" id="wh-close-create">
                <?php esc_html_e('Hủy', 'supership'); ?>
            </button>

            <button class="button button-primary" id="wh-btn-create">
                <?php esc_html_e('Tạo Kho', 'supership'); ?>
            </button>
        </div>
    </div>
</div>
