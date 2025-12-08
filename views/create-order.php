<?php
if (!defined('ABSPATH')) exit;
?>

<div class="wrap ss-create-order" style="max-width: 800px; margin-top: 20px;">
    <h1> Tạo Đơn SuperShip</h1>

    <form method="post" id="ss_order_form">
        <?php wp_nonce_field('ss_create_order', 'ss_nonce'); ?>
        <input type="hidden" name="ss_create_order" value="1">

        <div class="ss-section-box">
            <h2 class="title">1. Kho Lấy Hàng</h2>
            <table class="form-table">
                <tr>
                    <th>Chọn kho</th>
                    <td>
                        <select id="pickup_code" name="pickup_code" style="width: 100%;">
                            <option value="">-- Không chọn, nhập thủ công --</option>
                            <?php 
                            $warehouses = SS_Warehouses_Service::get_all();
                            $default = SS_Warehouses_Service::get_default();

                            foreach ($warehouses as $w):
                                $sel = ($default && $default['code'] === $w['code']) ? "selected" : "";
                            ?>
                                <option value="<?= esc_attr($w['code']) ?>" <?= $sel ?>>
                                    <?= esc_html($w['name']) ?> - <?= esc_html($w['formatted_address']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">Nếu không chọn, hãy nhập thông tin kho thủ công bên dưới.</p>
                    </td>
                </tr>
            </table>

            <div id="pickup_manual_fields" style="border-top: 1px dashed #ddd; padding-top: 15px; margin-top: 15px;">
                <table class="form-table">
                    <tr>
                        <th><span style="color: red;">*</span> Tên kho (người gửi)</th>
                        <td><input type="text" id="pickup_name" name="pickup_name" class="regular-text" style="width: 100%;"></td>
                    </tr>
                    <tr>
                        <th><span style="color: red;">*</span> SĐT kho</th>
                        <td><input type="tel" id="pickup_phone" name="pickup_phone" class="regular-text" placeholder="0234567890" style="width: 100%;"></td>
                    </tr>
                    <tr>
                        <th><span style="color: red;">*</span> Địa chỉ kho</th>
                        <td><input type="text" id="pickup_address" name="pickup_address" class="regular-text" style="width: 100%;"></td>
                    </tr>
                    <tr>
                        <th><span style="color: red;">*</span> Tỉnh/Thành Phố</th>
                        <td>
                            <select id="pickup_province" name="pickup_province" style="width: 100%;">
                                <option value="">-- Chọn tỉnh --</option>
                                <?php 
                                $provinces = SS_Location_Service::get_provinces();
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
                        <th><span style="color: red;">*</span> Quận/Huyện</th>
                        <td>
                            <select id="pickup_district" name="pickup_district" style="width: 100%;">
                                <option value="">-- Chọn quận/huyện --</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="ss-section-box">
            <h2 class="title">2.  Thông Tin Người Nhận</h2>
            <table class="form-table">
                <tr>
                    <th><span style="color: red;">*</span> Tên người nhận</th>
                    <td><input type="text" id="name" name="name" class="regular-text" required style="width: 100%;"></td>
                </tr>
                <tr>
                    <th><span style="color: red;">*</span> SĐT</th>
                    <td><input type="tel" id="phone" name="phone" class="regular-text" placeholder="0912345678" required style="width: 100%;"></td>
                </tr>
                <tr>
                    <th><span style="color: red;">*</span> Địa chỉ chi tiết</th>
                    <td><input type="text" id="address" name="address" class="regular-text" placeholder="Số nhà, tên đường" required style="width: 100%;"></td>
                </tr>

                <tr>
                    <th><span style="color: red;">*</span> Tỉnh/Thành Phố</th>
                    <td>
                        <select id="province" name="province" style="width: 100%;" required>
                            <option value="">-- Chọn tỉnh --</option>
                            <?php 
                            $provinces = SS_Location_Service::get_provinces();
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
                    <th><span style="color: red;">*</span> Quận/Huyện</th>
                    <td>
                        <select id="district" name="district" style="width: 100%;" required>
                            <option value="">-- Chọn quận/huyện --</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th>Phường/Xã</th>
                    <td>
                        <select id="commune" name="commune" style="width: 100%;">
                            <option value="">-- Chọn phường/xã --</option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>

        <div class="ss-section-box">
            <h2 class="title">3.  Hàng Hóa & Kích Thước</h2>
            <table class="form-table">
                <tr>
                    <th>Mã đơn riêng (SOC)</th>
                    <td>
                        <input type="text" id="soc" name="soc" class="regular-text" placeholder="Mã đơn hàng nội bộ của bạn" style="width: 100%;">
                        <p class="description">Mã tham chiếu đơn hàng nội bộ (nếu có).</p>
                    </td>
                </tr>

                <tr>
                    <th>Mô tả hàng hóa</th>
                    <td><input type="text" id="product" name="product" class="regular-text" placeholder="VD: Quần áo, Giày dép..." style="width: 100%;"></td>
                </tr>

                <tr>
                    <th>Giá trị đơn hàng (VND)</th>
                    <td><input type="number" id="value" name="value" class="regular-text" value="0" min="0" placeholder="0" style="width: 100%;"></td>
                </tr>

                <tr>
                    <th><span style="color: red;">*</span> Khối lượng (gram)</th>
                    <td>
                        <input type="number" id="weight" name="weight" class="regular-text" value="200" min="1" required placeholder="VD: 500" style="width: 100%;">
                    
                    </td>
                </tr>

                <tr>
                    <th>Kích thước (cm)</th>
                    <td>
                        <div class="ss-dimension-box">
                            <input type="number" id="length" name="length" value="0" min="0" placeholder="Dài">
                            <input type="number" id="width" name="width" value="0" min="0" placeholder="Rộng">
                            <input type="number" id="height" name="height" value="0" min="0" placeholder="Cao">
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="ss-section-box">
            <h2 class="title">4. Thanh Toán & Tùy Chọn</h2>
            <table class="form-table">
                <tr>
                    <th>COD Amount (VND)</th>
                    <td>
                        <input type="number" id="amount" name="amount" class="regular-text" value="0" min="0" placeholder="0" style="width: 100%;">
                        <p class="description">Số tiền thu hộ khách hàng.</p>
                    </td>
                </tr>

                <tr>
                    <th>Người trả phí</th>
                    <td>
                        <select id="payer" name="payer" style="width: 100%;">
                            <option value="1"> Người gửi (Shop)</option>
                            <option value="2"> Người nhận (Khách hàng)</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th>Cho xem/thử hàng</th>
                    <td>
                        <select id="config" name="config" style="width: 100%;">
                            <option value="1"> Xem hàng không thử</option>
                            <option value="2"> Được thử hàng</option>
                            <option value="3"> Không cho xem</option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <th>Đổi/Lấy hàng về</th>
                    <td>
                        <select id="barter" name="barter" style="width: 100%;">
                            <option value="">Không</option>
                            <option value="1">Có (Hỗ trợ đổi hàng và trả hàng về)</option>
                        </select>
                        <p class="description">Tùy chọn cho phép đổi hoặc lấy hàng về (nếu có).</p>
                    </td>
                </tr>

                <tr>
                    <th>Ghi chú</th>
                    <td>
                        <textarea id="note" name="note" class="regular-text" rows="3" placeholder="Giao giờ hành chính, gọi trước khi giao,..." style="width: 100%;"></textarea>
                    </td>
                </tr>
            </table>
        </div>

        <div style="margin: 30px 0; padding-top: 20px; border-top: 1px solid #ddd; text-align: center;">
            <button type="submit" class="button button-primary button-large" style="font-size: 18px; padding: 15px 40px; height: auto;">
                ✅ Tạo Đơn Hàng SuperShip
            </button>
        </div>
    </form>
</div>

<style>
.ss-create-order {
    max-width: 850px;
    margin: 20px auto !important;
    padding: 0 10px;
}
/* ===== TITLE ===== */

.ss-create-order .title {
    border-left: 4px solid #0073aa;
    padding-left: 10px;
    margin-bottom: 18px;
    font-size: 1.3em;
    font-weight: 600;
}

/* ===== BOX SECTION ===== */
.ss-section-box {
    background: #fff;
    border: 1px solid #dcdcdc;
    padding: 20px 25px;
    margin-bottom: 28px;
    border-radius: 6px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

/* ===== TABLE FORM ===== */
.ss-section-box .form-table th {
    width: 28%;
    min-width: 140px;
    padding: 10px 10px 10px 0;
    font-weight: 500;
    vertical-align: middle;
}

.ss-section-box .form-table td {
    padding: 10px 0;
}

/* ===== INPUTS ===== */
.ss-section-box input[type="text"],
.ss-section-box input[type="tel"],
.ss-section-box input[type="number"],
.ss-section-box select,
.ss-section-box textarea {
    width: 100%;
    max-width: 330px;
    padding: 6px 10px;
    border-radius: 4px;
    border: 1px solid #ccc;
    box-sizing: border-box;
}

/* ===== TEXTAREA ===== */
.ss-section-box textarea {
    width: 100% !important;
    max-width: 420px;
}

/* ===== ROW WIDTH FIX ===== */
.ss-section-box .form-table td > input.regular-text {
    max-width: 330px !important;
}

/* ===== KÍCH THƯỚC HÀNG HÓA ===== */
.ss-dimension-box {
    display: flex;
    gap: 6px;
    align-items: center;
}

.ss-dimension-box input {
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
.ss-submit-wrap {
    margin: 30px 0;
    padding-top: 20px;
    border-top: 1px solid #ddd;
    text-align: center;
}
.ss-submit-wrap button {
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
            action: 'ss_load_districts',
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
            action: 'ss_load_districts',
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

    // ===== LOAD COMMUNES WHEN RECEIVER DISTRICT CHANGES (Ajax) =====
    $('#district').on('change', function(){
        let district_code = $(this).val();
        
        $('#commune').html('<option value="">-- Chọn phường/xã --</option>');

        if (!district_code) return;

        $.post(ajaxurl, {
            action: 'ss_load_communes',
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
    $('#ss_order_form').on('submit', function(e){
        e.preventDefault();
        
        // Kiem tra cac truong bat buoc chung
        let required_filled = true;
        
        // Kiem tra cac truong required trong form-table (chi su dung the require tren input)
        $(this).find(':required').each(function(){
            if (!$(this).val()) {
                required_filled = false;
                return false; // Break loop
            }
        });

        if (!required_filled) {
            alert('⚠️ Vui lòng điền đầy đủ các trường bắt buộc (*)!');
            return false;
        }

        // Confirm truoc khi submit
        if (confirm('✅ Bạn chắc chắn muốn tạo đơn hàng này không?')) {
            this.submit();
        }
    });
    togglePickupFields();
});
</script>