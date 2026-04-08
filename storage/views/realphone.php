<?php if (!defined('APP_VERSION')) die("Yo, what's up?"); ?>
<!DOCTYPE html>
<html lang="<?= ACTIVE_LANG ?>">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
        <meta name="theme-color" content="#fff">

        <meta name="description" content="<?= site_settings("site_description") ?>">
        <meta name="keywords" content="<?= site_settings("site_keywords") ?>">

        <link rel="icon" href="<?= site_settings("logomark") ? site_settings("logomark") : APPURL."/assets/img/logomark.png" ?>" type="image/x-icon">
        <link rel="shortcut icon" href="<?= site_settings("logomark") ? site_settings("logomark") : APPURL."/assets/img/logomark.png" ?>" type="image/x-icon">

        <link rel="stylesheet" type="text/css" href="<?= APPURL."/assets/css/plugins.css?v=".VERSION ?>">
        <link rel="stylesheet" type="text/css" href="<?= APPURL."/assets/css/core.css?v=".VERSION ?>">
        <link rel="stylesheet" type="text/css" href="<?= APPURL."/inc/plugins/rpa-manager/assets/css/realphone.css?v=".VERSION ?>">
        <script src="https://cdn.tailwindcss.com"></script>
        <script src="https://unpkg.com/heroicons@2.0.18/24/outline/esm/index.js"></script>

        <title>Real Phone Management</title>
    </head>

    <body class="bg-gray-50">
        <?php 
            $TopBar = new stdClass;
            $TopBar->title = __("Real Phone Management");
            $TopBar->btn = false;
            require_once(APPPATH.'/views/fragments/topbar.fragment.php'); 
        ?>

        <!-- Toast Notification Container -->
        <div id="toast-container" class="fixed top-4 right-4 z-50"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 mt-16">
            <div class="flex justify-between items-center mb-6 animate-slide-in">
                <div class="flex items-center gap-2">
                    <a href="<?= APPURL ?>/dashboard" 
                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 hover-pulse">
                        <svg class="w-5 h-5 mr-2 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        <?= __("Return to Dashboard") ?>
                    </a>
                </div>
            </div>

            <?php require_once(APPPATH.'/views/fragments/realphone.fragment.php'); ?>
        </div>

        <script type="text/javascript" src="<?= APPURL."/assets/js/plugins.js?v=".VERSION ?>"></script>
        <?php require_once(APPPATH.'/inc/js-locale.inc.php'); ?>
        <script type="text/javascript" src="<?= APPURL."/assets/js/core.js?v=".VERSION ?>"></script>
        <script type="text/javascript">
            window.RPA_USER_FARMS     = <?= json_encode(array_values($AllUserFarms ?? []), JSON_UNESCAPED_UNICODE) ?>;
            window.RPA_USER_FARMS_API = '<?= APPURL ?>/e/rpa-manager/user-farms-api/';
        </script>
        <script type="text/javascript">
            function showAlert(message, type = 'error') {
                const toast = document.createElement('div');
                toast.className = `transform transition-all duration-300 ease-out scale-95 opacity-0 ${
                    type === 'error' 
                        ? 'bg-red-100 border-l-4 border-red-500 text-red-700' 
                        : 'bg-green-100 border-l-4 border-green-500 text-green-700'
                } p-4 rounded-r-lg shadow-md mb-4 flex items-center justify-between`;
                
                toast.innerHTML = `
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            ${type === 'error' 
                                ? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'
                                : '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />'
                            }
                        </svg>
                        <span class="text-sm font-medium">${message}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="ml-4 inline-flex text-gray-400 hover:text-gray-500 focus:outline-none">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                `;

                document.getElementById('toast-container').appendChild(toast);
                
                // Trigger animation
                requestAnimationFrame(() => {
                    toast.classList.remove('scale-95', 'opacity-0');
                    toast.classList.add('scale-100', 'opacity-100');
                });

                // Auto remove after 5 seconds
                setTimeout(() => {
                    if (toast.parentElement) {
                        toast.classList.add('scale-95', 'opacity-0');
                        setTimeout(() => toast.remove(), 300);
                    }
                }, 5000);
            }

            $(document).ready(function(){
                // Add animation classes to elements
                $('.device-card').addClass('animate-fade-in');
                
                // Add account button click
                $(".js-assign-account").on("click", function() {
                    const deviceId = $(this).data("device-id");
                    $("#selected-device-id").val(deviceId);
                    $("#assignment-modal").addClass("is-active");
                    
                    // Reset radio selection and button state
                    $('input[name="account"]').prop('checked', false);
                    $('#confirm-assignment').prop('disabled', true);
                });

                // Handle radio button selection
                $('input[name="account"]').on('change', function() {
                    $('#confirm-assignment').prop('disabled', !$(this).is(':checked'));
                });

                // Remove account button click
                $(".js-remove-account").on("click", function() {
                    const username = $(this).data("username");
                    const deviceId = $(this).data("device-id");
                    
                    if (confirm("<?= __('Are you sure you want to remove this account from the device?') ?>")) {
                        const $button = $(this);
                        $button.addClass('opacity-50 cursor-not-allowed').prop('disabled', true);
                        
                        $.ajax({
                            url: "<?= APPURL."/realphone" ?>",
                            type: "POST",
                            dataType: "json",
                            data: {
                                action: "remove-account",
                                username: username,
                                device_id: deviceId
                            },
                            success: function(resp) {
                                if (resp.result == 1) {
                                    location.reload();
                                } else {
                                    showAlert(resp.msg);
                                    $button.removeClass('opacity-50 cursor-not-allowed').prop('disabled', false);
                                }
                            },
                            error: function() {
                                showAlert("<?= __('An error occurred. Please try again.') ?>");
                                $button.removeClass('opacity-50 cursor-not-allowed').prop('disabled', false);
                            }
                        });
                    }
                });

                // Assignment confirmation
                $("#confirm-assignment").on("click", function() {
                    const deviceId = $("#selected-device-id").val();
                    const accountId = $('input[name="account"]:checked').val();
                    
                    if (!deviceId || !accountId) {
                        showAlert("<?= __('Please select an account') ?>");
                        return;
                    }

                    const $button = $(this);
                    $button.addClass('opacity-50 cursor-not-allowed').prop('disabled', true);
                    
                    $.ajax({
                        url: "<?= APPURL."/realphone" ?>",
                        type: "POST",
                        dataType: "json",
                        data: {
                            action: "assign-account",
                            device_id: deviceId,
                            account_id: accountId
                        },
                        success: function(resp) {
                            if (resp.result == 1) {
                                location.reload();
                            } else {
                                showAlert(resp.msg);
                                $button.removeClass('opacity-50 cursor-not-allowed').prop('disabled', false);
                            }
                        },
                        error: function() {
                            showAlert("<?= __('An error occurred. Please try again.') ?>");
                            $button.removeClass('opacity-50 cursor-not-allowed').prop('disabled', false);
                        }
                    });
                });

                // Login to farm button click
                $(".js-login-to-farm").on("click", function() {
                    const username = $(this).data("username");
                    const deviceId = $(this).data("deviceId");
                    const accountId = $(this).data("accountId");
                    const $button = $(this);

                    if (!accountId) {
                        showAlert("<?= __('Account not found') ?>", 'error');
                        return;
                    }

                    // Disable button and show loading state
                    $button.prop('disabled', true).addClass('opacity-50');
                    $button.find('svg').addClass('animate-spin');

                    $.ajax({
                        url: "<?= APPURL."/realphone" ?>",
                        type: "POST",
                        dataType: "json",
                        data: {
                            action: "login-account",
                            account_id: accountId,
                            device_id: deviceId
                        },
                        success: function(resp) {
                            if (resp.result == 1) {
                                showAlert(resp.msg || "Login successful", 'success');
                            } else {
                                showAlert(resp.msg || "Login failed", 'error');
                            }
                        },
                        error: function() {
                            showAlert("<?= __('An error occurred while processing your request') ?>", 'error');
                        },
                        complete: function() {
                            // Re-enable button and remove loading state
                            $button.prop('disabled', false).removeClass('opacity-50');
                            $button.find('svg').removeClass('animate-spin');
                        }
                    });
                });

               // View screen — URL is stored per-device from its farm node's screen_url
$(".js-view-screen").on("click", function () {
    const deviceId  = $(this).data("deviceId");
    const screenUrl = ($(this).data("screenUrl") || "").replace(/\/+$/, "");
    const iframe    = $("#screen-iframe");

    if (!screenUrl) {
        showAlert("<?= __('Screen URL is not configured for this farm node. Please set it in RPA Manager \u2192 Farm Nodes.') ?>", 'error');
        return;
    }
    iframe.attr("src", screenUrl + "/device/" + deviceId);
    $("#screen-modal").addClass("is-active");
});

                // Close screen modal
                $(".screen-modal__close, #screen-modal").on("click", function(e) {
                    if (e.target === this) {
                        $("#screen-modal").removeClass("is-active");
                        $("#screen-iframe").attr("src", "");
                    }
                });

                // Close assignment modal
                $(".modal__close, #assignment-modal").on("click", function(e) {
                    if (e.target === this) {
                        $("#assignment-modal").removeClass("is-active");
                        $("#selected-device-id").val("");
                        $('input[name="account"]').prop('checked', false);
                        $("#confirm-assignment").prop('disabled', true);
                    }
                });

                // ── User Farm Connections ─────────────────────────────────
                var userFarms = window.RPA_USER_FARMS || [];

                function renderUserFarms(farms) {
                    var $list = $("#user-farms-list");
                    $("#user-farms-count").text(farms.length);

                    if (!farms.length) {
                        $list.html('<p class="text-sm text-gray-400 py-2"><?= __("No farm connections yet. Click \"Connect Farm\" to add one.") ?></p>');
                        return;
                    }

                    var rows = farms.map(function(f) {
                        var statusBadge = f.is_active
                            ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700"><?= __("Active") ?></span>'
                            : '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500"><?= __("Inactive") ?></span>';

                        var screenCell = f.screen_url
                            ? '<a href="' + $('<span/>').text(f.screen_url).html() + '" target="_blank" class="text-xs text-indigo-600 hover:underline truncate max-w-xs inline-block">' + $('<span/>').text(f.screen_url).html() + '</a>'
                            : '<span class="text-xs text-gray-400">—</span>';

                        var toggleLabel = f.is_active ? '<?= __("Deactivate") ?>' : '<?= __("Activate") ?>';

                        return '<tr class="border-b border-gray-100 last:border-0 hover:bg-gray-50">'
                            + '<td class="py-2 pr-4 text-sm font-medium text-gray-800">' + $('<span/>').text(f.name).html() + '</td>'
                            + '<td class="py-2 pr-4 text-xs text-gray-500 truncate max-w-xs">' + $('<span/>').text(f.url).html() + '</td>'
                            + '<td class="py-2 pr-4">' + screenCell + '</td>'
                            + '<td class="py-2 pr-4">' + statusBadge + '</td>'
                            + '<td class="py-2 whitespace-nowrap">'
                            +   '<button class="js-uf-edit text-xs text-blue-600 hover:underline mr-3" data-id="' + f.id + '" data-name="' + $('<span/>').text(f.name).html() + '" data-url="' + $('<span/>').text(f.url).html() + '" data-screen-url="' + $('<span/>').text(f.screen_url || '').html() + '"><?= __("Edit") ?></button>'
                            +   '<button class="js-uf-toggle text-xs text-gray-500 hover:underline mr-3" data-id="' + f.id + '" data-active="' + f.is_active + '">' + toggleLabel + '</button>'
                            +   '<button class="js-uf-delete text-xs text-red-600 hover:underline" data-id="' + f.id + '" data-name="' + $('<span/>').text(f.name).html() + '"><?= __("Delete") ?></button>'
                            + '</td>'
                            + '</tr>';
                    }).join('');

                    $list.html('<table class="w-full">'
                        + '<thead><tr class="text-left text-xs font-medium text-gray-500 border-b border-gray-200">'
                        + '<th class="pb-2 pr-4"><?= __("Name") ?></th>'
                        + '<th class="pb-2 pr-4"><?= __("Farm API URL") ?></th>'
                        + '<th class="pb-2 pr-4"><?= __("Screen URL") ?></th>'
                        + '<th class="pb-2 pr-4"><?= __("Status") ?></th>'
                        + '<th class="pb-2"><?= __("Actions") ?></th>'
                        + '</tr></thead>'
                        + '<tbody>' + rows + '</tbody>'
                        + '</table>');
                }

                function resetUfForm() {
                    $("#uf-edit-id").val("");
                    $("#uf-name").val("");
                    $("#uf-url").val("");
                    $("#uf-screen-url").val("");
                    $("#uf-form-error").hide().text("");
                    $("#user-farm-form-title").text("<?= __("Connect New Farm") ?>");
                    $("#user-farm-form").hide();
                }

                // Initial render
                renderUserFarms(userFarms);

                // Toggle collapse/expand
                $("#user-farms-header").on("click", function(e) {
                    if ($(e.target).closest("#btn-connect-farm").length) return;
                    var $body = $("#user-farms-body");
                    var $chevron = $("#user-farms-chevron");
                    if ($body.is(":visible")) {
                        $body.slideUp(200);
                        $chevron.css("transform", "rotate(-90deg)");
                    } else {
                        $body.slideDown(200);
                        $chevron.css("transform", "rotate(0deg)");
                    }
                });

                // Show add form
                $("#btn-connect-farm").on("click", function(e) {
                    e.stopPropagation();
                    resetUfForm();
                    $("#user-farms-body").slideDown(200);
                    $("#user-farms-chevron").css("transform", "rotate(0deg)");
                    $("#user-farm-form").slideDown(200);
                    $("#uf-name").focus();
                });

                // Cancel form
                $("#btn-uf-cancel").on("click", function() {
                    resetUfForm();
                });

                // Edit button
                $(document).on("click", ".js-uf-edit", function() {
                    var $btn = $(this);
                    $("#uf-edit-id").val($btn.data("id"));
                    $("#uf-name").val($btn.data("name"));
                    $("#uf-url").val($btn.data("url"));
                    $("#uf-screen-url").val($btn.data("screenUrl") || "");
                    $("#uf-form-error").hide().text("");
                    $("#user-farm-form-title").text("<?= __("Edit Farm Connection") ?>");
                    $("#user-farms-body").slideDown(200);
                    $("#user-farms-chevron").css("transform", "rotate(0deg)");
                    $("#user-farm-form").slideDown(200);
                    $("#uf-name").focus();
                });

                // Save (create or update)
                $("#btn-uf-save").on("click", function() {
                    var editId   = $("#uf-edit-id").val();
                    var name     = $.trim($("#uf-name").val());
                    var url      = $.trim($("#uf-url").val());
                    var screenUrl= $.trim($("#uf-screen-url").val());

                    if (!name || !url) {
                        $("#uf-form-error").text("<?= __("Name and Farm API URL are required.") ?>").show();
                        return;
                    }

                    var $btn = $(this);
                    $btn.prop("disabled", true).addClass("opacity-50");

                    var payload = { name: name, url: url, screen_url: screenUrl };
                    if (editId) {
                        payload.action = "update";
                        payload.id     = editId;
                    } else {
                        payload.action = "create";
                    }

                    $.ajax({
                        url: window.RPA_USER_FARMS_API,
                        type: "POST",
                        contentType: "application/json",
                        dataType: "json",
                        data: JSON.stringify(payload),
                        success: function(resp) {
                            if (resp.success) {
                                if (editId) {
                                    userFarms = userFarms.map(function(f) {
                                        return f.id == editId ? resp.farm : f;
                                    });
                                } else {
                                    userFarms.push(resp.farm);
                                }
                                renderUserFarms(userFarms);
                                resetUfForm();
                                showAlert(resp.message || "<?= __("Saved.") ?>", "success");
                            } else {
                                $("#uf-form-error").text(resp.message || "<?= __("An error occurred.") ?>").show();
                            }
                        },
                        error: function() {
                            $("#uf-form-error").text("<?= __("An error occurred. Please try again.") ?>").show();
                        },
                        complete: function() {
                            $btn.prop("disabled", false).removeClass("opacity-50");
                        }
                    });
                });

                // Delete button
                $(document).on("click", ".js-uf-delete", function() {
                    var id   = $(this).data("id");
                    var name = $(this).data("name");
                    if (!confirm("<?= __("Delete farm connection") ?> \"" + name + "\"?")) return;

                    $.ajax({
                        url: window.RPA_USER_FARMS_API,
                        type: "POST",
                        contentType: "application/json",
                        dataType: "json",
                        data: JSON.stringify({ action: "delete", id: id }),
                        success: function(resp) {
                            if (resp.success) {
                                userFarms = userFarms.filter(function(f) { return f.id != id; });
                                renderUserFarms(userFarms);
                                showAlert(resp.message || "<?= __("Deleted.") ?>", "success");
                            } else {
                                showAlert(resp.message || "<?= __("An error occurred.") ?>", "error");
                            }
                        },
                        error: function() {
                            showAlert("<?= __("An error occurred. Please try again.") ?>", "error");
                        }
                    });
                });

                // Toggle active/inactive
                $(document).on("click", ".js-uf-toggle", function() {
                    var $btn     = $(this);
                    var id       = $btn.data("id");
                    var isActive = parseInt($btn.data("active")) ? 0 : 1;
                    var farm     = userFarms.find(function(f) { return f.id == id; });
                    if (!farm) return;

                    $.ajax({
                        url: window.RPA_USER_FARMS_API,
                        type: "POST",
                        contentType: "application/json",
                        dataType: "json",
                        data: JSON.stringify({
                            action: "update",
                            id: id,
                            name: farm.name,
                            url: farm.url,
                            screen_url: farm.screen_url || "",
                            is_active: isActive
                        }),
                        success: function(resp) {
                            if (resp.success) {
                                userFarms = userFarms.map(function(f) {
                                    return f.id == id ? resp.farm : f;
                                });
                                renderUserFarms(userFarms);
                            } else {
                                showAlert(resp.message || "<?= __("An error occurred.") ?>", "error");
                            }
                        },
                        error: function() {
                            showAlert("<?= __("An error occurred. Please try again.") ?>", "error");
                        }
                    });
                });
            });
        </script>
        

        <?php require_once(APPPATH.'/views/fragments/google-analytics.fragment.php'); ?>
    </body>
</html> 