<div id="wh-modal-create" class="wh-modal hidden">
    <div class="wh-modal-content">
        <h2><i class="bi bi-plus-circle"></i> Create Warehouse</h2>

        <table class="form-table">
            <tr><th>Warehouse Name</th><td><input type="text" id="wh_name" class="regular-text"></td></tr>
            <tr><th>Phone</th><td><input type="text" id="wh_phone" class="regular-text"></td></tr>
            <tr><th>Contact</th><td><input type="text" id="wh_contact" class="regular-text"></td></tr>
            <tr><th>Address</th><td><input type="text" id="wh_address" class="regular-text"></td></tr>

            <tr><th>Province</th><td><select id="wh_province" class="regular-text"></select></td></tr>
            <tr><th>District</th><td><select id="wh_district" class="regular-text"></select></td></tr>
            <tr><th>Commune</th><td><select id="wh_commune" class="regular-text"></select></td></tr>

            <tr>
                <th>Primary</th>
                <td>
                    <select id="wh_primary" class="regular-text">
                        <option value="1">Default</option>
                        <option value="2" selected>Normal</option>
                    </select>
                </td>
            </tr>
        </table>

        <div style="text-align:right; margin-top:20px;">
            <button class="button" id="wh-close-create">Cancel</button>
            <button class="button button-primary" id="wh-btn-create">
                <i class=""></i> Create
            </button>
        </div>
    </div>
</div>
