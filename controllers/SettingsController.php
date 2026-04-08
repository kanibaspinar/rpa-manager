<?php
namespace Plugins\RpaManager;

// Disable direct access
if (!defined('APP_VERSION')) {
    die("Yo, what's up?");
}

/**
 * Admin settings page for RPA Manager.
 * Handles screen_base_url form; farm node CRUD is done via FarmNodesController AJAX.
 */
class SettingsController extends \Controller
{
    const IDNAME        = 'rpa-manager';
    const TABLE_SETTINGS = 'rpa_manager_settings';
    const TABLE_NODES   = 'rpa_farm_nodes';

    public function process()
    {
        $AuthUser = $this->getVariable("AuthUser");
        if (!$AuthUser || !method_exists($AuthUser, 'isAdmin') || !$AuthUser->isAdmin()) {
            header("Location: " . APPURL . "/dashboard");
            exit;
        }

        $settings = $this->getSettings();
        $users    = $this->getUsersForAdmin();
        $nodes    = $this->getFarmNodes();

        if (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
            $screenBase = trim((string)\Input::post("screen_base_url"));

            $this->saveSetting('screen_base_url', $screenBase);

            $settings = $this->getSettings();
            $this->setVariable("success", __("Settings saved successfully."));
        }

        $this->setVariable("Settings",  $settings);
        $this->setVariable("Users",     $users);
        $this->setVariable("FarmNodes", $nodes);
        $this->setVariable("AuthUser",  $AuthUser);
        $this->setVariable("idname",    self::IDNAME);
        $this->view(PLUGINS_PATH . "/" . self::IDNAME . "/views/settings.php", "default");
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function getSettings()
    {
        $table = TABLE_PREFIX . self::TABLE_SETTINGS;
        try {
            $rows = \DB::table($table)->select(["name", "value"])->get();
        } catch (\Exception $e) {
            return [];
        }
        $out = [];
        foreach ((array)$rows as $row) {
            $name  = is_array($row) ? ($row["name"] ?? null) : ($row->name ?? null);
            $value = is_array($row) ? ($row["value"] ?? null) : ($row->value ?? null);
            if ($name !== null) {
                $out[$name] = $value;
            }
        }
        return $out;
    }

    protected function saveSetting(string $name, $value)
    {
        $table = TABLE_PREFIX . self::TABLE_SETTINGS;
        $now   = date('Y-m-d H:i:s');
        try {
            $exists = \DB::table($table)->where("name", "=", $name)->select(["id"])->get();
            if (!empty($exists)) {
                \DB::table($table)->where("name", "=", $name)->update([
                    "value"      => $value,
                    "updated_at" => $now,
                ]);
            } else {
                \DB::table($table)->insert([
                    "name"       => $name,
                    "value"      => $value,
                    "created_at" => $now,
                    "updated_at" => $now,
                ]);
            }
        } catch (\Exception $e) {
            // Non-fatal
        }
    }

    protected function getFarmNodes()
    {
        $table = TABLE_PREFIX . self::TABLE_NODES;
        try {
            $rows = \DB::table($table)
                ->select(["id", "name", "url", "screen_url", "is_active", "created_at"])
                ->orderBy("id", "ASC")
                ->get();
        } catch (\Exception $e) {
            return [];
        }

        $out = [];
        foreach ((array)$rows as $row) {
            $out[] = [
                "id"         => (int)(is_array($row) ? ($row["id"] ?? 0) : ($row->id ?? 0)),
                "name"       => (string)(is_array($row) ? ($row["name"] ?? "") : ($row->name ?? "")),
                "url"        => (string)(is_array($row) ? ($row["url"] ?? "") : ($row->url ?? "")),
                "screen_url" => (string)(is_array($row) ? ($row["screen_url"] ?? "") : ($row->screen_url ?? "")),
                "is_active"  => (int)(is_array($row) ? ($row["is_active"] ?? 1) : ($row->is_active ?? 1)),
            ];
        }
        return $out;
    }

    protected function getUsersForAdmin()
    {
        $table = TABLE_PREFIX . "users";
        try {
            $rows = \DB::table($table)
                ->select(["id"])
                ->orderBy("id", "ASC")
                ->get();
        } catch (\Exception $e) {
            return [];
        }

        $out = [];
        foreach ((array)$rows as $row) {
            $id = is_array($row) ? ($row["id"] ?? null) : ($row->id ?? null);
            if ($id === null) continue;

            $User = \Controller::model("User", (int)$id);
            if (!$User->isAvailable() || (method_exists($User, "isExpired") && $User->isExpired())) {
                continue;
            }
            $out[] = [
                "id"       => (int)$User->get("id"),
                "username" => (string)$User->get("username"),
                "email"    => (string)$User->get("email"),
            ];
        }
        return $out;
    }
}
