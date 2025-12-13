<?php
if (!defined('ABSPATH')) exit;
$warehouses = Warehouses_Service::get_all();
$default    = Warehouses_Service::get_default();
?>

<div class=" create-order" style="max-width: 800px; margin-top: 20px;">
    <h2><?php _e('Tạo Đơn SuperShip', 'supership'); ?></h2>

    <form method="post" id="order_form">
        <?php wp_nonce_field('create_order', 'nonce'); ?>
        <input type="hidden" name="create_order" value="1">

        <!-- ================== 1. KHO LẤY HÀNG ================== -->
        <div class="section-box">
            <h2 class="title"><?php _e('1. Địa Chỉ Lấy Hàng', 'supership'); ?></h2>

            <table class="form-table">
                <tr>
                    <th><?php _e('Chọn địa chỉ kho', 'supership'); ?></th>
                    <td>
                        <input type="hidden"
                            id="pickup_code"
                            name="pickup_code"
                            value="<?= esc_attr($default['code'] ?? '') ?>">

                        <div id="pickup_display" class="pickup-display-box">
                            <?= esc_html($default
                                ? $default['name'].' - '.$default['formatted_address']
                                : __('Chưa chọn kho', 'supership')) ?>
                        </div>

                        <button type="button"
                                class="button"
                                id="btn-change-pickup"
                                style="margin-top:8px;">
                            <?php _e('Thay đổi kho', 'supership'); ?>
                        </button>
                    </td>
                </tr>
            </table>

            <div id="pickup_manual_fields" style="border-top:1px dashed #ddd;">
                <table class="form-table">
                    <tr>
                        <th><span style="color:red">*</span> <?php _e('Tên Kho Hàng', 'supership'); ?></th>
                        <td><input type="text" id="pickup_name" name="pickup_name" style="width:100%;"></td>
                    </tr>
                    <tr>
                        <th><span style="color:red">*</span> <?php _e('Tên người liên hệ', 'supership'); ?></th>
                        <td><input type="text" id="pickup_contact" name="pickup_contact" style="width:100%;"></td>
                    </tr>
                    <tr>
                        <th><span style="color:red">*</span> <?php _e('SĐT của Điểm Lấy Hàng', 'supership'); ?></th>
                        <td><input type="tel" id="pickup_phone" name="pickup_phone" style="width:100%;"></td>
                    </tr>
                    <tr>
                        <th><span style="color:red">*</span> <?php _e('Địa chỉ Điểm Lấy Hàng', 'supership'); ?></th>
                        <td><input type="text" id="pickup_address" name="pickup_address" style="width:100%;"></td>
                    </tr>
                    <tr>
                        <th><span style="color:red">*</span> <?php _e('Tỉnh / Thành phố', 'supership'); ?></th>
                        <td>
                            <select id="pickup_province" name="pickup_province" style="width:100%;">
                                <option value=""><?php _e('-- Chọn Tỉnh/Thành Phố --', 'supership'); ?></option>
                                <?php foreach (Location_Service::get_provinces() as $p): ?>
                                    <option value="<?= esc_attr($p['code']); ?>">
                                        <?= esc_html($p['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><span style="color:red">*</span> <?php _e('Quận / Huyện', 'supership'); ?></th>
                        <td>
                            <select id="pickup_district" name="pickup_district" style="width:100%;">
                                <option value=""><?php _e('-- Chọn Quận/Huyện --', 'supership'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><span style="color:red">*</span> <?php _e('Phường / Xã', 'supership'); ?></th>
                        <td>
                            <select id="pickup_commune" name="pickup_commune" style="width:100%;">
                                <option value=""><?php _e('-- Chọn Phường/Xã --', 'supership'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
                <div class="checkbox-row" style="margin-top:12px;">
                    <label style="display:inline-flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" id="manual_pickup_toggle">
                        <span><?php _e('Nhập thông tin kho thủ công', 'supership'); ?></span>
                    </label>
                </div>
        <!-- ================== 2. NGƯỜI NHẬN ================== -->
        <div class="section-box">
            <h2 class="title"><?php _e('2. Thông Tin Người Nhận', 'supership'); ?></h2>

            <table class="form-table">
                <tr>
                    <th><span style="color:red">*</span> <?php _e('Tên người nhận', 'supership'); ?></th>
                    <td><input type="text" id="name" name="name" required style="width:100%;"></td>
                </tr>
                <tr>
                    <th><span style="color:red">*</span> <?php _e('Số điện thoại', 'supership'); ?></th>
                    <td><input type="tel" id="phone" name="phone" required style="width:100%;"></td>
                </tr>
                <tr>
                    <th><span style="color:red">*</span> <?php _e('Địa chỉ chi tiết', 'supership'); ?></th>
                    <td><input type="text" id="address" name="address" required style="width:100%;"></td>
                </tr>

                <tr>
                    <th>
                        <span style="color: red;">*</span>
                        <?php _e('Tỉnh/Thành phố', 'supership'); ?>
                    </th>
                    <td>
                        <select id="province" name="province" style="width: 100%;" required>
                            <option value="">
                                <?php _e('-- Chọn tỉnh --', 'supership'); ?>
                            </option>
                            <?php 
                            $provinces = Location_Service::get_provinces();
                            foreach ($provinces as $p):
                            ?>
                                <option value="<?= esc_attr($p['code']) ?>">
                                    <?= esc_html($p['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th>
                        <span style="color: red;">*</span>
                        <?php _e('Quận/Huyện', 'supership'); ?>
                    </th>
                    <td>
                        <select id="district" name="district" style="width: 100%;" required>
                            <option value="">
                                <?php _e('-- Chọn quận/huyện --', 'supership'); ?>
                            </option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th>
                        <span style="color: red;">*</span>
                        <?php _e('Phường/Xã', 'supership'); ?>
                    </th>
                    <td>
                        <select id="commune" name="commune" style="width: 100%;">
                            <option value="">
                                <?php _e('-- Chọn phường/xã --', 'supership'); ?>
                            </option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>

       <div class="section-box">
    <h2 class="title">
        <?php _e('3. Hàng hóa & Kích thước', 'supership'); ?>
    </h2>

    <table class="form-table">
        <tr>
            <th><?php _e('Mã đơn riêng', 'supership'); ?></th>
            <td>
                <input type="text"
                       id="soc"
                       name="soc"
                       class="regular-text"
                       placeholder="<?php esc_attr_e('Mã đơn hàng nội bộ của bạn', 'supership'); ?>"
                       style="width: 100%;">
            </td>
        </tr>

        <tr>
            <th>
                <span style="color: red;">*</span>
                <?php _e('Tên sản phẩm', 'supership'); ?>
            </th>
            <td>
                <input type="text"
                       id="product"
                       name="product"
                       class="regular-text"
                       placeholder="<?php esc_attr_e('VD: Quần áo, Giày dép...', 'supership'); ?>"
                       style="width: 100%;">
            </td>
        </tr>

        <tr>
            <th><?php _e('Giá trị đơn hàng (VND)', 'supership'); ?></th>
            <td>
                <input type="number"
                       id="value"
                       name="value"
                       class="regular-text"
                       min="0"
                       placeholder="0"
                       style="width: 100%;">
            </td>
        </tr>

        <tr>
            <th>
                <span style="color: red;">*</span>
                <?php _e('Khối lượng (gram)', 'supership'); ?>
            </th>
            <td>
                <input type="number"
                       id="weight"
                       name="weight"
                       class="regular-text"
                       value="200"
                       min="1"
                       required
                       placeholder="<?php esc_attr_e('VD: 500', 'supership'); ?>"
                       style="width: 100%;">
            </td>
        </tr>

        <tr>
            <th><?php _e('Kích thước (cm)', 'supership'); ?></th>
            <td>
                <div class="dimension-box">
                    <input type="number"
                           id="length"
                           name="length"
                           min="0"
                           placeholder="<?php esc_attr_e('Dài', 'supership'); ?>">
                    <input type="number"
                           id="width"
                           name="width"
                           min="0"
                           placeholder="<?php esc_attr_e('Rộng', 'supership'); ?>">
                    <input type="number"
                           id="height"
                           name="height"
                           min="0"
                           placeholder="<?php esc_attr_e('Cao', 'supership'); ?>">
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="section-box">
    <h2 class="title">
        <?php _e('4. Thanh toán & Tùy chọn', 'supership'); ?>
    </h2>
    <table class="form-table">
        <tr>
            <th>
                <span style="color: red;">*</span>
                <?php _e('COD (VND)', 'supership'); ?>
            </th>
            <td>
                <input type="number"
                       id="amount"
                       name="amount"
                       class="regular-text"
                       min="0"
                       placeholder="Tiền thu khách"
                       style="width: 100%;">
               
            </td>
        </tr>

        <tr>
            <th>
                <span style="color: red;">*</span>
                <?php _e('Người trả phí', 'supership'); ?>
            </th>
            <td>
                <select id="payer" name="payer" style="width: 100%;">
                    <option value="1">
                        <?php _e('Người gửi', 'supership'); ?>
                    </option>
                    <option value="2">
                        <?php _e('Người nhận', 'supership'); ?>
                    </option>
                </select>
            </td>
        </tr>

        <tr>
            <th>
                <span style="color: red;">*</span>
                <?php _e('Xem/thử hàng', 'supership'); ?>
            </th>
            <td>
                <select id="config" name="config" style="width: 100%;">
                    <option value="1">
                        <?php _e('Cho Xem Hàng Nhưng Không Cho Thử', 'supership'); ?>
                    </option>
                    <option value="2">
                        <?php _e('Cho Thử Hàng', 'supership'); ?>
                    </option>
                    <option value="3">
                        <?php _e('Không Cho Xem Hàng', 'supership'); ?>
                    </option>
                </select>
            </td>
        </tr>

        <tr>
            <th><?php _e('Đổi/Lấy hàng về', 'supership'); ?></th>
            <td>
                <select id="barter" name="barter" style="width: 100%;">
                    <option value="">
                        <?php _e('Không', 'supership'); ?>
                    </option>
                    <option value="1">
                        <?php _e('Có', 'supership'); ?>
                    </option>
                </select>
            </td>
        </tr>

        <tr>
            <th><?php _e('Ghi chú khi giao', 'supership'); ?></th>
            <td>
                <textarea id="note"
                          name="note"
                          class="regular-text"
                          rows="3"
                          placeholder="<?php esc_attr_e('Giao giờ hành chính, gọi trước khi giao,...', 'supership'); ?>"
                          style="width: 100%;"></textarea>
            </td>
        </tr>
    </table>
</div>

<div class="submit">
    <button type="submit" class="button button-primary">
        <?php _e('Tạo đơn', 'supership'); ?>
    </button>
</div>

<div id="pickup-modal" class="pickup-modal">
    <div class="pickup-modal-box">
        <div class="pickup-modal-header">
            <strong><?php _e('Chọn Kho Hàng', 'supership'); ?></strong>
            <span class="pickup-close">×</span>
        </div>

         <div class="pickup-list">
        <?php foreach ($warehouses as $w): ?>
            <label class="pickup-item">
            <input type="radio"
                    name="pickup_radio"
                    value="<?= esc_attr($w['code']); ?>"
                    data-text="<?= esc_attr($w['name'].' - '.$w['formatted_address']); ?>"
                    <?= ($default && $default['code'] === $w['code']) ? 'checked' : '' ?>>
            <div>
                <strong><?= esc_html($w['name']); ?></strong><br>
                <small><?= esc_html($w['formatted_address']); ?></small>
            </div>
            </label>
        <?php endforeach; ?>
        </div>

        <div class="pickup-modal-footer">
            <button type="button" class="button button-primary" id="confirmPickup">
                <?php _e('Xác nhận', 'supership'); ?>
            </button>
        </div>
    </div>
</div>

<style>

.create-order {
    max-width: 900px;
    margin: 20px 0 20px 0 !important;
    padding-right: 20px;
}
.section-box {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    margin-bottom: 20px;
}


.section-box .title {
    margin: 0;
    padding: 12px 16px;
    font-size: 14px;
    font-weight: 600;
    border-bottom: 1px solid #ccd0d4;
    background: #f6f7f7;
}


.section-box .form-table {
    margin: 0;
    width: 100%;
}

.section-box .form-table th {
    width: 220px;
    padding: 12px 16px;
    font-weight: 500;
    vertical-align: middle;
}

.section-box .form-table td {
    padding: 12px 16px;
}


.section-box input[type="text"],
.section-box input[type="tel"],
.section-box input[type="number"],
.section-box select,
.section-box textarea {
    width: 100%;
    max-width: 420px;
    height: 36px;
    padding: 6px 10px;
    border: 1px solid #8c8f94;
    border-radius: 4px;
    background: #fff;
}

.section-box textarea {
    height: auto;
    min-height: 70px;
}

/* Focus giống WP */
.section-box input:focus,
.section-box select:focus,
.section-box textarea:focus {
    border-color: #2271b1;
    box-shadow: 0 0 0 1px #2271b1;
    outline: none;
}


.section-box .description {
    margin-top: 6px;
    color: #646970;
    font-size: 13px;
}


.dimension-box {
    display: flex;
    gap: 10px;
}

.dimension-box input {
    width: 90px !important;
    text-align: center;
}


#pickup_manual_fields {
    padding: 16px;
    border-top: 1px dashed #ccd0d4;
    background: #fcfcfc;
}

.create-order .button-primary.button-large {
    font-size: 14px;
    padding: 8px 24px;
    height: auto;
}
#pickup_manual_fields .form-table {
    margin-top: 0 !important;
}
.pickup-modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.45);
    display: none;
    z-index: 9999;
}

.pickup-modal-box {
    background: #fff;
    width: 520px;
    max-height: 80vh;
    margin: 8% auto;
    border-radius: 12px;
    display: flex;
    flex-direction: column;
}

.pickup-modal-header {
    padding: 12px 16px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
}

.pickup-close {
    cursor: pointer;
    font-size: 20px;
}

.pickup-list {
    padding: 12px;
    overflow-y: auto;
    flex: 1;
}

.pickup-item {
    display: flex;
    gap: 10px;
    padding: 10px;
    cursor: pointer;
    border-radius: 8px;
}

.pickup-item:hover {
    background: #f6f7f7;
}

.pickup-modal-footer {
    padding: 12px;
    border-top: 1px solid #ddd;
    text-align: right;
}
.pickup-display-box {
    width: 100%;
    max-width: 420px;
    padding: 8px 12px;
    border: 1px solid #8c8f94;
    border-radius: 4px;
    background: #f6f7f7;
    color: #1d2327;
    line-height: 1.5;
}
</style>

<script>
jQuery(function($){

    // ===== TOGGLE PICKUP MANUAL FIELDS =====
    function togglePickupByCheckbox() {
        let isManual = $('#manual_pickup_toggle').is(':checked');
        
        if (isManual) {
            // Nhập thủ công - ẩn dropdown chọn kho
            $('#pickup_code').closest('tr').fadeOut(300, function() {
                $('#pickup_manual_fields').slideDown(400, function() {
                    // Callback sau khi animation xong
                    $(this).css('opacity', '1');
                });
            });
            
            // Xóa required của dropdown, thêm required cho manual fields
            $('#pickup_code').removeAttr('required');
            $('#pickup_manual_fields')
                .find('input, select')
                .attr('required', 'required');
                
        } else {
            // Chọn kho - ẩn manual fields
            $('#pickup_manual_fields').slideUp(400, function() {
                $('#pickup_code').closest('tr').fadeIn(300);
            });
            
            // Ngược lại
            $('#pickup_code').attr('required', 'required');
            $('#pickup_manual_fields')
                .find('input, select')
                .removeAttr('required');
        }
    }
    // Checkbox change event
    $('#manual_pickup_toggle').on('change', function() {
        togglePickupByCheckbox();
    });

    // Initial state - mặc định hiện chọn kho, ẩn manual
    $('#pickup_manual_fields').hide();
    $('#pickup_code').closest('tr').show();

    // ===== LOAD DISTRICTS WHEN RECEIVER PROVINCE CHANGES (Ajax) =====
    $('#province').on('change', function(){
        let province_code = $(this).val();
        
        $('#district').html('<option value="">-- Chọn quận/huyện --</option>').trigger('change');
        $('#commune').html('<option value="">-- Chọn phường/xã --</option>');

        if (!province_code) return;

        $.post(ajaxurl, {
            action: 'load_districts',
            province_code: province_code
        }, function(res){
            if (res.districts && res.districts.length > 0) {
                let html = '<option value="">-- Chọn quận/huyện --</option>';
                res.districts.forEach(function(d){
                    html += `<option value="${d.code}">${d.name}</option>`;
                });
                $('#district').html(html);
            }
        });
    });

    // ===== LOAD DISTRICTS WHEN PICKUP PROVINCE CHANGES (Ajax) =====
    $('#pickup_province').on('change', function(){
        let province_code = $(this).val();
        
        $('#pickup_district').html('<option value="">-- Chọn quận/huyện --</option>');

        if (!province_code) return;

        $.post(ajaxurl, {
            action: 'load_districts',
            province_code: province_code
        }, function(res){
            if (res.districts && res.districts.length > 0) {
                let html = '<option value="">-- Chọn quận/huyện --</option>';
                res.districts.forEach(function(d){
                    html += `<option value="${d.code}">${d.name}</option>`;
                });
                $('#pickup_district').html(html);
            }
        });
    });

            // ===== LOAD COMMUNES FOR PICKUP DISTRICT =====
        $('#pickup_district').on('change', function(){
            let district_code = $(this).val();

            $('#pickup_commune').html('<option value="">-- Chọn phường/xã --</option>');

            if (!district_code) return;

            $.post(ajaxurl, {
                action: 'load_communes',
                district_code: district_code
            }, function(res){
                if (res.communes && res.communes.length > 0) {
                    let html = '<option value="">-- Chọn phường/xã --</option>';
                    res.communes.forEach(function(c){
                        html += `<option value="${c.code}">${c.name}</option>`;
                    });
                    $('#pickup_commune').html(html);
                }
            });
        });
    // ===== LOAD COMMUNES WHEN RECEIVER DISTRICT CHANGES (Ajax) =====
    $('#district').on('change', function(){
        let district_code = $(this).val();
        
        $('#commune').html('<option value="">-- Chọn phường/xã --</option>');

        if (!district_code) return;

        $.post(ajaxurl, {
            action: 'load_communes',
            district_code: district_code
        }, function(res){
            if (res.communes && res.communes.length > 0) {
                let html = '<option value="">-- Chọn phường/xã --</option>';
                res.communes.forEach(function(c){
                    html += `<option value="${c.code}">${c.name}</option>`;
                });
                $('#commune').html(html);
            }
        });
    });

    // ===== SUBMIT FORM VALIDATION =====
    $('#order_form').on('submit', function(e){
        e.preventDefault();
        
        // Kiem tra cac truong bat buoc chung
        let required_filled = true;

        if (!$('#name').val()) {
        ssAlert("⚠️ Vui lòng nhập Tên Người Nhận!");
        $('#name').focus();
        return;
    }

    if (!$('#phone').val()) {
        ssAlert("⚠️ Vui lòng nhập Số Điện Thoại Người Nhận!");
        $('#phone').focus();
        return;
    }

    if (!$('#address').val()) {
        ssAlert("⚠️ Vui lòng nhập Địa Chỉ Chi Tiết Người Nhận!");
        $('#address').focus();
        return;
    }

    if (!$('#province').val()) {
        ssAlert("⚠️ Vui lòng chọn Tỉnh/Thành Phố Người Nhận!");
        $('#province').focus();
        return;
    }

    if (!$('#district').val()) {
        ssAlert("⚠️ Vui lòng chọn Quận/Huyện Người Nhận!");
        $('#district').focus();
        return;
    }

    if (!$('#commune').val()) {
        ssAlert("⚠️ Vui lòng chọn Phường/Xã Người Nhận!");
        $('#commune').focus();
        return;
    }

    // ===============================
    // 2. KIỂM TRA KHO LẤY HÀNG
    // ===============================
    let manualPickup = $('#manual_pickup_toggle').is(':checked');

        // Nếu KHÔNG chọn kho → bắt buộc nhập thủ công
        if (manualPickup) {

            if (!$('#pickup_name').val()) {
                ssAlert("⚠️ Vui lòng nhập Tên Kho Lấy Hàng!");
                $('#pickup_name').focus();
                return;
            }

            if (!$('#pickup_phone').val()) {
                ssAlert("⚠️ Vui lòng nhập SĐT Kho Lấy Hàng!");
                $('#pickup_phone').focus();
                return;
            }

            if (!$('#pickup_address').val()) {
                ssAlert("⚠️ Vui lòng nhập Địa Chỉ Kho Lấy Hàng!");
                $('#pickup_address').focus();
                return;
            }

            if (!$('#pickup_province').val()) {
                ssAlert("⚠️ Vui lòng chọn Tỉnh/Thành Kho Lấy Hàng!");
                $('#pickup_province').focus();
                return;
            }

            if (!$('#pickup_district').val()) {
                ssAlert("⚠️ Vui lòng chọn Quận/Huyện Kho Lấy Hàng!");
                $('#pickup_district').focus();
                return;
            }

            if (!$('#pickup_commune').val()) {
                ssAlert("⚠️ Vui lòng chọn Phường/Xã Kho Lấy Hàng!");
                $('#pickup_commune').focus();
                return;
            }
        }

        // ===============================
        // 3. KIỂM TRA KHỐI LƯỢNG
        // ===============================
        if (!$('#weight').val() || $('#weight').val() <= 0) {
            ssAlert("⚠️ Vui lòng nhập Khối Lượng hợp lệ (>0)!");
            $('#weight').focus();
            return;
        }
        
        // Kiem tra cac truong required trong form-table (chi su dung the require tren input)
        $(this).find(':required').each(function(){
            if (!$(this).val()) {
                required_filled = false;
                return false; // Break loop
            }
        });

        if (!required_filled) {
            ssAlert(' Vui lòng điền đầy đủ các trường bắt buộc (*)!');
            return false;
        }

        // Confirm truoc khi submit
        if (confirm(' Bạn chắc chắn muốn tạo đơn hàng này không?')) {
            this.submit();
        }
    });
    togglePickupByCheckbox();
});
jQuery(function($){

    const modal = $('#pickup-modal');

    // Mở modal
    $('#btn-change-pickup').on('click', function(){
        modal.show();
    });

    // Đóng modal
    $('.pickup-close').on('click', function(){
        modal.hide();
    });

    // Xác nhận chọn kho
    $('#confirmPickup').on('click', function(){
    const checked = $('input[name="pickup_radio"]:checked');
    if (!checked.length) { alert('Vui lòng chọn kho'); return; }

    $('#pickup_code').val(checked.val());
    $('#pickup_display').text(checked.data('text'));
    modal.hide();
    });


});

const style = document.createElement('style');
style.textContent = `
    #pickup_manual_fields {
        overflow: hidden;
        transition: opacity 0.3s ease;
    }
    
    /* Smooth fade cho select loading state */
    select:disabled {
        opacity: 0.6;
        cursor: wait;
    }
    
    /* Highlight required fields on focus */
    input:required:focus,
    select:required:focus {
        border-color: #0073aa;
        box-shadow: 0 0 0 1px #0073aa;
    }
    
    /* Animation cho form submit button */
    button[type="submit"]:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }
    
    /* Checkbox label enhancement */
    label[for="manual_pickup_toggle"] {
        cursor: pointer;
        user-select: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border-radius: 4px;
        transition: background-color 0.2s;
    }
    
    label[for="manual_pickup_toggle"]:hover {
        background-color: #f0f0f0;
    }
    
    #manual_pickup_toggle {
        cursor: pointer;
        width: 18px;
        height: 18px;
    }
`;
document.head.appendChild(style);
</script>
