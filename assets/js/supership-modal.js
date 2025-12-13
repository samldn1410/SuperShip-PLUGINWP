jQuery(document).ready(function($) {
    if (typeof modal_ajax === 'undefined') {
        return;
    }

    const modal = $('#config-modal');
    const modalBody = $('#modal-body');
    const createBtn = $('#modal-create-btn');
    const orderId = createBtn.data('order-id');

    // 1. Mở Modal
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
                    createBtn.prop('disabled', false).text('Tạo Đơn SuperShip');
                    const $pickup = modalBody.find('select[name="select_pickup_code"]');
                    if ($pickup.length && $pickup.val()) {
                        $pickup.trigger('change'); // 👈 QUAN TRỌNG
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi tải cấu hình',
                        text: response.data.message || 'Lỗi không xác định'
                    });
                    createBtn.prop('disabled', true);
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi kết nối Server',
                    text: 'Không thể tải cấu hình.'
                });
                createBtn.prop('disabled', true);
            }
        });
    });

    // 2. Đóng Modal
    $('.close-btn').on('click', function() {
        modal.hide();
    });

    $(window).on('click', function(event) {
        if (event.target == modal[0]) {
            modal.hide();
        }
    });

    // 3. Tạo đơn SuperShip
    createBtn.on('click', function(e) {
        e.preventDefault();

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
            Swal.fire({
                icon: 'warning',
                title: 'Thiếu thông tin',
                text: 'Vui lòng điền đầy đủ địa chỉ người nhận.'
            });
            return;
        }

        Swal.fire({
            title: 'Xác nhận tạo đơn?',
            text: "Bạn có chắc chắn muốn tạo đơn SuperShip?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Tạo đơn',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (!result.isConfirmed) return;

            const btn = createBtn;
            btn.prop('disabled', true).text('Đang Tạo Đơn...');

            modalBody.prepend('<div id="loading-overlay"><p>Đang xử lý tạo đơn...</p></div>');

            const form_data = modalBody.find('select, input, textarea').serializeArray().reduce(function(obj, item) {
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
                        Swal.fire({
                            icon: 'success',
                            title: 'Tạo đơn thành công!',
                            html: `Mã đơn: <b>${response.data.code}</b>`
                        }).then(() => {
                            window.location.href = response.data.redirect_url;
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Tạo đơn thất bại',
                            html: response.data.error_detail || 'Lỗi không xác định'
                        });
                        btn.prop('disabled', false).text('Tạo Đơn SuperShip');
                    }
                },
                error: function(xhr, status, error) {
                    $('#loading-overlay').remove();
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi kết nối Server',
                        text: error
                    });
                    btn.prop('disabled', false).text('Tạo Đơn SuperShip');
                }
            });
        });
    });
});

// Hủy đơn
jQuery(document).on('click', '.cancel-order', function(e) {
    e.preventDefault();

    const orderId = jQuery(this).data('order-id');
    const Code = jQuery(this).data('code');

    Swal.fire({
        title: 'Xác nhận hủy đơn?',
        text: 'Bạn chắc chắn muốn hủy đơn SuperShip?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Hủy đơn',
        cancelButtonText: 'Không'
    }).then((result) => {
        if (!result.isConfirmed) return;

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
                    Swal.fire({
                        icon: 'success',
                        title: 'Đã hủy đơn',
                        text: res.data.message
                    }).then(() => location.reload());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Hủy thất bại',
                        text: res.data.message || 'Lỗi không xác định'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi AJAX',
                    text: 'Không thể hủy đơn.'
                });
            }
        });
    });
});

// Làm mới đơn
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

        btn.removeClass("loading").text("Cập nhật đơn");

        Swal.fire({
            icon: 'success',
            title: 'Cập nhật thành công',
            text: response.data.message
        }).then(() => location.reload());

    }).fail(function() {
        btn.removeClass("loading").text("Cập nhật đơn");

        Swal.fire({
            icon: 'error',
            title: 'Lỗi Server',
            text: 'Không thể cập nhật đơn.'
        });
    });
});
jQuery(document).ready(function ($) {

    function toggleBarterExtra() {
        const $checkbox = $('#barter_checkbox');
        const $extra = $('#barter_extra');

        if (!$checkbox.length || !$extra.length) return;

        if ($checkbox.is(':checked')) {
            $extra.show();
        } else {
            $extra.hide();
        }
    }

    /**
     * 1️⃣ Khi modal được load bằng AJAX
     * modal-body được inject HTML sau khi click nút mở modal
     */
    $(document).on('ajaxComplete', function (event, xhr, settings) {
        if (settings.data && settings.data.includes('load_config_modal')) {
            toggleBarterExtra(); // chạy ngay sau khi HTML render
        }
    });

    /**
     * 2️⃣ Khi user tick / bỏ tick checkbox
     */
    $(document).on('change', '#barter_checkbox', function () {
        toggleBarterExtra();
    });
    $(document).on('change', 'select[name="select_pickup_code"]', function () {
    const pickupCode = $(this).val();
    const orderId = $('#modal-create-btn').data('order-id');

    if (!pickupCode) return;

    $('#shipping_preview').html('Đang tính phí...');

    $.post(modal_ajax.ajax_url, {
        action: 'preview_shipping_fee',
        security: modal_ajax.nonce,
        order_id: orderId,
        pickup_code: pickupCode
    }, function (res) {
        if (!res.success) {
            $('#shipping_preview').html(res.data.message);
            return;
        }
       $('#shipping_preview').html(`
            <span class="fee">Phí vận chuyển: ${res.data.fee}</span>
            <span class="sep"> | </span>
            <span class="sub">Phí bảo hiểm: ${res.data.insurance}</span>
        `);
    });
});
});

