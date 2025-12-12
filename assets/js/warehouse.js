jQuery(document).ready(function($) {
    console.log('🚀 SuperShip Checkout Blocks loaded');

    let districtData = [];
    let communeData = [];

    // ============================================
    // Hàm chuyển input text thành select dropdown
    // ============================================
    function convertToSelect(selector, placeholder) {
        const $input = $(selector);
        
        if ($input.length && $input.is('input[type="text"]')) {
            const inputId = $input.attr('id');
            const inputName = $input.attr('name');
            const inputValue = $input.val();
            const isRequired = $input.attr('required');
            
            // Tạo select mới
            const $select = $('<select></select>')
                .attr('id', inputId)
                .attr('name', inputName)
                .addClass($input.attr('class'))
                .css({
                    width: '100%',
                    padding: '12px',
                    border: '1px solid #ddd',
                    borderRadius: '4px',
                    fontSize: '16px'
                });
            
            if (isRequired) {
                $select.attr('required', 'required');
            }
            
            // Thêm option mặc định
            $select.append(`<option value="">${placeholder}</option>`);
            
            // Thay thế input bằng select
            $input.replaceWith($select);
            
            console.log(`✅ Converted ${selector} to select`);
            
            return $select;
        }
        
        return $input;
    }

    // ============================================
    // Chờ DOM render xong rồi mới convert
    // ============================================
    setTimeout(function() {
        
        // Convert input text → select
        const $districtSelect = convertToSelect(
            '#shipping-supership\\/district', 
            '-- Chọn quận/huyện --'
        );
        
        const $communeSelect = convertToSelect(
            '#shipping-supership\\/commune', 
            '-- Chọn phường/xã --'
        );
        
        console.log('District select:', $districtSelect.length);
        console.log('Commune select:', $communeSelect.length);
        
    }, 500);

    // ============================================
    // Lắng nghe sự kiện thay đổi tỉnh
    // ============================================
    $(document).on('change', '#shipping-supership\\/province', function() {
        const provinceCode = $(this).val();
        console.log('🏙️ Province changed:', provinceCode);
        
        if (!provinceCode) {
            resetDistrict();
            resetCommune();
            return;
        }
        
        loadDistricts(provinceCode);
    });

    // ============================================
    // Lắng nghe sự kiện thay đổi quận/huyện
    // ============================================
    $(document).on('change', '#shipping-supership\\/district', function() {
        const districtCode = $(this).val();
        console.log('🏘️ District changed:', districtCode);
        
        if (!districtCode) {
            resetCommune();
            return;
        }
        
        loadCommunes(districtCode);
    });

    // ============================================
    // Load danh sách Quận/Huyện
    // ============================================
    function loadDistricts(provinceCode) {
        const $district = $('#shipping-supership\\/district');
        
        $district.html('<option value="">Đang tải...</option>');
        resetCommune();
        
        $.ajax({
            url: supership_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'load_districts',
                province_code: provinceCode
            },
            success: function(response) {
                console.log('✅ Districts loaded:', response);
                
                if (response.success && response.districts) {
                    districtData = response.districts;
                    
                    $district.html('<option value="">-- Chọn quận/huyện --</option>');
                    
                    response.districts.forEach(function(d) {
                        $district.append(
                            `<option value="${d.code}" data-name="${d.name}">${d.name}</option>`
                        );
                    });
                    
                    // Trigger WooCommerce update
                    $(document.body).trigger('update_checkout');
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Load districts failed:', error);
                $district.html('<option value="">-- Lỗi tải dữ liệu --</option>');
            }
        });
    }

    // ============================================
    // Load danh sách Phường/Xã
    // ============================================
    function loadCommunes(districtCode) {
        const $commune = $('#shipping-supership\\/commune');
        
        $commune.html('<option value="">Đang tải...</option>');
        
        $.ajax({
            url: supership_ajax.ajaxurl,
            type: 'POST',
            data: {
                action: 'load_communes',
                district_code: districtCode
            },
            success: function(response) {
                console.log('✅ Communes loaded:', response);
                
                if (response.success && response.communes) {
                    communeData = response.communes;
                    
                    $commune.html('<option value="">-- Chọn phường/xã --</option>');
                    
                    response.communes.forEach(function(c) {
                        $commune.append(
                            `<option value="${c.code}" data-name="${c.name}">${c.name}</option>`
                        );
                    });
                    
                    // Trigger WooCommerce update
                    $(document.body).trigger('update_checkout');
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Load communes failed:', error);
                $commune.html('<option value="">-- Lỗi tải dữ liệu --</option>');
            }
        });
    }

    // ============================================
    // Reset functions
    // ============================================
    function resetDistrict() {
        $('#shipping-supership\\/district')
            .html('<option value="">-- Chọn quận/huyện --</option>');
        districtData = [];
    }

    function resetCommune() {
        $('#shipping-supership\\/commune')
            .html('<option value="">-- Chọn phường/xã --</option>');
        communeData = [];
    }

    // ============================================
    // Lưu tên địa chỉ (không chỉ code)
    // ============================================
    $(document).on('change', '#shipping-supership\\/district, #shipping-supership\\/commune', function() {
        const $this = $(this);
        const selectedName = $this.find('option:selected').data('name');
        
        // Lưu vào hidden field hoặc data attribute để submit
        $this.attr('data-selected-name', selectedName);
        
        console.log('📝 Selected:', selectedName);
    });

});