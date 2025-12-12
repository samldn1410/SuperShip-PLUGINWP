<div id="wh-modal-edit" class="wh-modal hidden">
    <div class="wh-modal-content">
        <h2>
            <i class="bi bi-pencil-square"></i>
            <?php esc_html_e('Sửa Kho Hàng', 'supership'); ?>
        </h2>

        <input type="hidden" id="wh_edit_code">

        <table class="form-table">
            <tr>
                <th><?php esc_html_e('Tên Kho Hàng', 'supership'); ?></th>
                <td>
                    <input
                        type="text"
                        id="wh_edit_name"
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
                        id="wh_edit_phone"
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
                        id="wh_edit_contact"
                        class="regular-text"
                        placeholder="<?php esc_attr_e('Nhập tên người liên lạc', 'supership'); ?>"
                    >
                </td>
            </tr>
        </table>

        <div style="text-align:right; margin-top:20px;">
            <button class="button" id="wh-close-edit">
                <?php esc_html_e('Thoát', 'supership'); ?>
            </button>

            <button class="button button-primary" id="wh-btn-update">
                <?php esc_html_e('Cập nhật', 'supership'); ?>
            </button>
        </div>
    </div>
</div>
