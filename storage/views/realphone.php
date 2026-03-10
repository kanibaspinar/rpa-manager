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
     <script>
    const farmNumber = <?= json_encode($FarmNumber) ?>;
    // Screen base URLs from RPA Manager settings (injected by controller before rendering this view)
    window.RPA_SCREEN_BASE_URL = <?= isset($ScreenBaseUrl) ? json_encode($ScreenBaseUrl) : 'null' ?>;
    window.RPA_SCREEN_BASE_URL_2 = <?= isset($ScreenBaseUrl2) ? json_encode($ScreenBaseUrl2) : 'null' ?>;
</script>

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

               // View screen functionality (Screen base URL comes from RPA Manager settings)
$(".js-view-screen2").on("click", function () {
    const deviceId = $(this).data("deviceId");
    const iframe = $("#screen-iframe");

    const baseUrl = (window.RPA_SCREEN_BASE_URL_2 || window.RPA_SCREEN_BASE_URL || "").replace(/\/+$/, "");
    if (!baseUrl) {
        showAlert("<?= __('Screen base URL is not configured. Please ask admin to set it in RPA Manager settings.') ?>", 'error');
        return;
    }
    iframe.attr("src", baseUrl + "/device/" + deviceId);

    $("#screen-modal").addClass("is-active");
});

                // Close screen modal
                $(".screen-modal__close, #screen-modal").on("click", function(e) {
                    if (e.target === this) {
                        $("#screen-modal").removeClass("is-active");
                        $("#screen-iframe").attr("src", "");
                    }
                });
               // View screen functionality
$(".js-view-screen").on("click", function () {
    const deviceId = $(this).data("deviceId");
    const iframe = $("#screen-iframe");

    const baseUrl = (window.RPA_SCREEN_BASE_URL || "").replace(/\/+$/, "");
    if (!baseUrl) {
        showAlert("<?= __('Screen base URL is not configured. Please ask admin to set it in RPA Manager settings.') ?>", 'error');
        return;
    }
    iframe.attr("src", baseUrl + "/device/" + deviceId);

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
            });
        </script>
        

        <?php require_once(APPPATH.'/views/fragments/google-analytics.fragment.php'); ?>
    </body>
</html> 