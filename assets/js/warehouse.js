jQuery(document).ready(function($){


  $('#wh-open-create-modal').on('click', function(e){
        e.preventDefault();
        $('#wh-modal-create').removeClass('hidden');
        $('body').css('overflow', 'hidden');
        loadProvinces(); // load tỉnh chỉ 1 lần
        $('#wh_district').html('<option value="">-- Chọn quận/huyện --</option>');
        $('#wh_commune').html('<option value="">-- Chọn phường/xã --</option>');
    });

   
    $('#wh-close-create').on('click', function(){
        $('#wh-modal-create').addClass('hidden');
        $('body').css('overflow', 'auto');
    });
    $('#wh-btn-create').off('click').on('click', function(){

        let data = {
            action: 'wh_create_warehouse',
            name: $('#wh_name').val(),
            phone: $('#wh_phone').val(),
            contact: $('#wh_contact').val(),
            address: $('#wh_address').val(),
            province: $('#wh_province option:selected').data('name'),
            district: $('#wh_district option:selected').data('name'),
            commune:  $('#wh_commune option:selected').data('name'),
            primary: $('#wh_primary').val(),
        };

        $.post(wh_ajax.ajaxurl, data, function(res){
             Swal.fire({
                icon: res.success ? 'success' : 'error',
                title: res.success ? 'Success' : 'Error',
                text: res.message
            }).then(() => {
                if (res.success) location.reload();
            });
        });
    });


    $(document).on('click', '.wh-edit-btn', function(e){
        e.preventDefault();

        let row = $(this).closest('tr');

        $('#wh_edit_code').val(row.data('code'));
        $('#wh_edit_name').val(row.data('name'));
        $('#wh_edit_phone').val(row.data('phone'));
        $('#wh_edit_contact').val(row.data('contact'));

        $('#wh-modal-edit').removeClass('hidden');
        $('body').css('overflow', 'hidden');
    });

    $('#wh-close-edit').on('click', function(){
        $('#wh-modal-edit').addClass('hidden');
        $('body').css('overflow', 'auto');
    });


    // ---- UPDATE WAREHOUSE ----
    $('#wh-btn-update').off('click').on('click', function(){

        let data = {
            action: 'wh_update_warehouse',
            code: $('#wh_edit_code').val(),
            name: $('#wh_edit_name').val(),
            phone: $('#wh_edit_phone').val(),
            contact: $('#wh_edit_contact').val(),
        };

        $.post(wh_ajax.ajaxurl, data, function(res){
           Swal.fire({
                icon: res.success ? 'success' : 'error',
                title: res.success ? 'Updated' : 'Error',
                text: res.message
            }).then(() => {
                if (res.success) location.reload();
            });
        });
    });

    function loadProvinces() {
        $.post(wh_ajax.ajaxurl, { action: 'load_provinces' }, function(res){
            let $p = $('#wh_province');
            $p.html('<option value="">-- Chọn tỉnh --</option>');
            res.provinces.forEach(p => {
               $p.append(`<option value="${p.code}" data-name="${p.name}">${p.name}</option>`);
            });
        });
    }
    $('#wh_province').on('change', function(){

        let province = $(this).val();

        $('#wh_district').html('<option value="">Đang tải...</option>');
        $('#wh_commune').html('<option value="">-- Chọn phường/xã --</option>');

        $.post(wh_ajax.ajaxurl, {
            action: 'load_districts',
            province_code: province
        }, function(res){
            let $d = $('#wh_district');
            $d.html('<option value="">-- Chọn quận/huyện --</option>');
            res.districts.forEach(d => {
               $d.append(`<option value="${d.code}" data-name="${d.name}">${d.name}</option>`);
            });
        });

    });


    /** Load communes */
    $('#wh_district').on('change', function(){

        let district = $(this).val();

        $('#wh_commune').html('<option>Đang tải...</option>');

        $.post(wh_ajax.ajaxurl, {
            action: 'load_communes',
            district_code: district
        }, function(res){
            let $c = $('#wh_commune');
            $c.html('<option value="">-- Chọn phường/xã --</option>');
            res.communes.forEach(c => {
                $c.append(`<option value="${c.code}" data-name="${c.name}">${c.name}</option>`);
            });
        });

    });


});
