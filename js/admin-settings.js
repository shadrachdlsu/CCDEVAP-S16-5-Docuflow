"use strict";

/*
|--------------------------------------------------------------------------
| ADMIN SYSTEM SETTINGS JAVASCRIPT MODULE
|--------------------------------------------------------------------------
| Handles interactive radio button selection for Admin Approval setting on new user
| registrations.
*/

document.addEventListener("DOMContentLoaded", function () {

    /*
    |--------------------------------------------------------------------------
    | DOM ELEMENTS
    |--------------------------------------------------------------------------
    */

    const approvalRadios = document.querySelectorAll('input[name="requireAdminApproval"]');
    const statusBadge = document.getElementById("approvalStatusBadge");

    if (approvalRadios.length === 0) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | EVENT LISTENERS
    |--------------------------------------------------------------------------
    */

    approvalRadios.forEach(function (radio) {

        radio.addEventListener("change", function () {

            const selectedValue = this.value;
            const isEnabled = selectedValue === "1";

            // Disable radios during request
            approvalRadios.forEach(r => r.disabled = true);

            $.ajax({
                url: "../controllers/AdminSettingsController.php",
                type: "POST",
                data: {
                    action: "toggle_admin_approval",
                    enabled: isEnabled ? 1 : 0
                },
                dataType: "json",
                success: function (response) {

                    approvalRadios.forEach(r => r.disabled = false);

                    if (response && response.success) {

                        if (statusBadge) {

                            if (isEnabled) {
                                statusBadge.textContent = "Enabled";
                                statusBadge.className = "status-badge status-active";
                            } else {
                                statusBadge.textContent = "Disabled (Auto-Approve)";
                                statusBadge.className = "status-badge status-inactive";
                            }

                        }

                    } else {

                        alert("Error updating setting: " + (response.error || "Unknown error"));

                        // Revert radio selection
                        approvalRadios.forEach(r => {
                            r.checked = (r.value === (isEnabled ? "0" : "1"));
                        });

                    }

                },
                error: function () {

                    approvalRadios.forEach(r => r.disabled = false);
                    alert("Failed to communicate with server.");

                    // Revert radio selection
                    approvalRadios.forEach(r => {
                        r.checked = (r.value === (isEnabled ? "0" : "1"));
                    });

                }
            });

        });

    });

});
