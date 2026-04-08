<?php if (!defined('APP_VERSION')) die("Yo, what's up?"); ?>

<!-- System Notifications -->
<div class="fixed top-20 right-4 z-50 w-96">
    <?php if (Input::get("success")): ?>
        <div class="animate-fade-in bg-green-50 border-l-4 border-green-500 p-4 mb-4 rounded-r-lg shadow-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-400 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-green-700 font-medium">
                    <?= htmlchars(Input::get("success")) ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <?php if (Input::get("error")): ?>
        <div class="animate-fade-in bg-red-50 border-l-4 border-red-500 p-4 mb-4 rounded-r-lg shadow-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-400 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-red-700 font-medium">
                    <?= htmlchars(Input::get("error")) ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <?php if (Input::get("warning")): ?>
        <div class="animate-fade-in bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-4 rounded-r-lg shadow-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-yellow-400 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <p class="text-sm text-yellow-700 font-medium">
                    <?= htmlchars(Input::get("warning")) ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <?php if (Input::get("info")): ?>
        <div class="animate-fade-in bg-blue-50 border-l-4 border-blue-500 p-4 mb-4 rounded-r-lg shadow-lg">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-400 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-blue-700 font-medium">
                    <?= htmlchars(Input::get("info")) ?>
                </p>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- ── My Farm Connections ─────────────────────────────────────────────── -->
<div class="bg-white rounded-lg shadow-md mb-6" id="user-farms-section">
    <div class="flex items-center justify-between px-5 py-4 cursor-pointer" id="user-farms-header">
        <h3 class="text-base font-semibold text-gray-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
            </svg>
            <?= __("My Farm Connections") ?>
            <span id="user-farms-count" class="ml-1 text-xs font-medium bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full">
                <?= count($AllUserFarms ?? []) ?>
            </span>
        </h3>
        <div class="flex items-center gap-3">
            <button type="button" id="btn-connect-farm"
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all duration-200">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                <?= __("Connect Farm") ?>
            </button>
            <svg id="user-farms-chevron" class="w-5 h-5 text-gray-400 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </div>
    </div>

    <div id="user-farms-body" class="border-t border-gray-100 px-5 py-4">
        <!-- Setup info banner -->
        <div class="flex items-start gap-3 p-3 mb-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
            <svg class="w-5 h-5 text-blue-400 mt-0.5 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <?= __("Connect your own phone farm by entering its API URL and screen URL below.") ?>
                <?= __("Don't have a farm set up yet?") ?>
                <a href="https://github.com/kanibaspinar/phone-automation"
                   target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-1 font-medium text-blue-700 hover:text-blue-900 hover:underline ml-1">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.477 2 12c0 4.418 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.009-.868-.013-1.703-2.782.604-3.369-1.341-3.369-1.341-.454-1.155-1.11-1.462-1.11-1.462-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.578 9.578 0 0112 6.836c.85.004 1.705.115 2.504.337 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.202 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.919.678 1.852 0 1.336-.012 2.415-.012 2.743 0 .267.18.578.688.48C19.138 20.163 22 16.418 22 12c0-5.523-4.477-10-10-10z" clip-rule="evenodd"/>
                    </svg>
                    <?= __("Phone Automation Setup Guide") ?>
                </a>
            </div>
        </div>

        <!-- Farm list rendered by JS -->
        <div id="user-farms-list"></div>

        <!-- Add / Edit form -->
        <div id="user-farm-form" style="display:none;"
             class="mt-4 p-4 bg-indigo-50 border border-indigo-200 rounded-lg">
            <h4 class="text-sm font-semibold text-gray-700 mb-3" id="user-farm-form-title"><?= __("Connect New Farm") ?></h4>
            <input type="hidden" id="uf-edit-id" value="">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1"><?= __("Farm Name") ?></label>
                    <input type="text" id="uf-name"
                           placeholder="<?= __("e.g. My Personal Farm") ?>"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1"><?= __("Farm API URL") ?></label>
                    <input type="url" id="uf-url"
                           placeholder="https://your-farm.example.com"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        <?= __("Screen URL") ?>
                        <span class="text-gray-400 font-normal">(<?= __("optional") ?>)</span>
                    </label>
                    <input type="url" id="uf-screen-url"
                           placeholder="https://screens.example.com"
                           class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400">
                </div>
            </div>
            <div id="uf-form-error" class="text-xs text-red-600 mb-2" style="display:none;"></div>
            <div class="flex gap-2 justify-end">
                <button type="button" id="btn-uf-cancel"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none transition-all">
                    <?= __("Cancel") ?>
                </button>
                <button type="button" id="btn-uf-save"
                        class="inline-flex items-center gap-1 px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 transition-all">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <?= __("Save") ?>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="grid gap-6">
    <?php if (!empty($Devices)): ?>
        <?php foreach($Devices as $device): ?>
            <div class="bg-white rounded-lg shadow-md p-6 device-card">
                <div class="flex justify-between items-start mb-6">
                    <div class="space-y-2">
                        <div class="flex items-center space-x-4">
                            <h3 class="text-lg font-semibold text-gray-800"><?= htmlchars($device->device_name) ?></h3>
                            <?php if (count($device->assigned_accounts) < 10): ?>
                                <button class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform transition-all duration-200 js-assign-account mr-2"
                                        data-device-id="<?= $device->device_id ?>">
                                    <svg class="w-4 h-4 mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    <?= __("Add Account") ?>
                                </button>
                            <?php endif; ?>
                            <?php if (!empty($device->screen_url)): ?>
                            <button class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transform transition-all duration-200 js-view-screen"
                                    data-device-id="<?= htmlchars($device->device_id) ?>"
                                    data-screen-url="<?= htmlchars($device->screen_url) ?>">
                                <svg class="w-4 h-4 mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <?= __("View Screen") ?>
                            </button>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center text-gray-500 text-sm">
                            <svg class="w-5 h-5 text-gray-400 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            ID: <?= htmlchars($device->device_id) ?>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium transform transition-all duration-200 hover:scale-105 <?= $device->status == 'connected' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                        <svg class="w-4 h-4 mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <?php if ($device->status == 'connected'): ?>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            <?php else: ?>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            <?php endif; ?>
                        </svg>
                        <?= ucfirst($device->status) ?>
                    </span>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-gray-50 rounded-lg p-4 flex flex-col items-center metric-card">
                        <svg class="w-6 h-6 text-blue-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span class="text-sm text-gray-500"><?= __("Accounts") ?></span>
                        <span class="font-semibold text-gray-900"><?= $device->account_count ?>/10</span>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 flex flex-col items-center metric-card">
                        <svg class="w-6 h-6 text-blue-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span class="text-sm text-gray-500"><?= __("Battery") ?></span>
                        <span class="font-semibold text-gray-900"><?= $device->metrics->battery_level->level ?>%</span>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 flex flex-col items-center metric-card">
                        <svg class="w-6 h-6 text-blue-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                        </svg>
                        <span class="text-sm text-gray-500"><?= __("RAM") ?></span>
                        <span class="font-semibold text-gray-900"><?= number_format($device->metrics->ram_usage->usage_percent, 1) ?>%</span>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 flex flex-col items-center metric-card">
                        <svg class="w-6 h-6 text-blue-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-sm text-gray-500"><?= __("Instagram") ?></span>
                        <span class="font-semibold text-gray-900">v<?= $device->metrics->instagram_version ?></span>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h4 class="flex items-center text-gray-900 font-medium">
                            <svg class="w-5 h-5 text-blue-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <?= __("Assigned Accounts") ?> (<?= count($device->assigned_accounts) ?>/10)
                        </h4>
                    </div>
                    
                    <div class="space-y-3">
                        <?php if (!empty($device->assigned_accounts)): ?>
                            <?php foreach($device->assigned_accounts as $account): 
                                // Find the account object from Accounts
                                $accountObj = null;
                                foreach($Accounts->getDataAs("Account") as $acc) {
                                    if ($acc->get("username") === $account) {
                                        $accountObj = $acc;
                                        break;
                                    }
                                }
                            ?>
                                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg transform transition-all duration-200 hover:bg-gray-100">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-medium transform transition-all duration-200 hover:rotate-12">
                                            <?= strtoupper(substr($account, 0, 1)) ?>
                                        </div>
                                        <span class="text-gray-900 font-medium"><?= htmlchars($account) ?></span>
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <button class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform transition-all duration-200 hover:scale-105 js-login-to-farm" 
                                                data-username="<?= htmlchars($account) ?>"
                                                data-device-id="<?= $device->device_id ?>"
                                                data-account-id="<?= $accountObj ? $accountObj->get("id") : '' ?>">
                                            <svg class="w-5 h-5 mr-2 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                            </svg>
                                            <?= __("Login to Farm") ?>
                                        </button>
                                        <button class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform transition-all duration-200 hover:scale-105 js-remove-account" 
                                                data-username="<?= htmlchars($account) ?>"
                                                data-device-id="<?= $device->device_id ?>">
                                            <svg class="w-5 h-5 mr-2 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            <?= __("Remove") ?>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p class="text-gray-500"><?= __("No accounts assigned to this device") ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="flex items-center text-gray-500 text-sm mt-6">
                    <svg class="w-5 h-5 text-gray-400 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <?= __("Last seen:") ?> <?= date("Y-m-d H:i", strtotime($device->last_seen)) ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="bg-white rounded-lg shadow-md p-8 text-center animate-fade-in">
            <div class="mx-auto flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 transform transition-all duration-300 hover:rotate-12">
                <svg class="w-8 h-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h3 class="mt-4 text-lg font-medium text-gray-900"><?= __("No Devices") ?></h3>
            <p class="mt-2 text-gray-500"><?= __("No devices are available at the moment.") ?></p>
            
            <div class="mt-6 bg-blue-50 border border-blue-100 rounded-lg p-4">
                <div class="flex items-center justify-center mb-3">
                    <svg class="w-5 h-5 text-blue-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-blue-800 font-medium"><?= __("Want to get started?") ?></span>
                </div>
                <p class="text-sm text-blue-600 mb-4">
                    <?= __("Learn about our hybrid phone farm options and device rental services.") ?>
                </p>
                <a href="https://sharingtools.services/support" 
                   target="_blank"
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform transition-all duration-200 hover:scale-105">
                    <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <?= __("Contact Support") ?>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Assignment Modal -->
<style>
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 50;
        padding: 1rem;
        overflow-y: auto;
    }

    .modal.is-active {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal__dialog {
        width: 100%;
        max-width: 500px;
        margin: auto;
    }
</style>

<div class="modal" id="assignment-modal">
    <div class="modal__dialog">
        <div class="bg-white rounded-lg shadow-xl transform transition-all duration-300">
            <input type="hidden" id="selected-device-id" value="">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <svg class="w-5 h-5 text-blue-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    <?= __("Add Account to Device") ?>
                </h3>
            </div>
            
            <div class="p-6">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <?= __("Available Accounts") ?>
                    </label>
                    <div class="mt-1 max-h-60 overflow-y-auto space-y-2">
                        <?php 
                        $hasAvailableAccounts = false;
                        foreach($Accounts->getDataAs("Account") as $account): 
                            $isAssigned = false;
                            foreach ($Devices as $d) {
                                if (in_array($account->get("username"), $d->assigned_accounts)) {
                                    $isAssigned = true;
                                    break;
                                }
                            }
                            if (!$isAssigned):
                                $hasAvailableAccounts = true;
                        ?>
                            <label class="relative flex items-center p-3 hover:bg-gray-50 cursor-pointer rounded-md transition-all duration-200 group">
                                <input type="radio" 
                                       name="account" 
                                       value="<?= $account->get("id") ?>" 
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300">
                                <div class="ml-3 flex items-center flex-grow">
                                    <div class="w-8 h-8 bg-blue-600 text-white rounded-full flex items-center justify-center font-medium transform transition-all duration-200 group-hover:rotate-12">
                                        <?= strtoupper(substr($account->get("username"), 0, 1)) ?>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900"><?= htmlchars($account->get("username")) ?></p>
                                    </div>
                                </div>
                            </label>
                        <?php 
                            endif;
                        endforeach; 
                        if (!$hasAvailableAccounts):
                        ?>
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p class="text-gray-500"><?= __("No available accounts to assign") ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                <button type="button" 
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform transition-all duration-200 hover:scale-105 modal__close">
                    <?= __("Cancel") ?>
                </button>
                <button type="button" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transform transition-all duration-200 hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
                        id="confirm-assignment"
                        disabled>
                    <?= __("Add Account") ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Screen Modal after the assignment modal -->
<div class="modal" id="screen-modal">
    <div class="modal__dialog" style="max-width: 450px; max-height: 95vh;">
        <div class="bg-white rounded-lg shadow-xl transform transition-all duration-300">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <svg class="w-5 h-5 text-indigo-500 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <?= __("Device Screen") ?>
                </h3>
                <button type="button" class="text-gray-400 hover:text-gray-500 focus:outline-none screen-modal__close">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="relative" style="height: 800px;">
                <iframe id="screen-iframe" 
                        class="w-full h-full"
                        style="border: none; transform: scale(1.1); transform-origin: top center;"
                        sandbox="allow-same-origin allow-scripts allow-forms allow-modals"
                        allow="clipboard-read; clipboard-write"
                        loading="lazy">
                </iframe>
            </div>
        </div>
    </div>
</div> 