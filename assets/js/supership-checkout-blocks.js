jQuery(function ($) {
    console.log("Supership Checkout JS Loaded");

    function log() {
        console.log.apply(console, arguments);
    }

    /**
     * ======== 1) KHI CHỌN TỈNH ========
     */
    $(document).on("change", "select#shipping-supership-province", function () {

        let province_code = $(this).val();
        log("Province selected:", province_code);

        // Reset huyện + xã
        const districtSelect = $("select#shipping-supership-district");
        const communeSelect  = $("select#shipping-supership-commune");

        districtSelect.html('<option value="">Đang tải...</option>');
        communeSelect.html('<option value="">Chọn phường/xã</option>');

        // Gửi AJAX request
        $.post(
            ajax_object.ajax_url,
            {
                action: "load_districts",
                province_code: province_code
            }
        )
        .done(function (res) {

            log("📥 District response:", res);

            // Nếu API lỗi thì không được để field trống -> avoid "Invalid district"
            if (!res || !res.districts) {
                districtSelect.html('<option value="">Không có dữ liệu</option>');
                return;
            }

            let html = '<option value="">Chọn quận/huyện</option>';
            res.districts.forEach(function (d) {
                html += `<option value="${d.code}">${d.name}</option>`;
            });

            districtSelect.html(html);
        })
        .fail(function (err) {
            console.error("AJAX district error:", err);
        });
    });

    /**
     * ======== 2) KHI CHỌN HUYỆN ========
     */
    $(document).on("change", "select#shipping-supership-district", function () {

        let district_code = $(this).val();
        log("District selected:", district_code);

        const communeSelect  = $("select#shipping-supership-commune");
        communeSelect.html('<option value="">Đang tải...</option>');

        $.post(
            ajax_object.ajax_url,
            {
                action: "load_communes",
                district_code: district_code
            }
        )
        .done(function (res) {

            log("📥 Commune response:", res);

            if (!res || !res.communes) {
                communeSelect.html('<option value="">Không có dữ liệu</option>');
                return;
            }

            let html = '<option value="">Chọn phường/xã</option>';
            res.communes.forEach(function (c) {
                html += `<option value="${c.code}">${c.name}</option>`;
            });

            communeSelect.html(html);
        })
        .fail(function (err) {
            console.error("AJAX commune error:", err);
        });
    });
});
