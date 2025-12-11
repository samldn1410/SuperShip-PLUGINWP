<div id="wh-modal-create" class="wh-modal hidden">
    <div class="wh-modal-content">
        <h2>Tạo kho hàng</h2>

        <table class="form-table">
            <tr><th>Tên kho</th><td><input type="text" id="wh_name"></td></tr>
            <tr><th>SĐT</th><td><input type="text" id="wh_phone"></td></tr>
            <tr><th>Liên hệ</th><td><input type="text" id="wh_contact"></td></tr>
            <tr><th>Địa chỉ</th><td><input type="text" id="wh_address"></td></tr>

            <tr><th>Tỉnh</th><td><select id="wh_province"></select></td></tr>
            <tr><th>Quận/Huyện</th><td><select id="wh_district"></select></td></tr>
            <tr><th>Phường/Xã</th><td><select id="wh_commune"></select></td></tr>

            <tr>
                <th>Mặc định</th>
                <td>
                    <select id="wh_primary">
                        <option value="1">Kho mặc định</option>
                        <option value="2" selected>Kho thường</option>
                    </select>
                </td>
            </tr>
        </table>

        <button class="button button-primary" id="wh-btn-create">Tạo kho</button>
        <button class="button" id="wh-close-create">Đóng</button>
    </div>
</div>
