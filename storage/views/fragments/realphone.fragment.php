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
                            <button class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transform transition-all duration-200 js-view-screen"
                                    data-device-id="<?= $device->device_id ?>" >
                                <svg class="w-4 h-4 mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <?= __("View Screen F1") ?>
                            </button>
							<button class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transform transition-all duration-200 js-view-screen2"
                                    data-device-id="<?= $device->device_id ?>" >
                                <svg class="w-4 h-4 mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <?= __("View Screen F2") ?>
                            </button>
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