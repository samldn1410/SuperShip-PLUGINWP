jQuery(document).ready(function($) {
    if (typeof modal_ajax === 'undefined') {
        return;
    }

    const modal = $('#config-modal');
    const modalBody = $('#modal-body');
    const createBtn = $('#modal-create-btn');
    const orderId = createBtn.data('order-id');

    // 1. Mở Modal và Load nội dung cấu hình (AJAX)
    $('#create-order-modal-btn').on('click', function(e) {
        e.preventDefault();
        
        modal.show();
        modalBody.html('<p style="text-align:center;">Đang tải cấu hình...</p>');
        createBtn.prop('disabled', true).text('Đang tải...');

        $.ajax({
            url: modal_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'load_config_modal',
                security: modal_ajax.nonce,
                order_id: orderId
            },
            success: function(response) {
                if (response.success) {
                    modalBody.html(response.data.html);
                    createBtn.prop('disabled', false).text('✅ Tạo Đơn SuperShip');
                } else {
                    modalBody.html('<p style="color:red; text-align:center;">❌ Lỗi tải cấu hình: ' + (response.data.message || 'Lỗi không xác định') + '</p>');
                    createBtn.prop('disabled', true);
                }
            },
            error: function() {
                modalBody.html('<p style="color:red; text-align:center;">❌ Lỗi kết nối Server khi tải cấu hình.</p>');
                createBtn.prop('disabled', true);
            }
        });
    });

    // 2. Đóng Modal
    $('.close-btn').on('click', function() {
        modal.hide();
    });

    // Đóng Modal khi click ngoài
    $(window).on('click', function(event) {
        if (event.target == modal[0]) {
            modal.hide();
        }
    });

    // 3. Xử lý Tạo Đơn khi click nút trong Modal (AJAX)
    createBtn.on('click', function(e) {
        e.preventDefault();
        
        // Validation cơ bản (kiểm tra các trường địa chỉ có trống không)
        let isValid = true;
        modalBody.find('input[required]').each(function() {
            if ($(this).val().trim() === '') {
                isValid = false;
                $(this).css('border-color', 'red');
            } else {
                $(this).css('border-color', '#ccc');
            }
        });
        
        if (!isValid) {
            alert('Vui lòng điền đầy đủ thông tin địa chỉ người nhận (Tỉnh/TP, Quận/Huyện, Phường/Xã, Địa chỉ chi tiết).');
            return;
        }

        if (!confirm('Bạn có chắc chắn muốn tạo đơn SuperShip với các cấu hình này không?')) {
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true).text('Đang Tạo Đơn...');
        
        // Thêm overlay trong khi tạo đơn
        modalBody.prepend('<div id="loading-overlay"><p>Đang xử lý tạo đơn...</p></div>');
        
        // Lấy dữ liệu từ form trong modal body
        const form_data = modalBody.find('select, input').serializeArray().reduce(function(obj, item) {
            obj[item.name] = item.value;
            return obj;
        }, {});

        $.ajax({
            url: modal_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'create_supership_order_ajax',
                security: modal_ajax.nonce,
                order_id: orderId,
                config_data: form_data
            },
            success: function(response) {
                $('#loading-overlay').remove();
                if (response.success) {
                    alert(response.data.message + ' Mã đơn: ' + response.data.code);
                    window.location.href = response.data.redirect_url; // Redirect để refresh trạng thái
                } else {
                    let details = response.data.details 
                    ? JSON.stringify(response.data.details, null, 2)
                    : (response.data.error_detail || 'Không có chi tiết.');
                    alert(response.data.message + '\nChi tiết: ' + (response.data.error_detail || JSON.stringify(response.data.raw_details,null, 2)));
                    console.error('SuperShip API Error:', response.data.raw_details);
                    btn.prop('disabled', false).text('✅ Tạo Đơn SuperShip');
                }
            },
            error: function(xhr, status, error) {
                $('#loading-overlay').remove();
                alert('Lỗi kết nối Server khi tạo đơn: ' + error);
                btn.prop('disabled', false).text('✅ Tạo Đơn SuperShip');
                console.error('AJAX Error:', xhr.responseText);
            }
        });
    });
});
jQuery(document).on('click', '.cancel-order', function(e) {
    e.preventDefault();

    const orderId = jQuery(this).data('order-id');
    const Code = jQuery(this).data('code');

    if (!confirm("Hủy đơn SuperShip?")) return;

    jQuery.ajax({
        url: modal_ajax.ajax_url,
        type: "POST",
        data: {
            action: "cancel_supership_order",
            security: modal_ajax.nonce,
            order_id: orderId,
            supership_code: Code
        },
        success: function(res) {
            if (res.success) {
                alert(res.data.message);
                location.reload();
            } else {
                alert("Hủy thất bại: " + (res.data.message || "Lỗi"));
            }
        },
        error: function(xhr) {
            alert("Lỗi AJAX khi hủy đơn");
        }
    });
});
jQuery(document).on("click", ".update-order-info", function(e) {
    e.preventDefault();

    let btn = jQuery(this);
    let orderId = btn.data("order-id");

    btn.addClass("loading").text("Đang cập nhật...");

    jQuery.post(modal_ajax.ajax_url, {
        action: "update_order_info",
        security: modal_ajax.nonce,
        order_id: orderId
    }, function(response) {

        btn.removeClass("loading").text("🔄 Cập nhật đơn");

        alert(response.data.message);
        location.reload();
    }).fail(function() {
        btn.removeClass("loading").text("🔄 Cập nhật đơn");
        alert("Lỗi server khi cập nhật thông tin đơn");
    });
});