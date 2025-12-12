<div id="wh-modal-edit" class="wh-modal hidden">
    <div class="wh-modal-content">
        <h2><i class="bi bi-pencil-square"></i> Edit Warehouse</h2>

        <input type="hidden" id="wh_edit_code">

        <table class="form-table">
            <tr>
                <th>Warehouse Name</th>
                <td><input type="text" id="wh_edit_name" class="regular-text"></td>
            </tr>
            <tr>
                <th>Phone</th>
                <td><input type="text" id="wh_edit_phone" class="regular-text"></td>
            </tr>
            <tr>
                <th>Contact</th>
                <td><input type="text" id="wh_edit_contact" class="regular-text"></td>
            </tr>
        </table>

        <div style="text-align:right; margin-top:20px;">
            <button class="button" id="wh-close-edit">Cancel</button>
            <button class="button button-primary" id="wh-btn-update">
                <i class=""></i> Update
            </button>
        </div>
    </div>
</div>
