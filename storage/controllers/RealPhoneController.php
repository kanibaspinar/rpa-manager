<?php
/**
 * RealPhone Controller
 */
class RealPhoneController extends Controller
{
    protected $resp;

    public function process()
    {
        $AuthUser      = $this->getVariable("AuthUser");
        $EmailSettings = \Controller::model("GeneralData", "email-settings");

        if (!$AuthUser) {
            header("Location: " . APPURL . "/login");
            exit;
        } elseif (
            !$AuthUser->isAdmin() &&
            !$AuthUser->isEmailVerified() &&
            $EmailSettings->get("data.email_verification")
        ) {
            header("Location: " . APPURL . "/profile?a=true");
            exit;
        } elseif ($AuthUser->isExpired()) {
            header("Location: " . APPURL . "/expired");
            exit;
        }

        $this->resp = new \stdClass();
        $this->initialize();

        if (Input::post("action")) {
            switch (Input::post("action")) {
                case 'get-user-devices':   $this->getUserDevices();          break;
                case 'assign-account':     $this->assignAccountToDevice();   break;
                case 'remove-account':     $this->removeAccountFromDevice(); break;
                case 'login-account':      $this->loginAccountToDevice();    break;
                default:
                    $this->resp->msg = __("Invalid action");
                    $this->jsonecho();
            }
        }

        $userId  = $AuthUser->get("id");
        $Devices = $this->buildUserDevices($userId);

        $Accounts = Controller::model("Accounts");
        $Accounts->setPage(Input::get("page"))
            ->where("slave", "=", 0)
            ->where("user_id", "=", $userId)
            ->orderBy("id", "DESC")
            ->fetchData();

        $RpaStats = Controller::model("Rpas");
        $RpaStats->orderBy("id", "DESC")->fetchData();

        // Load all of the user's own farm connections for the management UI
        $AllUserFarms = $this->loadAllUserFarms($userId);

        $this->setVariable("Devices",      $Devices)
             ->setVariable("Accounts",     $Accounts)
             ->setVariable("RpaStats",     $RpaStats)
             ->setVariable("AllUserFarms", $AllUserFarms)
             ->setVariable("Settings",     Controller::model("GeneralData", "settings"));

        $this->view("realphone");
    }

    // ── Private actions ───────────────────────────────────────────────────────

    private function initialize()
    {
        $this->resp->result = 0;
        $this->resp->msg    = "";
    }

    private function getUserDevices()
    {
        $AuthUser = $this->getVariable("AuthUser");
        $userId   = $AuthUser->get("id");

        try {
            $Devices = $this->buildUserDevices($userId);

            if ($Devices) {
                $this->resp->result  = 1;
                $this->resp->devices = $Devices;
            } else {
                throw new Exception(__("Failed to fetch devices"));
            }
        } catch (Exception $e) {
            $this->resp->msg = $e->getMessage();
        }

        $this->jsonecho();
    }

    private function assignAccountToDevice()
    {
        if (!Input::post("account_id") || !Input::post("device_id")) {
            $this->resp->msg = __("Account ID and Device ID are required!");
            $this->jsonecho();
        }

        $Account  = Controller::model("Account", Input::post("account_id"));
        $deviceId = Input::post("device_id");

        if (!$Account->isAvailable()) {
            $this->resp->msg = __("Invalid Account");
            $this->jsonecho();
        }

        // Find which farm node owns this device (checks admin nodes + user's own farms)
        $AuthUser = $this->getVariable("AuthUser");
        $node = $this->findDeviceNode($deviceId, (int)$AuthUser->get("id"));
        if (!$node) {
            $this->resp->msg = __("No farm nodes configured or device not found.");
            $this->jsonecho();
        }

        $apiBase = rtrim($node['url'], '/') . "/";

        try {
            // Persist RPA stat record with the correct farm URL
            $RpaStat = Controller::model("Rpa", $Account->get("username"));
            if (!$RpaStat->isAvailable()) {
                $RpaStat = Controller::model("Rpa");
                $RpaStat->set("username", $Account->get("username"))
                    ->set("deviceid", $deviceId)
                    ->set("serverurl", $apiBase)
                    ->save();
            } else {
                $RpaStat->delete();
                $RpaStat = Controller::model("Rpa");
                $RpaStat->set("username", $Account->get("username"))
                    ->set("deviceid", $deviceId)
                    ->set("serverurl", $apiBase)
                    ->save();
            }

            $loginData = $this->prepareLoginData($Account);
            $this->loginAccountToFarm($loginData, $deviceId, $Account);

            $this->resp->result = 1;
            $this->resp->msg    = __("Account assigned successfully");
        } catch (Exception $e) {
            $this->resp->msg = $e->getMessage();
        }

        $this->jsonecho();
    }

    private function removeAccountFromDevice()
    {
        if (!Input::post("username") || !Input::post("device_id")) {
            $this->resp->msg = __("Username and Device ID are required!");
            $this->jsonecho();
        }

        $username = Input::post("username");
        $deviceId = Input::post("device_id");

        $RpaStat = Controller::model("Rpa", $username);
        $apiUrl  = $RpaStat->get("serverurl") . "api/instagram/accounts/" . $username;

        try {
            $response = $this->makeApiRequest('DELETE', $apiUrl);

            if ($response && isset($response->success) && $response->success) {
                $RpaStat2 = Controller::model("Rpas");
                $RpaStat2->where("username", "=", $username)
                    ->where("deviceid", "=", $deviceId)
                    ->delete();

                $this->resp->result = 1;
                $this->resp->msg    = __("Account removed successfully");
            } else {
                throw new Exception(__("Failed to remove account"));
            }
        } catch (Exception $e) {
            $this->resp->msg = $e->getMessage();
        }

        $this->jsonecho();
    }

    private function loginAccountToDevice()
    {
        if (!Input::post("account_id") || !Input::post("device_id")) {
            $this->resp->msg = __("Account ID and Device ID are required!");
            $this->jsonecho();
        }

        $Account  = Controller::model("Account", Input::post("account_id"));
        $deviceId = Input::post("device_id");

        if (!$Account->isAvailable()) {
            $this->resp->msg = __("Invalid Account");
            $this->jsonecho();
        }

        try {
            $loginData = $this->prepareLoginData($Account);
            $response  = $this->loginAccountToFarm($loginData, $deviceId, $Account);

            if ($response && isset($response->success) && $response->success) {
                $this->resp->result = 1;
                $this->resp->msg    = __("Account log in process started successfully");
            } else {
                throw new Exception(__("Failed to log in process account"));
            }
        } catch (Exception $e) {
            $this->resp->msg = $e->getMessage();
        }

        $this->jsonecho();
    }

    // ── Farm node helpers ─────────────────────────────────────────────────────

    /**
     * Load all active farm nodes from np_rpa_farm_nodes.
     * Falls back to legacy rpa_manager_settings if table is empty.
     */
    private function getFarmNodes()
    {
        $table = TABLE_PREFIX . "rpa_farm_nodes";
        try {
            $rows = DB::table($table)
                ->where("is_active", "=", 1)
                ->orderBy("id", "ASC")
                ->select(["id", "url", "screen_url"])
                ->get();

            $nodes = [];
            foreach ((array)$rows as $row) {
                $url = is_array($row) ? ($row['url'] ?? '') : ($row->url ?? '');
                if (!$url) continue;
                $nodes[] = [
                    'id'         => (int)(is_array($row) ? ($row['id'] ?? 0) : ($row->id ?? 0)),
                    'url'        => trim($url),
                    'screen_url' => trim((string)(is_array($row) ? ($row['screen_url'] ?? '') : ($row->screen_url ?? ''))),
                ];
            }

            // Fallback to legacy settings if no nodes configured
            if (empty($nodes)) {
                $legacy = $this->getLegacyFarmUrl(1);
                if ($legacy) {
                    $nodes[] = ['id' => 0, 'url' => $legacy, 'screen_url' => $this->getLegacyScreenUrl(1)];
                }
                $legacy2 = $this->getLegacyFarmUrl(2);
                if ($legacy2) {
                    $nodes[] = ['id' => 0, 'url' => $legacy2, 'screen_url' => $this->getLegacyScreenUrl(2)];
                }
            }

            return $nodes;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Fetch devices for a user from ALL sources:
     * admin-configured farm nodes + user's own farm connections.
     * Each device gets a 'screen_url' and 'farm_source' property.
     */
    private function buildUserDevices($userId)
    {
        $adminNodes       = $this->getFarmNodes();
        $userFarms        = $this->getUserFarms($userId);
        $globalScreenBase = $this->getGlobalScreenBaseUrl();
        $Devices          = [];

        $sources = array_merge(
            array_map(function ($n) { $n['source'] = 'admin'; return $n; }, $adminNodes),
            array_map(function ($n) { $n['source'] = 'user';  return $n; }, $userFarms)
        );

        foreach ($sources as $node) {
            $apiUrl = rtrim($node['url'], '/') . "/api/devices/user/" . $userId;
            try {
                $response = $this->makeApiRequest('GET', $apiUrl);
                if (!empty($response->devices)) {
                    foreach ($response->devices as $dev) {
                        $dev->screen_url   = $node['screen_url'] ?: $globalScreenBase;
                        $dev->farm_node_id = $node['id'];
                        $dev->farm_source  = $node['source'];
                        $Devices[]         = $dev;
                    }
                }
            } catch (Exception $e) {
                // Farm unreachable — skip silently
            }
        }

        return $Devices;
    }

    /**
     * Iterate all sources (admin nodes + user farms) to find which one owns a device.
     */
    private function findDeviceNode($deviceId, $userId = 0)
    {
        $adminNodes = $this->getFarmNodes();
        $userFarms  = $userId > 0 ? $this->getUserFarms($userId) : [];
        $allSources = array_merge($adminNodes, $userFarms);

        if (empty($allSources)) return null;

        foreach ($allSources as $node) {
            $listUrl = rtrim($node['url'], '/') . "/api/devices/list";
            try {
                $check = $this->makeApiRequest('GET', $listUrl);
                if (!empty($check->devices)) {
                    foreach ($check->devices as $device) {
                        if (isset($device->device_id) && $device->device_id === $deviceId) {
                            return $node;
                        }
                    }
                }
            } catch (Exception $e) {
                // Farm unreachable — skip
            }
        }

        return $allSources[0];
    }

    /**
     * Load active user-owned farms for device fetching.
     */
    private function getUserFarms($userId)
    {
        $table = TABLE_PREFIX . "rpa_user_farms";
        try {
            $rows = DB::table($table)
                ->where("user_id", "=", $userId)
                ->where("is_active", "=", 1)
                ->orderBy("id", "ASC")
                ->select(["id", "url", "screen_url"])
                ->get();

            $farms = [];
            foreach ((array)$rows as $row) {
                $url = is_array($row) ? ($row['url'] ?? '') : ($row->url ?? '');
                if (!$url) continue;
                $farms[] = [
                    'id'         => (int)(is_array($row) ? ($row['id'] ?? 0) : ($row->id ?? 0)),
                    'url'        => trim($url),
                    'screen_url' => trim((string)(is_array($row) ? ($row['screen_url'] ?? '') : ($row->screen_url ?? ''))),
                ];
            }
            return $farms;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Load ALL user farms (including inactive) for the management UI.
     */
    private function loadAllUserFarms($userId)
    {
        $table = TABLE_PREFIX . "rpa_user_farms";
        try {
            $rows = DB::table($table)
                ->where("user_id", "=", $userId)
                ->orderBy("id", "ASC")
                ->select(["id", "name", "url", "screen_url", "is_active", "created_at"])
                ->get();

            $farms = [];
            foreach ((array)$rows as $row) {
                $farms[] = [
                    'id'         => (int)(is_array($row) ? ($row['id'] ?? 0) : ($row->id ?? 0)),
                    'name'       => (string)(is_array($row) ? ($row['name'] ?? '') : ($row->name ?? '')),
                    'url'        => (string)(is_array($row) ? ($row['url'] ?? '') : ($row->url ?? '')),
                    'screen_url' => (string)(is_array($row) ? ($row['screen_url'] ?? '') : ($row->screen_url ?? '')),
                    'is_active'  => (int)(is_array($row) ? ($row['is_active'] ?? 1) : ($row->is_active ?? 1)),
                ];
            }
            return $farms;
        } catch (Exception $e) {
            return [];
        }
    }

    // ── Account / login helpers ───────────────────────────────────────────────

    private function prepareLoginData($Account)
    {
        try {
            $password = \Defuse\Crypto\Crypto::decrypt(
                $Account->get("password"),
                \Defuse\Crypto\Key::loadFromAsciiSafeString(CRYPTO_KEY)
            );
        } catch (Exception $e) {
            throw new Exception(__("Encryption error"));
        }

        return [
            'username'       => $Account->get("username"),
            'password'       => $password,
            'email'          => $Account->get("data.imap_username") ?: "",
            'email_password' => $Account->get("data.imap_password") ?: "",
        ];
    }

    private function loginAccountToFarm($loginData, $deviceId, $Account)
    {
        $RpaStat = Controller::model("Rpa", $Account->get("username"));
        $apiUrl  = $RpaStat->get("serverurl") . "api/instagram/login";

        $data = array_merge($loginData, ['device_id' => $deviceId]);

        if (!$RpaStat->isAvailable()) {
            $RpaStat = Controller::model("Rpa");
            $RpaStat->set("username", $Account->get("username"))
                ->set("deviceid", $deviceId)
                ->set("serverurl", $RpaStat->get("serverurl"))
                ->save();
        } else {
            $RpaStat->set("deviceid", $deviceId)
                ->set("serverurl", $RpaStat->get("serverurl"))
                ->save();
        }

        return $this->makeApiRequest('POST', $apiUrl, $data);
    }

    // ── Settings helpers ──────────────────────────────────────────────────────

    /**
     * Global fallback screen base URL from rpa_manager_settings.
     */
    private function getGlobalScreenBaseUrl()
    {
        return $this->readSetting("screen_base_url");
    }

    /**
     * Legacy: read farm API URL from old rpa_manager_settings keys.
     */
    private function getLegacyFarmUrl($index = 1)
    {
        return $this->readSetting($index == 2 ? "farm_api_url_2" : "farm_api_url");
    }

    /**
     * Legacy: read screen base URL from old rpa_manager_settings keys.
     */
    private function getLegacyScreenUrl($index = 1)
    {
        return $this->readSetting($index == 2 ? "screen_base_url_2" : "screen_base_url");
    }

    private function readSetting($key)
    {
        $table = TABLE_PREFIX . "rpa_manager_settings";
        try {
            $rows = DB::table($table)
                ->where("name", "=", $key)
                ->limit(1)
                ->select(["value"])
                ->get();
            if (!empty($rows)) {
                $row = $rows[0];
                $val = is_array($row) ? ($row["value"] ?? "") : ($row->value ?? "");
                return trim((string)$val);
            }
        } catch (Exception $e) {}
        return "";
    }

    // ── HTTP helper ───────────────────────────────────────────────────────────

    private function makeApiRequest($method, $url, $data = null)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
        ]);

        switch (strtoupper($method)) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
            case 'DELETE':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
                if ($data !== null) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                }
                break;
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new \Exception(__("API request failed (HTTP $httpCode)"));
        }

        return $response !== '' ? json_decode($response) : null;
    }
}
