<?php if (!defined('APP_VERSION')) die("Yo, what's up?"); ?>
<!DOCTYPE html>
<html lang="<?= ACTIVE_LANG ?>">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0"/>
        <meta name="theme-color" content="#fff">
        <title><?= __("RPA Manager") ?></title>
        <link rel="icon" href="<?= site_settings("logomark") ? site_settings("logomark") : APPURL."/assets/img/logomark.png" ?>" type="image/x-icon">
        <link rel="shortcut icon" href="<?= site_settings("logomark") ? site_settings("logomark") : APPURL."/assets/img/logomark.png" ?>" type="image/x-icon">
        <link rel="stylesheet" type="text/css" href="<?= APPURL."/assets/css/plugins.css?v=".VERSION ?>">
        <link rel="stylesheet" type="text/css" href="<?= APPURL."/assets/css/core.css?v=".VERSION ?>">
        <style>
            /* ── Tab system ──────────────────────────────────────── */
            .rpa-tabs { display:flex; gap:4px; margin-bottom:20px; border-bottom:2px solid rgba(0,0,0,.07); padding-bottom:0; }
            .rpa-tab-btn {
                padding:9px 18px; font-size:13px; font-weight:500; cursor:pointer;
                border:none; background:none; border-radius:6px 6px 0 0;
                color:var(--dashboard-text-secondary, #6D7784);
                transition:all .15s; position:relative; bottom:-2px;
                border-bottom:2px solid transparent;
            }
            .rpa-tab-btn:hover { color:var(--dashboard-link,#3B82F6); background:rgba(59,130,246,.05); }
            .rpa-tab-btn.active {
                color:var(--dashboard-link,#3B82F6);
                border-bottom:2px solid var(--dashboard-link,#3B82F6);
                background:rgba(59,130,246,.06);
            }
            .rpa-tab-btn .mdi { font-size:15px; margin-right:5px; vertical-align:middle; }
            .rpa-tab-panel { display:none; }
            .rpa-tab-panel.active { display:block; animation:rpaFadeIn .2s ease; }
            @keyframes rpaFadeIn { from{opacity:0;transform:translateY(4px)} to{opacity:1;transform:translateY(0)} }

            /* ── Farm nodes table ────────────────────────────────── */
            .rpa-nodes-table { width:100%; border-collapse:collapse; font-size:13px; }
            .rpa-nodes-table th {
                text-align:left; padding:8px 12px; font-weight:600;
                background:rgba(0,0,0,.03); border-bottom:1px solid rgba(0,0,0,.08);
                color:var(--dashboard-text-secondary,#6D7784); font-size:11px; text-transform:uppercase; letter-spacing:.5px;
            }
            .rpa-nodes-table td { padding:10px 12px; border-bottom:1px solid rgba(0,0,0,.05); vertical-align:middle; }
            .rpa-nodes-table tr:last-child td { border-bottom:none; }
            .rpa-nodes-table tr:hover td { background:rgba(59,130,246,.03); }
            .rpa-nodes-table tr.editing td { background:rgba(59,130,246,.04); }
            .rpa-node-url { color:var(--dashboard-text-secondary,#6D7784); font-size:11px; word-break:break-all; max-width:260px; }
            .rpa-status-badge {
                display:inline-flex; align-items:center; gap:4px;
                padding:2px 8px; border-radius:20px; font-size:11px; font-weight:600;
            }
            .rpa-status-active { background:rgba(16,185,129,.1); color:#059669; }
            .rpa-status-inactive { background:rgba(107,114,128,.1); color:#6B7280; }
            .rpa-node-actions { display:flex; gap:6px; white-space:nowrap; }
            .rpa-btn-icon {
                width:28px; height:28px; border-radius:6px; border:1px solid rgba(0,0,0,.12);
                background:#fff; cursor:pointer; display:inline-flex; align-items:center;
                justify-content:center; font-size:14px; transition:all .15s; color:#374151;
            }
            .rpa-btn-icon:hover { background:#f3f4f6; border-color:rgba(0,0,0,.2); }
            .rpa-btn-icon.danger:hover { background:rgba(239,68,68,.08); color:#DC2626; border-color:rgba(239,68,68,.3); }
            .rpa-btn-icon.primary:hover { background:rgba(59,130,246,.08); color:#2563EB; border-color:rgba(59,130,246,.3); }

            /* ── Inline edit row ─────────────────────────────────── */
            .rpa-edit-row td { padding:8px 12px; background:rgba(59,130,246,.04); }
            .rpa-edit-row input[type=text], .rpa-edit-row input[type=url] {
                padding:5px 8px; border-radius:5px; border:1px solid rgba(59,130,246,.4);
                font-size:12px; background:#fff; width:100%;
            }
            .rpa-edit-row input:focus { outline:none; border-color:#3B82F6; box-shadow:0 0 0 2px rgba(59,130,246,.15); }

            /* ── Add node form ───────────────────────────────────── */
            #rpa-add-node-form {
                display:none; background:rgba(16,185,129,.04);
                border:1px dashed rgba(16,185,129,.3); border-radius:8px;
                padding:14px 16px; margin-top:12px;
            }
            #rpa-add-node-form.show { display:block; animation:rpaFadeIn .2s ease; }
            #rpa-add-node-form label { font-size:12px; font-weight:600; display:block; margin-bottom:3px; }
            #rpa-add-node-form input {
                padding:7px 10px; border-radius:6px; border:1px solid rgba(0,0,0,.15);
                font-size:13px; width:100%; box-sizing:border-box; background:#fff;
            }
            #rpa-add-node-form input:focus { outline:none; border-color:#3B82F6; box-shadow:0 0 0 2px rgba(59,130,246,.12); }

            /* ── Device manager ──────────────────────────────────── */
            .rpa-device-section { padding:10px 0; }
            .rpa-device-section + .rpa-device-section { border-top:1px solid rgba(0,0,0,.06); }
            .rpa-section-label { font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:var(--dashboard-text-secondary,#6D7784); margin-bottom:8px; }
            .rpa-result-pre {
                max-height:280px; overflow:auto; padding:10px 12px;
                border-radius:8px; background:rgba(0,0,0,.03); font-size:11px;
                font-family:monospace; border:1px solid rgba(0,0,0,.06); white-space:pre-wrap; word-break:break-all;
            }
            .rpa-result-pre.success-bg { background:rgba(16,185,129,.06); border-color:rgba(16,185,129,.2); }
            .rpa-result-pre.error-bg   { background:rgba(239,68,68,.06);  border-color:rgba(239,68,68,.2); }

            /* ── Node select dropdown ────────────────────────────── */
            .rpa-node-select {
                width:100%; padding:8px 10px; border-radius:6px;
                border:1px solid rgba(0,0,0,.15); font-size:13px; background:#fff;
                appearance:none; cursor:pointer;
            }
            .rpa-node-select:focus { outline:none; border-color:#3B82F6; box-shadow:0 0 0 2px rgba(59,130,246,.12); }
            .rpa-select-wrap { position:relative; }
            .rpa-select-wrap::after {
                content:'\F035D'; font-family:'Material Design Icons'; pointer-events:none;
                position:absolute; right:10px; top:50%; transform:translateY(-50%); font-size:16px;
                color:var(--dashboard-text-secondary,#6D7784);
            }

            /* ── Empty state ─────────────────────────────────────── */
            .rpa-empty-state {
                text-align:center; padding:32px 16px; color:var(--dashboard-text-secondary,#6D7784);
            }
            .rpa-empty-state .mdi { font-size:36px; display:block; margin-bottom:8px; opacity:.4; }
            .rpa-empty-state p { font-size:13px; margin:0; }

            /* ── Notice banner ───────────────────────────────────── */
            .rpa-notice {
                padding:8px 12px; border-radius:6px; font-size:12px;
                display:flex; align-items:center; gap:8px; margin-bottom:10px;
            }
            .rpa-notice-warn { background:rgba(245,158,11,.08); color:#92400E; border:1px solid rgba(245,158,11,.2); }
            .rpa-notice-ok   { background:rgba(16,185,129,.08); color:#065F46; border:1px solid rgba(16,185,129,.2); }

            /* ── Spinner ─────────────────────────────────────────── */
            .rpa-spinner { display:none; width:16px; height:16px; border:2px solid rgba(59,130,246,.2); border-top-color:#3B82F6; border-radius:50%; animation:rpaSpinner .6s linear infinite; vertical-align:middle; }
            @keyframes rpaSpinner { to{transform:rotate(360deg)} }

            /* ── Settings form ───────────────────────────────────── */
            .rpa-form-field { margin-bottom:14px; }
            .rpa-form-field label { display:block; font-weight:600; font-size:12px; margin-bottom:5px; }
            .rpa-form-field input[type=url], .rpa-form-field input[type=text] {
                width:100%; padding:8px 10px; border-radius:6px;
                border:1px solid rgba(0,0,0,.15); font-size:13px; box-sizing:border-box;
            }
            .rpa-form-hint { font-size:11px; color:var(--dashboard-text-secondary,#6D7784); margin-top:4px; }
        </style>
    </head>
    <body class="<?= $AuthUser ? ( $AuthUser->get("preferences.dark_mode_status") == "1" ? "darkside" : "" ) : "" ?>">
        <?php
            $Nav = new stdClass;
            $Nav->activeMenu = "rpa-manager";
            require_once(APPPATH.'/views/fragments/navigation.fragment.php');
        ?>
        <?php
            $TopBar = new stdClass;
            $TopBar->title = __("RPA Manager");
            $TopBar->btn = false;
            require_once(APPPATH.'/views/fragments/topbar.fragment.php');
        ?>

        <div class="dashboard-skeleton" id="rpa-manager-settings">
            <div class="dashboard-wrapper">

                <!-- ── Tab navigation ──────────────────────────────── -->
                <div class="rpa-tabs">
                    <button class="rpa-tab-btn active" data-tab="nodes">
                        <span class="mdi mdi-server-network"></span><?= __("Farm Nodes") ?>
                    </button>
                    <button class="rpa-tab-btn" data-tab="devices">
                        <span class="mdi mdi-cellphone-link"></span><?= __("Device Manager") ?>
                    </button>
                    <button class="rpa-tab-btn" data-tab="general">
                        <span class="mdi mdi-cog-outline"></span><?= __("General Settings") ?>
                    </button>
                </div>

                <!-- ══════════════════════════════════════════════════ -->
                <!-- Tab: Farm Nodes                                    -->
                <!-- ══════════════════════════════════════════════════ -->
                <div class="rpa-tab-panel active" id="rpa-tab-nodes">
                    <div class="dashboard-row">
                        <div style="grid-column: span 8;">
                            <div class="dashboard-card">
                                <div class="dashboard-card-inner" style="align-items:stretch; text-align:left;">
                                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                                        <div>
                                            <div class="dashboard-title"><?= __("Phone Farm Nodes") ?></div>
                                            <div class="dashboard-subtitle">
                                                <?= __("Each node is an independent phone farm API. Phones are tied to the node they belong to.") ?>
                                            </div>
                                        </div>
                                        <button type="button" id="btn-show-add-node" class="dashboard-button" style="white-space:nowrap; flex-shrink:0; margin-left:16px; font-size:12px;">
                                            <span class="mdi mdi-plus"></span> <?= __("Add Node") ?>
                                        </button>
                                    </div>

                                    <!-- Nodes table -->
                                    <div id="rpa-nodes-wrap" style="margin-top:12px;">
                                        <table class="rpa-nodes-table" id="rpa-nodes-table">
                                            <thead>
                                                <tr>
                                                    <th style="width:30px;">#</th>
                                                    <th><?= __("Name") ?></th>
                                                    <th><?= __("API URL") ?></th>
                                                    <th style="width:90px;"><?= __("Status") ?></th>
                                                    <th style="width:80px;"><?= __("Actions") ?></th>
                                                </tr>
                                            </thead>
                                            <tbody id="rpa-nodes-tbody">
                                                <!-- filled by JS -->
                                            </tbody>
                                        </table>
                                        <div id="rpa-nodes-empty" class="rpa-empty-state" style="display:none;">
                                            <span class="mdi mdi-server-off"></span>
                                            <p><?= __("No farm nodes configured yet. Add your first node above.") ?></p>
                                        </div>
                                    </div>

                                    <!-- Add node form -->
                                    <div id="rpa-add-node-form">
                                        <div style="font-weight:600; font-size:13px; margin-bottom:10px;">
                                            <span class="mdi mdi-plus-circle-outline" style="color:#10B981;"></span>
                                            <?= __("Add New Farm Node") ?>
                                        </div>
                                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                                            <div style="flex:1; min-width:140px;">
                                                <label><?= __("Node Name") ?></label>
                                                <input type="text" id="rpa-new-node-name" placeholder="<?= __("e.g. Main Farm") ?>" autocomplete="off">
                                            </div>
                                            <div style="flex:2; min-width:200px;">
                                                <label><?= __("API URL") ?></label>
                                                <input type="url" id="rpa-new-node-url" placeholder="https://your-farm-api.example.com" autocomplete="off">
                                            </div>
                                            <div style="flex:2; min-width:200px;">
                                                <label><?= __("Screen URL") ?> <span style="font-weight:400; color:var(--dashboard-text-secondary,#6D7784);">(<?= __("optional") ?>)</span></label>
                                                <input type="url" id="rpa-new-node-screen-url" placeholder="https://screens.example.com" autocomplete="off">
                                            </div>
                                        </div>
                                        <div style="display:flex; gap:8px; margin-top:10px; align-items:center;">
                                            <label style="display:flex; align-items:center; gap:6px; font-size:12px; font-weight:500; cursor:pointer;">
                                                <input type="checkbox" id="rpa-new-node-active" checked> <?= __("Active") ?>
                                            </label>
                                            <div style="flex:1;"></div>
                                            <button type="button" id="btn-cancel-add-node" class="dashboard-button dashboard-button--secondary" style="font-size:12px;">
                                                <?= __("Cancel") ?>
                                            </button>
                                            <button type="button" id="btn-save-new-node" class="dashboard-button" style="font-size:12px;">
                                                <span class="mdi mdi-content-save-outline"></span> <?= __("Save Node") ?>
                                                <span class="rpa-spinner" id="spinner-add-node"></span>
                                            </button>
                                        </div>
                                        <div id="rpa-add-node-msg" style="font-size:12px; margin-top:6px;"></div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Sidebar: info card -->
                        <div class="dashboard-col-4">
                            <div class="dashboard-card">
                                <div class="dashboard-card-inner" style="align-items:stretch; text-align:left;">
                                    <div class="dashboard-title"><?= __("How it works") ?></div>
                                    <div style="font-size:13px; line-height:1.6; color:var(--dashboard-text-secondary,#6D7784);">
                                        <p style="margin:0 0 10px;">
                                            <?= __("Each Farm Node is an independent phone farm server running the RPA API. Phones registered to a node are tracked separately.") ?>
                                        </p>
                                        <ul style="margin:0; padding-left:16px;">
                                            <li style="margin-bottom:6px;"><?= __("Add as many farm nodes as you need") ?></li>
                                            <li style="margin-bottom:6px;"><?= __("When assigning devices, select which farm to target") ?></li>
                                            <li style="margin-bottom:6px;"><?= __("Each phone stays tied to its own farm — no mixing") ?></li>
                                            <li><?= __("Disable a node to pause it without deleting") ?></li>
                                        </ul>
                                    </div>
                                    <div style="margin-top:14px; padding:10px 12px; border-radius:8px; background:rgba(59,130,246,.06); font-size:12px;">
                                        <strong><?= __("API endpoints used:") ?></strong><br>
                                        <code style="font-size:11px;">/api/devices/list</code><br>
                                        <code style="font-size:11px;">/api/devices/user/{id}</code><br>
                                        <code style="font-size:11px;">/api/devices/assign</code><br>
                                        <code style="font-size:11px;">/api/devices/create</code><br>
                                        <code style="font-size:11px;">/api/devices/{id}</code> (DELETE)
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════ -->
                <!-- Tab: Device Manager                               -->
                <!-- ══════════════════════════════════════════════════ -->
                <div class="rpa-tab-panel" id="rpa-tab-devices">
                    <div class="dashboard-row">
                        <div class="dashboard-col-5">
                            <div class="dashboard-card">
                                <div class="dashboard-card-inner" style="align-items:stretch; text-align:left;">
                                    <div class="dashboard-title"><?= __("Device Manager") ?></div>
                                    <div class="dashboard-subtitle" style="margin-bottom:14px;">
                                        <?= __("Select a farm node and user to manage devices.") ?>
                                    </div>

                                    <!-- Farm node selector -->
                                    <div class="rpa-device-section">
                                        <div class="rpa-section-label"><?= __("1. Select Farm Node") ?></div>
                                        <div id="rpa-dm-no-nodes" class="rpa-notice rpa-notice-warn" style="display:none;">
                                            <span class="mdi mdi-alert-outline"></span>
                                            <?= __("No farm nodes found. Please add one in the Farm Nodes tab first.") ?>
                                        </div>
                                        <div class="rpa-select-wrap">
                                            <select id="rpa-farm-node-select" class="rpa-node-select">
                                                <option value=""><?= __("— Select a farm node —") ?></option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- User picker -->
                                    <div class="rpa-device-section" style="position:relative;" id="rpa-user-picker">
                                        <div class="rpa-section-label"><?= __("2. Select User") ?></div>
                                        <input type="hidden" id="rpa-admin-user" value="">
                                        <input type="text"
                                               id="rpa-user-search"
                                               placeholder="<?= __("Search by username, email or ID...") ?>"
                                               autocomplete="off"
                                               style="width:100%; padding:8px 10px; border-radius:6px; border:1px solid rgba(0,0,0,.15); font-size:13px; background:#fff; box-sizing:border-box;">
                                        <div id="rpa-user-dropdown"
                                             style="display:none; position:absolute; left:0; right:0; top:100%; z-index:200;
                                                    max-height:200px; overflow-y:auto; background:#fff;
                                                    border:1px solid rgba(0,0,0,.15); border-top:none;
                                                    border-radius:0 0 6px 6px; box-shadow:0 4px 14px rgba(0,0,0,.1);">
                                        </div>
                                    </div>

                                    <!-- List actions -->
                                    <div class="rpa-device-section">
                                        <div class="rpa-section-label"><?= __("3. Browse Devices") ?></div>
                                        <div style="display:flex; gap:8px;">
                                            <button type="button" id="btn-rpa-list-user-devices" class="dashboard-button dashboard-button--secondary" style="flex:1; font-size:12px;">
                                                <span class="mdi mdi-account-box-outline"></span> <?= __("User's Devices") ?>
                                            </button>
                                            <button type="button" id="btn-rpa-list-available" class="dashboard-button dashboard-button--secondary" style="flex:1; font-size:12px;">
                                                <span class="mdi mdi-cellphone-check"></span> <?= __("Available") ?>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Assign -->
                                    <div class="rpa-device-section">
                                        <div class="rpa-section-label"><?= __("4. Assign Devices") ?></div>
                                        <div style="display:flex; gap:8px; align-items:center;">
                                            <div style="flex:1;">
                                                <input type="number" id="rpa-assign-count" value="1" min="1"
                                                       placeholder="<?= __("Count") ?>"
                                                       style="width:100%; padding:7px 9px; border-radius:6px; border:1px solid rgba(0,0,0,.15); font-size:13px;">
                                            </div>
                                            <button type="button" id="btn-rpa-assign" class="dashboard-button" style="flex:2; font-size:12px; padding:8px 10px;">
                                                <span class="mdi mdi-cellphone-arrow-down"></span> <?= __("Assign") ?>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Create device -->
                                    <div class="rpa-device-section">
                                        <div class="rpa-section-label"><?= __("5. Create Device") ?></div>
                                        <div style="display:flex; gap:6px; flex-wrap:wrap;">
                                            <input type="text" id="rpa-create-device-id" placeholder="device_id"
                                                   style="flex:1; min-width:80px; padding:7px 9px; border-radius:6px; border:1px solid rgba(0,0,0,.15); font-size:12px;">
                                            <input type="text" id="rpa-create-device-name" placeholder="<?= __("Name") ?>"
                                                   style="flex:1; min-width:80px; padding:7px 9px; border-radius:6px; border:1px solid rgba(0,0,0,.15); font-size:12px;">
                                            <button type="button" id="btn-rpa-create-device" class="dashboard-button" style="font-size:12px; padding:7px 12px;">
                                                <span class="mdi mdi-plus"></span> <?= __("Create") ?>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Delete device -->
                                    <div class="rpa-device-section">
                                        <div class="rpa-section-label"><?= __("6. Delete / Unassign Device") ?></div>
                                        <div style="display:flex; gap:6px;">
                                            <input type="text" id="rpa-delete-device-id" placeholder="device_id"
                                                   style="flex:1; padding:7px 9px; border-radius:6px; border:1px solid rgba(0,0,0,.15); font-size:12px;">
                                            <button type="button" id="btn-rpa-delete-device" class="dashboard-button dashboard-button--danger" style="font-size:12px; padding:7px 12px;">
                                                <span class="mdi mdi-delete-outline"></span> <?= __("Delete") ?>
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Result panel -->
                        <div class="dashboard-col-4">
                            <div class="dashboard-card" style="height:100%;">
                                <div class="dashboard-card-inner" style="align-items:stretch; text-align:left; height:100%;">
                                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:10px;">
                                        <div class="dashboard-title" style="margin:0;"><?= __("Result") ?></div>
                                        <button type="button" id="btn-rpa-clear-result" class="rpa-btn-icon" title="<?= __("Clear") ?>">
                                            <span class="mdi mdi-broom"></span>
                                        </button>
                                    </div>
                                    <div id="rpa-result-status" style="margin-bottom:6px; min-height:20px;"></div>
                                    <pre id="rpa-devices-result" class="rpa-result-pre"><?= __("Results will appear here after an action.") ?></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ══════════════════════════════════════════════════ -->
                <!-- Tab: General Settings                             -->
                <!-- ══════════════════════════════════════════════════ -->
                <div class="rpa-tab-panel" id="rpa-tab-general">
                    <div class="dashboard-row">
                        <div class="dashboard-col-4">
                            <div class="dashboard-card">
                                <div class="dashboard-card-inner" style="align-items:stretch; text-align:left;">
                                    <div class="dashboard-title"><?= __("General Settings") ?></div>
                                    <div class="dashboard-subtitle" style="margin-bottom:14px;">
                                        <?= __("Global settings for the RPA Manager plugin.") ?>
                                    </div>

                                    <?php if (!empty($success)): ?>
                                        <div class="rpa-notice rpa-notice-ok" style="margin-bottom:12px;">
                                            <span class="mdi mdi-check-circle-outline"></span>
                                            <?= htmlchars($success) ?>
                                        </div>
                                    <?php endif; ?>

                                    <form method="post" action="">
                                        <div class="rpa-form-field">
                                            <label><?= __("Screen Base URL") ?></label>
                                            <input type="url"
                                                   name="screen_base_url"
                                                   value="<?= htmlchars($Settings['screen_base_url'] ?? '') ?>"
                                                   placeholder="https://screens.localto.net/">
                                            <div class="rpa-form-hint">
                                                <?= __("Base URL for live screen links. Used as: {url}{device_id}") ?>
                                            </div>
                                        </div>
                                        <button type="submit" class="dashboard-button">
                                            <span class="mdi mdi-content-save-outline"></span>
                                            <?= __("Save Settings") ?>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- .dashboard-wrapper -->
        </div><!-- .dashboard-skeleton -->

        <!-- ── Bootstrap data ──────────────────────────────────────── -->
        <script type="text/javascript">
            window.RPA_FARM_NODES_API   = '<?= APPURL ?>/e/rpa-manager/farm-nodes-api/';
            window.RPA_MANAGER_DEVICES_API_URL = '<?= APPURL ?>/e/rpa-manager/devices-api/';
            window.RPA_USERS = <?= json_encode(array_map(function($u) {
                return ['id' => (int)$u['id'], 'username' => $u['username'], 'email' => $u['email']];
            }, $Users ?? []), JSON_UNESCAPED_UNICODE) ?>;
            window.RPA_FARM_NODES_INIT  = <?= json_encode(array_values($FarmNodes ?? []), JSON_UNESCAPED_UNICODE) ?>;
            window.RPA_MANAGER_I18N = {
                select_user:    '<?= __("Please select a user first.") ?>',
                select_node:    '<?= __("Please select a farm node first.") ?>',
                fill_device:    '<?= __("Please fill device_id and Name.") ?>',
                enter_device:   '<?= __("Please enter device_id to delete.") ?>',
                confirm_delete_device: '<?= __("Are you sure you want to delete/unassign this device?") ?>',
                confirm_delete_node:   '<?= __("Delete this farm node? This cannot be undone.") ?>',
                no_name:        '<?= __("Please enter a node name.") ?>',
                no_url:         '<?= __("Please enter a valid URL.") ?>'
            };
        </script>

        <script type="text/javascript" src="<?= APPURL."/assets/js/plugins.js?v=".VERSION ?>"></script>
        <?php require_once(APPPATH.'/inc/js-locale.inc.php'); ?>
        <script type="text/javascript" src="<?= APPURL."/assets/js/core.js?v=".VERSION ?>"></script>
        <script type="text/javascript" src="<?= APPURL."/inc/plugins/rpa-manager/assets/js/rpa-manager-settings.js?v=".VERSION ?>"></script>
    </body>
</html>
