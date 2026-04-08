<?php
namespace Plugins\RpaManager;

const IDNAME = "rpa-manager";

// Disable direct access
if (!defined('APP_VERSION')) {
    die("Yo, what's up?");
}

/**
 * Event: plugin.install
 * - Create all required tables
 * - Migrate legacy farm_api_url setting to first farm node
 * - Hydrate core files from plugin storage snapshot
 */
function install($Plugin)
{
    if ($Plugin->get("idname") != IDNAME) {
        return false;
    }

    // Core RPA data table
    $sql_rpa = "
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

    // Settings key/value store (legacy + screen_base_url)
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

    // User-owned farm connections (each SaaS user can add their own farm)
    $sql_user_farms = "
        CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."rpa_user_farms` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `name` varchar(191) NOT NULL,
            `url` varchar(512) NOT NULL,
            `screen_url` varchar(512) DEFAULT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `idx_user` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    // Farm nodes: each entry is an independent phone farm API
    $sql_nodes = "
        CREATE TABLE IF NOT EXISTS `".TABLE_PREFIX."rpa_farm_nodes` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(191) NOT NULL,
            `url` varchar(512) NOT NULL,
            `screen_url` varchar(512) DEFAULT NULL,
            `is_active` tinyint(1) NOT NULL DEFAULT 1,
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    try {
        $pdo = \DB::pdo();

        foreach ([$sql_rpa, $sql_np, $sql_settings, $sql_user_farms, $sql_nodes] as $q) {
            $stmt = $pdo->prepare($q);
            $stmt->execute();
            $stmt->closeCursor();
        }

        // Migration: add farm_node_id to np_rpa tables so each row knows which farm node it belongs to.
        // np_rpa already has serverurl; farm_node_id is a convenient FK to np_rpa_farm_nodes.
        foreach ([TABLE_PREFIX . "rpa", "np_rpa"] as $tbl) {
            try {
                $check = $pdo->prepare("SHOW COLUMNS FROM `{$tbl}` LIKE 'farm_node_id'");
                $check->execute();
                if ($check->rowCount() === 0) {
                    $pdo->exec("ALTER TABLE `{$tbl}` ADD COLUMN `farm_node_id` int(11) DEFAULT NULL");
                }
                $check->closeCursor();
            } catch (\Exception $e) {
                // Table may not exist yet or alter failed — non-fatal
            }
        }

        // Migration: add screen_url to np_rpa_farm_nodes if it doesn't exist yet
        try {
            $check = $pdo->prepare("SHOW COLUMNS FROM `".TABLE_PREFIX."rpa_farm_nodes` LIKE 'screen_url'");
            $check->execute();
            if ($check->rowCount() === 0) {
                $pdo->exec("ALTER TABLE `".TABLE_PREFIX."rpa_farm_nodes` ADD COLUMN `screen_url` varchar(512) DEFAULT NULL AFTER `url`");
            }
            $check->closeCursor();
        } catch (\Exception $e) {}

        // Back-fill farm_node_id in np_rpa rows by matching serverurl against farm node URLs
        $rpaTable   = TABLE_PREFIX . "rpa";
        $nodesTable = TABLE_PREFIX . "rpa_farm_nodes";
        try {
            $nodeRows = $pdo->query("SELECT `id`, `url` FROM `{$nodesTable}`")->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($nodeRows as $nodeRow) {
                if (empty($nodeRow['url'])) continue;
                $upd = $pdo->prepare(
                    "UPDATE `{$rpaTable}` SET `farm_node_id` = ? WHERE `serverurl` = ? AND `farm_node_id` IS NULL"
                );
                $upd->execute([(int)$nodeRow['id'], trim($nodeRow['url'])]);
                $upd->closeCursor();
            }
        } catch (\Exception $e) {
            // Non-fatal backfill
        }

        // Migration: if old farm_api_url setting exists but no farm nodes yet, create first node
        $nodesTable    = TABLE_PREFIX . "rpa_farm_nodes";
        $settingsTable = TABLE_PREFIX . "rpa_manager_settings";

        $countStmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM `{$nodesTable}`");
        $countStmt->execute();
        $countRow = $countStmt->fetch(\PDO::FETCH_ASSOC);
        $countStmt->closeCursor();

        if (isset($countRow['cnt']) && (int)$countRow['cnt'] === 0) {
            $urlStmt = $pdo->prepare("SELECT `value` FROM `{$settingsTable}` WHERE `name` = 'farm_api_url' LIMIT 1");
            $urlStmt->execute();
            $urlRow = $urlStmt->fetch(\PDO::FETCH_ASSOC);
            $urlStmt->closeCursor();

            if (!empty($urlRow['value'])) {
                $now = date('Y-m-d H:i:s');
                $ins = $pdo->prepare(
                    "INSERT INTO `{$nodesTable}` (`name`, `url`, `is_active`, `created_at`, `updated_at`) VALUES (?, ?, 1, ?, ?)"
                );
                $ins->execute(["Primary Farm", trim($urlRow['value']), $now, $now]);
                $ins->closeCursor();

                // Also migrate farm_api_url_2 if set
                $url2Stmt = $pdo->prepare("SELECT `value` FROM `{$settingsTable}` WHERE `name` = 'farm_api_url_2' LIMIT 1");
                $url2Stmt->execute();
                $url2Row = $url2Stmt->fetch(\PDO::FETCH_ASSOC);
                $url2Stmt->closeCursor();

                if (!empty($url2Row['value'])) {
                    $ins2 = $pdo->prepare(
                        "INSERT INTO `{$nodesTable}` (`name`, `url`, `is_active`, `created_at`, `updated_at`) VALUES (?, ?, 1, ?, ?)"
                    );
                    $ins2->execute(["Secondary Farm", trim($url2Row['value']), $now, $now]);
                    $ins2->closeCursor();
                }
            }
        }

    } catch (\PDOException $e) {}

    // Hydrate core files from plugin storage snapshot
    $storageBase = PLUGINS_PATH . "/" . IDNAME . "/storage";
    $map = [
        $storageBase . "/models/RpaModel.php"                    => APPPATH . "/models/RpaModel.php",
        $storageBase . "/models/RpasModel.php"                   => APPPATH . "/models/RpasModel.php",
        $storageBase . "/controllers/RealPhoneController.php"    => APPPATH . "/controllers/RealPhoneController.php",
        $storageBase . "/views/realphone.php"                    => APPPATH . "/views/realphone.php",
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
        @copy($src, $dst);
    }
}
\Event::bind("plugin.install", __NAMESPACE__ . '\install');

/**
 * Event: plugin.remove
 * Tables and backups are intentionally kept to avoid data loss.
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

    // Admin settings UI
    $router->map("GET|POST", "/e/".IDNAME."/settings/?", [
        PLUGINS_PATH . "/". IDNAME ."/controllers/SettingsController.php",
        __NAMESPACE__ . "\SettingsController"
    ]);

    // Farm nodes CRUD AJAX
    $router->map("GET|POST", "/e/".IDNAME."/farm-nodes-api/?", [
        PLUGINS_PATH . "/". IDNAME ."/controllers/FarmNodesController.php",
        __NAMESPACE__ . "\FarmNodesController"
    ]);

    // User farm connections AJAX (any logged-in user)
    $router->map("GET|POST", "/e/".IDNAME."/user-farms-api/?", [
        PLUGINS_PATH . "/". IDNAME ."/controllers/UserFarmsController.php",
        __NAMESPACE__ . "\UserFarmsController"
    ]);

    // Device management AJAX
    $router->map("GET|POST", "/e/".IDNAME."/devices-api/?", [
        PLUGINS_PATH . "/". IDNAME ."/controllers/AdminDevicesController.php",
        __NAMESPACE__ . "\AdminDevicesController"
    ]);

    // Core RealPhone route
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
 * Add Real Phone Manager entry for non-admin users.
 */
function navigation_realphone($Nav, $AuthUser)
{
    $idname = IDNAME;
    if (!isset($GLOBALS["_PLUGINS_"][$idname]["config"])) return;
    if (!$AuthUser) return;

    $is_admin = method_exists($AuthUser, 'isAdmin') && $AuthUser->isAdmin();
    if ($is_admin) {
        return;
    }

    $user_modules = $AuthUser->get("settings.modules") ?: [];
    if (!in_array($idname, (array)$user_modules)) {
        return;
    }

    include __DIR__ . "/views/fragments/navigation-realphone.fragment.php";
}
\Event::bind("navigation.add_special_menu", __NAMESPACE__ . '\navigation_realphone');

/**
 * Add module to package options.
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
