
jQuery(document).ready(function($) {
    
    const $modal = $('#create-order-modal');
    const $form = $('#modal-form');
    const $btn = $('#create-order-btn');
    const $resultDiv = $('#modal-result');

    // 1. Mở Modal & Điền dữ liệu ban đầu
    $btn.on('click', function(e) {
        e.preventDefault();
        // Điền dữ liệu mặc định từ WC vào form
        $('#soc').val($('#modal-initial-soc').val());
        $('#name').val($('#modal-initial-name').val());
        $('#phone').val($('#modal-initial-phone').val());
        $('#address').val($('#modal-initial-address').val());
        $('#amount').val($('#modal-initial-amount').val());
        $('#value').val($('#modal-initial-amount').val());
        $('#weight').val($('#modal-initial-weight-gram').val());
        $resultDiv.empty(); 
        $modal.fadeIn(300);
    });
    // 2. Đóng Modal
    $('.close-btn').on('click', function() {
        $modal.fadeOut(300);
    });
    // Đóng khi click ngoài modal
    $(window).on('click', function(e) {
        if (e.target.id === 'create-order-modal') {
            $modal.fadeOut(300);
        }
    });
    // 3. Xử lý Submit Form (AJAX)
    $form.on('submit', function(e) {
        e.preventDefault();

        if (!$('#weight').val() || $('#weight').val() <= 0) {
            alert("⚠️ Vui lòng nhập Khối Lượng hợp lệ (>0)!");
            $('#weight').focus();
            return;
        }
        
        if (!confirm(' Bạn chắc chắn muốn tạo đơn hàng này không?')) {
            return;
        }

        const $submitBtn = $('#modal-submit-btn');
        $submitBtn.prop('disabled', true).text('Đang Tạo Đơn, vui lòng chờ...');
        $resultDiv.empty();

        $.ajax({
            url: data.ajax_url,
            type: 'POST',
            data: $form.serialize(), 
            success: function(response) {
        
                $resultDiv.html('<div class="notice notice-success is-dismissible"><p> ' + response.data.message + '</p></div>');
               
            },
            error: function(jqXHR) {
                // Thất bại
                const response = jqXHR.responseJSON;
                const message = response?.data?.message || 'Lỗi không xác định.';
                let html = '<div class="notice notice-error is-dismissible"><p>' + message + '</p>';

                if (response?.data?.details) {
                    html += '<pre style="max-height: 200px; overflow: auto;">' + JSON.stringify(response.data.details, null, 2) + '</pre>';
                }
                html += '</div>';
                
                $resultDiv.html(html);
            },
            complete: function() {
                $submitBtn.prop('disabled', false).text(' Tạo Đơn Hàng SuperShip');
            }
        });
    });
    
    // ** Giữ nguyên các hàm load_districts/load_communes cũ của bạn nếu cần **
});