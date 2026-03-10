<?php
namespace Plugins\RpaManager;

const IDNAME = "rpa-manager";

// Disable direct access
if (!defined('APP_VERSION')) {
    die("Yo, what's up?");
}

/**
 * Event: plugin.install
 * - Create core np_rpa table if it does not exist
 * - Create module settings table
 * - Ensure RealPhone route exists
 * - Backup core realphone-related files into plugin storage
 */
function install($Plugin)
{
    if ($Plugin->get("idname") != IDNAME) {
        return false;
    }

    // Create np_rpa table (id, username, serverurl, deviceid, follow, story_view, story_like, start_time, end_time, account_problems, data, sync_time, data_send)
    $sql = "
        CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."rpa` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(255) NOT NULL,
            `serverurl` varchar(255) NOT NULL,
            `deviceid` varchar(255) NOT NULL,
            `follow` longtext NOT NULL,
            `story_view` longtext NOT NULL,
            `story_like` longtext NOT NULL,
            `start_time` longtext NOT NULL,
            `end_time` longtext NOT NULL,
            `account_problems` longtext NOT NULL,
            `data` longtext DEFAULT NULL,
            `sync_time` datetime DEFAULT NULL,
            `data_send` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // Mirror table name np_rpa (without prefix) for backwards compatibility with existing RpaModel usage.
    $sql_np = "
        CREATE TABLE IF NOT EXISTS `np_rpa` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `username` varchar(255) NOT NULL,
            `serverurl` varchar(255) NOT NULL,
            `deviceid` varchar(255) NOT NULL,
            `follow` longtext NOT NULL,
            `story_view` longtext NOT NULL,
            `story_like` longtext NOT NULL,
            `start_time` longtext NOT NULL,
            `end_time` longtext NOT NULL,
            `account_problems` longtext NOT NULL,
            `data` longtext DEFAULT NULL,
            `sync_time` datetime DEFAULT NULL,
            `data_send` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // Settings table for module (simple key/value)
    $sql_settings = "
        CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."rpa_manager_settings` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(191) NOT NULL,
            `value` text NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_name` (`name`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // Local record of device assignments per SaaS user (for future unassign logic/UI)
    $sql_assigned = "
        CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."rpa_assigned_devices` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `device_id` varchar(255) NOT NULL,
            `device_name` varchar(255) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_device` (`device_id`),
            KEY `idx_user` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    try {
        $pdo = \DB::pdo();
        foreach ([$sql, $sql_np, $sql_settings, $sql_assigned] as $q) {
            $stmt = $pdo->prepare($q);
            $stmt->execute();
            $stmt->closeCursor();
        }
    } catch (\PDOException $e) {}

    // On install, hydrate core files FROM the plugin's storage snapshot
    // into the main app, so new installations get the bundled versions.
    $storageBase = PLUGINS_PATH . "/" . IDNAME . "/storage";
    $map = [
        $storageBase . "/models/RpaModel.php"                 => APPPATH . "/models/RpaModel.php",
        $storageBase . "/models/RpasModel.php"                => APPPATH . "/models/RpasModel.php",
        $storageBase . "/controllers/RealPhoneController.php" => APPPATH . "/controllers/RealPhoneController.php",
        $storageBase . "/views/realphone.php"                 => APPPATH . "/views/realphone.php",
        $storageBase . "/views/fragments/realphone.fragment.php" => APPPATH . "/views/fragments/realphone.fragment.php",
    ];

    foreach ($map as $src => $dst) {
        if (!file_exists($src)) {
            continue;
        }
        $dstDir = dirname($dst);
        if (!is_dir($dstDir)) {
            @mkdir($dstDir, 0755, true);
        }
        // Always overwrite app copies with the module's storage snapshot,
        // even if destination files already exist.
        @copy($src, $dst);
    }
}
\Event::bind("plugin.install", __NAMESPACE__ . '\install');

/**
 * Event: plugin.remove
 * We intentionally keep tables and backups to avoid data loss.
 */
function uninstall($Plugin)
{
    if ($Plugin->get("idname") != IDNAME) {
        return false;
    }
}
\Event::bind("plugin.remove", __NAMESPACE__ . '\uninstall');

/**
 * Map routes
 */
function route_maps($global_variable_name)
{
    $router = $GLOBALS[$global_variable_name];

    // Admin settings UI for RPA Manager
    $router->map("GET|POST", "/e/".IDNAME."/settings/?", [
        PLUGINS_PATH . "/". IDNAME ."/controllers/SettingsController.php",
        __NAMESPACE__ . "\SettingsController"
    ]);

    // Admin devices management AJAX endpoint
    $router->map("GET|POST", "/e/".IDNAME."/devices-api/?", [
        PLUGINS_PATH . "/". IDNAME ."/controllers/AdminDevicesController.php",
        __NAMESPACE__ . "\AdminDevicesController"
    ]);

    // Ensure /realphone route exists and points to core RealPhoneController
    $router->map("GET|POST", "/realphone/?", [
        APPPATH . "/controllers/RealPhoneController.php",
        "RealPhoneController"
    ]);
}
\Event::bind("router.map", __NAMESPACE__ . '\route_maps');

/**
 * Add module entry to navigation (admin-only).
 */
function navigation_admin($Nav, $AuthUser)
{
    $idname = IDNAME;
    if (!isset($GLOBALS["_PLUGINS_"][$idname]["config"])) return;
    $is_admin = $AuthUser && method_exists($AuthUser, 'isAdmin') && $AuthUser->isAdmin();
    if (!$is_admin) return;
    include __DIR__ . "/views/fragments/navigation.fragment.php";
}
\Event::bind("navigation.add_special_menu", __NAMESPACE__ . '\navigation_admin');

/**
 * Add Real Phone Manager entry for non-admin users, redirecting to /realphone.
 */
function navigation_realphone($Nav, $AuthUser)
{
    $idname = IDNAME;
    if (!isset($GLOBALS["_PLUGINS_"][$idname]["config"])) return;
    if (!$AuthUser) return;

    $is_admin = method_exists($AuthUser, 'isAdmin') && $AuthUser->isAdmin();
    if ($is_admin) {
        // Admins use the full module navigation instead.
        return;
    }

    // Only show if module is enabled for the user's package.
    $user_modules = $AuthUser->get("settings.modules") ?: [];
    if (!in_array($idname, (array)$user_modules)) {
        return;
    }

    include __DIR__ . "/views/fragments/navigation-realphone.fragment.php";
}
\Event::bind("navigation.add_special_menu", __NAMESPACE__ . '\navigation_realphone');

/**
 * Add module to package options so admins can enable it per plan.
 */
function add_module_option($package_modules)
{
    $config = include __DIR__ . "/config.php";
    $idname = IDNAME;
    ?>
    <div class="mt-15">
        <label>
            <input type="checkbox" class="checkbox" name="modules[]" value="<?= $idname ?>" <?= in_array($idname, (array)$package_modules) ? "checked" : "" ?>>
            <span>
                <span class="icon unchecked"><span class="mdi mdi-check"></span></span>
                <?= __($config["plugin_name"] ?? "RPA Manager") ?>
            </span>
        </label>
    </div>
    <?php
}
\Event::bind("package.add_module_option", __NAMESPACE__ . '\add_module_option');

