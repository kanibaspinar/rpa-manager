<?php if (!defined('APP_VERSION')) die("Yo, what's up?"); ?>
<!DOCTYPE html>
<html lang="<?= ACTIVE_LANG ?>">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
        <meta name="theme-color" content="#fff">
        <title><?= __("RPA Manager - Settings") ?></title>
        <link rel="icon" href="<?= site_settings("logomark") ? site_settings("logomark") : APPURL."/assets/img/logomark.png" ?>" type="image/x-icon">
        <link rel="shortcut icon" href="<?= site_settings("logomark") ? site_settings("logomark") : APPURL."/assets/img/logomark.png" ?>" type="image/x-icon">
        <link rel="stylesheet" type="text/css" href="<?= APPURL."/assets/css/plugins.css?v=".VERSION ?>">
        <link rel="stylesheet" type="text/css" href="<?= APPURL."/assets/css/core.css?v=".VERSION ?>">
    </head>
    <body class="<?= $AuthUser ? ( $AuthUser->get("preferences.dark_mode_status") ? ( $AuthUser->get("preferences.dark_mode_status") == "1" ? "darkside" : "" ) : "" ) : "" ?>">
        <?php
            $Nav = new stdClass;
            $Nav->activeMenu = "rpa-manager";
            require_once(APPPATH.'/views/fragments/navigation.fragment.php');
        ?>
        <?php
            $TopBar = new stdClass;
            $TopBar->title = __("RPA Manager - Settings");
            $TopBar->btn = false;
            require_once(APPPATH.'/views/fragments/topbar.fragment.php');
        ?>
        <div class="dashboard-skeleton" id="rpa-manager-settings">
            <div class="dashboard-wrapper">
                <div class="dashboard-row">
                    <div class="dashboard-col-4">
                        <div class="dashboard-card">
                            <div class="dashboard-card-inner" style="align-items: stretch; text-align: left;">
                                <div class="dashboard-title"><?= __("Farm API configuration") ?></div>
                                <div class="dashboard-subtitle" style="margin-bottom: 15px;">
                                    <?= __("Set the base URL(s) of your real-phone farm API. These URLs will be used by RealPhoneController and RPA integrations.") ?>
                                </div>
                                <?php if (!empty($success)): ?>
                                    <div style="margin-bottom:10px; padding:8px 10px; border-radius:6px; background:rgba(0,128,0,.08); color:#256029; font-size:13px;">
                                        <?= htmlchars($success) ?>
                                    </div>
                                <?php endif; ?>
                                <form method="post" action="">
                                    <div style="margin-bottom: 12px;">
                                        <label style="display:block; font-weight:500; margin-bottom:5px;"><?= __("Primary Farm API URL") ?></label>
                                        <input type="url"
                                               name="farm_api_url"
                                               value="<?= htmlchars($Settings['farm_api_url'] ?? '') ?>"
                                               placeholder="https://your-farm-api.example.com"
                                               style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid rgba(0,0,0,.15); font-size:13px;">
                                        <p style="font-size:11px; color:var(--dashboard-text-secondary,#6D7784); margin-top:4px;">
                                            <?= __("This is the base URL used for device endpoints, e.g. {url}/api/devices/list", ["url" => "<code>{your-url}</code>"]) ?>
                                        </p>
                                    </div>
                                    <div style="margin-bottom: 12px;">
                                        <label style="display:block; font-weight:500; margin-bottom:5px;"><?= __("Secondary Farm API URL (optional)") ?></label>
                                        <input type="url"
                                               name="farm_api_url_2"
                                               value="<?= htmlchars($Settings['farm_api_url_2'] ?? '') ?>"
                                               placeholder="https://your-backup-farm-api.example.com"
                                               style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid rgba(0,0,0,.15); font-size:13px;">
                                        <p style="font-size:11px; color:var(--dashboard-text-secondary,#6D7784); margin-top:4px;">
                                            <?= __("Optional backup farm used when the primary is not reachable.") ?>
                                        </p>
                                    </div>
                                    <div style="margin-bottom: 12px;">
                                        <label style="display:block; font-weight:500; margin-bottom:5px;"><?= __("Screen base URL") ?></label>
                                        <input type="url"
                                               name="screen_base_url"
                                               value="<?= htmlchars($Settings['screen_base_url'] ?? '') ?>"
                                               placeholder="https://screens.localto.net/"
                                               style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid rgba(0,0,0,.15); font-size:13px;">
                                        <p style="font-size:11px; color:var(--dashboard-text-secondary,#6D7784); margin-top:4px;">
                                            <?= __("Base URL for live screen links, used like {url}{device_id} (example: https://screens.localto.net/device_123).", ["url" => "<code>https://screens.localto.net/</code>"]) ?>
                                        </p>
                                    </div>
                                    <button type="submit" class="dashboard-button">
                                        <span class="mdi mdi-content-save-outline"></span>
                                        <?= __("Save settings") ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="dashboard-col-4">
                        <div class="dashboard-card">
                            <div class="dashboard-card-inner" style="align-items: stretch; text-align: left;">
                                <div class="dashboard-title"><?= __("Admin device manager") ?></div>
                                <div class="dashboard-subtitle" style="margin-bottom: 12px;">
                                    <?= __("Assign and manage real-phone devices for any user. Uses the configured Farm API URL.") ?>
                                </div>
                                <div style="margin-bottom: 10px; position:relative;" id="rpa-user-picker">
                                    <label style="display:block; font-weight:500; margin-bottom:5px;"><?= __("Select user") ?></label>
                                    <input type="hidden" id="rpa-admin-user" value="">
                                    <input type="text"
                                           id="rpa-user-search"
                                           placeholder="<?= __("Search by username, email or ID...") ?>"
                                           autocomplete="off"
                                           style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid rgba(0,0,0,.15); font-size:13px; background:#fff;">
                                    <div id="rpa-user-dropdown"
                                         style="display:none; position:absolute; left:0; right:0; top:100%; z-index:100;
                                                max-height:220px; overflow-y:auto; background:#fff;
                                                border:1px solid rgba(0,0,0,.15); border-top:none;
                                                border-radius:0 0 6px 6px; box-shadow:0 4px 12px rgba(0,0,0,.1);">
                                    </div>
                                    <script>
                                        window.RPA_USERS = <?= json_encode(array_map(function($u){
                                            return [
                                                'id' => (int)$u['id'],
                                                'username' => $u['username'],
                                                'email' => $u['email'],
                                            ];
                                        }, $Users ?? []), JSON_UNESCAPED_UNICODE) ?>;
                                    </script>
                                </div>
                                <div style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:10px;">
                                    <button type="button" id="btn-rpa-list-user-devices" class="dashboard-button dashboard-button--secondary" style="flex:1 1 48%; font-size:12px;">
                                        <?= __("User's devices") ?>
                                    </button>
                                    <button type="button" id="btn-rpa-list-available" class="dashboard-button dashboard-button--secondary" style="flex:1 1 48%; font-size:12px;">
                                        <?= __("Available devices") ?>
                                    </button>
                                </div>
                                <div style="margin-bottom:10px; display:flex; gap:8px;">
                                    <div style="flex:1;">
                                        <label style="display:block; font-weight:500; margin-bottom:4px; font-size:12px;"><?= __("Assign count") ?></label>
                                        <input type="number" id="rpa-assign-count" value="1" min="1" style="width:100%; padding:6px 8px; border-radius:6px; border:1px solid rgba(0,0,0,.15); font-size:12px;">
                                    </div>
                                    <div style="flex:1; align-self:flex-end;">
                                        <button type="button" id="btn-rpa-assign" class="dashboard-button" style="width:100%; font-size:12px; padding:7px 10px;">
                                            <?= __("Assign devices") ?>
                                        </button>
                                    </div>
                                </div>
                                <div style="margin-bottom:8px;">
                                    <label style="display:block; font-weight:500; margin-bottom:4px; font-size:12px;"><?= __("Create device") ?></label>
                                    <div style="display:flex; gap:6px; margin-bottom:4px;">
                                        <input type="text" id="rpa-create-device-id" placeholder="device_id" style="flex:1; padding:6px 8px; border-radius:6px; border:1px solid rgba(0,0,0,.15); font-size:12px;">
                                        <input type="text" id="rpa-create-device-name" placeholder="Name (e.g. P1)" style="flex:1; padding:6px 8px; border-radius:6px; border:1px solid rgba(0,0,0,.15); font-size:12px;">
                                        <button type="button" id="btn-rpa-create-device" class="dashboard-button" style="font-size:12px; padding:7px 10px;">
                                            <?= __("Create") ?>
                                        </button>
                                    </div>
                                </div>
                                <div style="margin-bottom:8px;">
                                    <label style="display:block; font-weight:500; margin-bottom:4px; font-size:12px;"><?= __("Delete / unassign device") ?></label>
                                    <div style="display:flex; gap:6px;">
                                        <input type="text" id="rpa-delete-device-id" placeholder="device_id" style="flex:1; padding:6px 8px; border-radius:6px; border:1px solid rgba(0,0,0,.15); font-size:12px;">
                                        <button type="button" id="btn-rpa-delete-device" class="dashboard-button dashboard-button--danger" style="font-size:12px; padding:7px 10px;">
                                            <?= __("Delete") ?>
                                        </button>
                                    </div>
                                </div>
                                <div style="margin-top:10px;">
                                    <label style="display:block; font-weight:500; margin-bottom:4px; font-size:12px;"><?= __("Result") ?></label>
                                    <pre id="rpa-devices-result" style="max-height:260px; overflow:auto; padding:8px 10px; border-radius:6px; background:rgba(0,0,0,.04); font-size:11px;"></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript" src="<?= APPURL."/assets/js/plugins.js?v=".VERSION ?>"></script>
        <?php require_once(APPPATH.'/inc/js-locale.inc.php'); ?>
        <script type="text/javascript" src="<?= APPURL."/assets/js/core.js?v=".VERSION ?>"></script>
        <script type="text/javascript">
            window.RPA_MANAGER_DEVICES_API_URL = '<?= APPURL ?>/e/rpa-manager/devices-api/';
            window.RPA_MANAGER_I18N = {
                select_user: '<?= __("Please select a user first.") ?>',
                fill_device: '<?= __("Please fill device_id and Name.") ?>',
                enter_device: '<?= __("Please enter device_id to delete.") ?>',
                confirm_delete: '<?= __("Are you sure you want to delete/unassign this device?") ?>'
            };
        </script>
        <script type="text/javascript" src="<?= APPURL."/inc/plugins/rpa-manager/assets/js/rpa-manager-settings.js?v=".VERSION ?>"></script>
    </body>
</html>

