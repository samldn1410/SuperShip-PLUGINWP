<?php
if (!defined('ABSPATH')) exit;
?>
<div class="wrap create-order" style="max-width: 800px; margin-top: 20px;">
    <h1><?php _e('Tạo Đơn SuperShip', 'supership'); ?></h1>

    <form method="post" id="order_form">
        <?php wp_nonce_field('create_order', 'nonce'); ?>
        <input type="hidden" name="create_order" value="1">

        <!-- ================== 1. KHO LẤY HÀNG ================== -->
        <div class="section-box">
            <h2 class="title"><?php _e('1. Kho Lấy Hàng', 'supership'); ?></h2>

            <table class="form-table">
                <tr>
                    <th><?php _e('Chọn kho', 'supership'); ?></th>
                    <td>
                        <select id="pickup_code" name="pickup_code" style="width:100%;">
                            <option value="">
                                <?php _e('-- Không chọn, nhập thủ công --', 'supership'); ?>
                            </option>
                            <?php
                            $warehouses = Warehouses_Service::get_all();
                            $default = Warehouses_Service::get_default();
                            foreach ($warehouses as $w):
                                $sel = ($default && $default['code'] === $w['code']) ? 'selected' : '';
                            ?>
                                <option value="<?= esc_attr($w['code']); ?>" <?= $sel; ?>>
                                    <?= esc_html($w['name']); ?> - <?= esc_html($w['formatted_address']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">
                            <?php _e('Nếu không chọn, hãy nhập thông tin kho thủ công bên dưới.', 'supership'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <div id="pickup_manual_fields" style="border-top:1px dashed #ddd;padding-top:15px;margin-top:15px;">
                <table class="form-table">
                    <tr>
                        <th><span style="color:red">*</span> <?php _e('Tên kho (người gửi)', 'supership'); ?></th>
                        <td><input type="text" id="pickup_name" name="pickup_name" style="width:100%;"></td>
                    </tr>
                    <tr>
                        <th><span style="color:red">*</span> <?php _e('SĐT kho', 'supership'); ?></th>
                        <td><input type="tel" id="pickup_phone" name="pickup_phone" style="width:100%;"></td>
                    </tr>
                    <tr>
                        <th><span style="color:red">*</span> <?php _e('Địa chỉ kho', 'supership'); ?></th>
                        <td><input type="text" id="pickup_address" name="pickup_address" style="width:100%;"></td>
                    </tr>
                    <tr>
                        <th><span style="color:red">*</span> <?php _e('Tỉnh / Thành phố', 'supership'); ?></th>
                        <td>
                            <select id="pickup_province" name="pickup_province" style="width:100%;">
                                <option value=""><?php _e('-- Chọn tỉnh --', 'supership'); ?></option>
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
                                <option value=""><?php _e('-- Chọn quận/huyện --', 'supership'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><span style="color:red">*</span> <?php _e('Phường / Xã', 'supership'); ?></th>
                        <td>
                            <select id="pickup_commune" name="pickup_commune" style="width:100%;">
                                <option value=""><?php _e('-- Chọn phường/xã --', 'supership'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
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
            <th><?php _e('Mã đơn riêng (SOC)', 'supership'); ?></th>
            <td>
                <input type="text"
                       id="soc"
                       name="soc"
                       class="regular-text"
                       placeholder="<?php esc_attr_e('Mã đơn hàng nội bộ của bạn', 'supership'); ?>"
                       style="width: 100%;">
                <p class="description">
                    <?php _e('Mã tham chiếu đơn hàng nội bộ (nếu có).', 'supership'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th>
                <span style="color: red;">*</span>
                <?php _e('Thông tin sản phẩm', 'supership'); ?>
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
                           value="0"
                           min="0"
                           placeholder="<?php esc_attr_e('Dài', 'supership'); ?>">
                    <input type="number"
                           id="width"
                           name="width"
                           value="0"
                           min="0"
                           placeholder="<?php esc_attr_e('Rộng', 'supership'); ?>">
                    <input type="number"
                           id="height"
                           name="height"
                           value="0"
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
                       placeholder="0"
                       style="width: 100%;">
                <p class="description">
                    <?php _e('Số tiền thu hộ khách hàng.', 'supership'); ?>
                </p>
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
                        <?php _e('Người gửi (Shop)', 'supership'); ?>
                    </option>
                    <option value="2">
                        <?php _e('Người nhận (Khách hàng)', 'supership'); ?>
                    </option>
                </select>
            </td>
        </tr>

        <tr>
            <th>
                <span style="color: red;">*</span>
                <?php _e('Cho xem thử hàng', 'supership'); ?>
            </th>
            <td>
                <select id="config" name="config" style="width: 100%;">
                    <option value="1">
                        <?php _e('Xem hàng không thử', 'supership'); ?>
                    </option>
                    <option value="2">
                        <?php _e('Được thử hàng', 'supership'); ?>
                    </option>
                    <option value="3">
                        <?php _e('Không cho xem', 'supership'); ?>
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
                        <?php _e('Có (Hỗ trợ đổi hàng và trả hàng về)', 'supership'); ?>
                    </option>
                </select>
                <p class="description">
                    <?php _e('Tùy chọn cho phép đổi hoặc lấy hàng về (nếu có).', 'supership'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th><?php _e('Ghi chú', 'supership'); ?></th>
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

<div style="margin: 30px 0; padding-top: 20px; border-top: 1px solid #ddd; text-align: center;">
    <button type="submit"
            class="button button-primary button-large"
            style="font-size: 18px; padding: 15px 40px; height: auto;">
        <?php _e('Tạo đơn hàng SuperShip', 'supership'); ?>
    </button>
</div>

<style>
.create-order {
    max-width: 850px;
    margin: 20px auto !important;
    padding: 0 10px;
}
/* ===== TITLE ===== */

.create-order .title {
    border-left: 4px solid #0073aa;
    padding-left: 10px;
    margin-bottom: 18px;
    font-size: 1.3em;
    font-weight: 600;
}

/* ===== BOX SECTION ===== */
.section-box {
    background: #fff;
    border: 1px solid #dcdcdc;
    padding: 20px 25px;
    margin-bottom: 28px;
    border-radius: 6px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

/* ===== TABLE FORM ===== */
.section-box .form-table th {
    width: 28%;
    min-width: 140px;
    padding: 10px 10px 10px 0;
    font-weight: 500;
    vertical-align: middle;
}

.section-box .form-table td {
    padding: 10px 0;
}

/* ===== INPUTS ===== */
.section-box input[type="text"],
.section-box input[type="tel"],
.section-box input[type="number"],
.section-box select,
.section-box textarea {
    width: 100%;
    max-width: 330px;
    padding: 6px 10px;
    border-radius: 4px;
    border: 1px solid #ccc;
    box-sizing: border-box;
}

/* ===== TEXTAREA ===== */
.section-box textarea {
    width: 100% !important;
    max-width: 420px;
}

/* ===== ROW WIDTH FIX ===== */
.section-box .form-table td > input.regular-text {
    max-width: 330px !important;
}

/* ===== KÍCH THƯỚC HÀNG HÓA ===== */
.dimension-box input {
    width: 80px !important;
    min-width: 80px !important;
    max-width: 80px !important;
    text-align: center;
}

.dimension-box input {
    width: 70px !important;
    text-align: center;
}

/* ===== PICKUP MANUAL BOX ===== */
#pickup_manual_fields {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px dashed #ccc;
}

/* ===== SUBMIT BUTTON ===== */
.submit-wrap {
    margin: 30px 0;
    padding-top: 20px;
    border-top: 1px solid #ddd;
    text-align: center;
}
.submit-wrap button {
    font-size: 18px;
    padding: 14px 36px;
    height: auto;
}

</style>

<script>
jQuery(function($){

    // ===== TOGGLE PICKUP MANUAL FIELDS =====
    function togglePickupFields() {
        let code = $('#pickup_code').val();
        let $manualFields = $('#pickup_manual_fields');
        
        // Cac truong bat buoc khi KHONG chon kho
        let requiredFields = ['pickup_name', 'pickup_phone', 'pickup_address', 'pickup_province', 'pickup_district'];

        if (code) {
            // Co chon kho → an form nhap thu cong
            $manualFields.slideUp(200);
            requiredFields.forEach(id => $('#' + id).removeAttr('required'));
        } else {
            // Khong chon kho → hien form nhap thu cong
            $manualFields.slideDown(200);
            requiredFields.forEach(id => $('#' + id).attr('required', 'required'));
        }
    }
    
    // Su kien change cho Chọn kho
    $('#pickup_code').on('change', togglePickupFields);

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
    let pickup_code = $('#pickup_code').val();

        // Nếu KHÔNG chọn kho → bắt buộc nhập thủ công
        if (!pickup_code) {

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
    togglePickupFields();
});
</script>