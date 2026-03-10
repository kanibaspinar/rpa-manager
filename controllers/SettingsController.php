<?php
namespace Plugins\RpaManager;

// Disable direct access
if (!defined('APP_VERSION')) {
    die("Yo, what's up?");
}

/**
 * Admin settings for RPA Manager (farm API URL and related options)
 */
class SettingsController extends \Controller
{
    const IDNAME = 'rpa-manager';
    const TABLE_SETTINGS = 'rpa_manager_settings';

    public function process()
    {
        $AuthUser = $this->getVariable("AuthUser");
        if (!$AuthUser || !method_exists($AuthUser, 'isAdmin') || !$AuthUser->isAdmin()) {
            header("Location: " . APPURL . "/dashboard");
            exit;
        }

        $settings = $this->getSettings();
        $users    = $this->getUsersForAdmin();

        if (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'POST') {
            $farmUrl    = trim(\Input::post("farm_api_url"));
            $farmUrl2   = trim(\Input::post("farm_api_url_2"));
            $screenBase = trim(\Input::post("screen_base_url"));

            $this->saveSettings([
                'farm_api_url'    => $farmUrl,
                'farm_api_url_2'  => $farmUrl2,
                'screen_base_url' => $screenBase,
            ]);

            $settings = $this->getSettings();
            $this->setVariable("success", __("Settings saved successfully."));
        }

        $this->setVariable("Settings", $settings);
        $this->setVariable("Users", $users);
        $this->setVariable("AuthUser", $AuthUser);
        $this->setVariable("idname", self::IDNAME);
        $this->view(PLUGINS_PATH . "/" . self::IDNAME . "/views/settings.php", "default");
    }

    protected function getSettings()
    {
        $table = TABLE_PREFIX . self::TABLE_SETTINGS;
        try {
            $rows = \DB::table($table)->select(["name", "value"])->get();
        } catch (\Exception $e) {
            return [];
        }
        $out = [];
        foreach ($rows as $row) {
            $name = is_array($row) ? ($row["name"] ?? null) : ($row->name ?? null);
            $value = is_array($row) ? ($row["value"] ?? null) : ($row->value ?? null);
            if ($name !== null) {
                $out[$name] = $value;
            }
        }
        return $out;
    }

    protected function saveSettings(array $data)
    {
        $table = TABLE_PREFIX . self::TABLE_SETTINGS;
        $now = date('Y-m-d H:i:s');
        foreach ($data as $name => $value) {
            try {
                $exists = \DB::table($table)
                    ->where("name", "=", $name)
                    ->select(["id"])
                    ->get();
                if (!empty($exists)) {
                    \DB::table($table)
                        ->where("name", "=", $name)
                        ->update([
                            "value" => $value,
                            "updated_at" => $now,
                        ]);
                } else {
                    \DB::table($table)->insert([
                        "name" => $name,
                        "value" => $value,
                        "created_at" => $now,
                        "updated_at" => $now,
                    ]);
                }
            } catch (\Exception $e) {
                // ignore single-setting failures
            }
        }
    }

    /**
     * Load a lightweight list of users for the admin device manager UI.
     */
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
        foreach ($rows as $row) {
            $id = is_array($row) ? ($row["id"] ?? null) : ($row->id ?? null);
            if ($id === null) {
                continue;
            }
            $User = \Controller::model("User", (int)$id);
            if (!$User->isAvailable() || (method_exists($User, "isExpired") && $User->isExpired())) {
                // Skip expired or unavailable users
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

