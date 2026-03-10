<?php
namespace Plugins\RpaManager;

// Disable direct access
if (!defined('APP_VERSION')) {
    die("Yo, what's up?");
}

/**
 * Admin-only AJAX controller to manage device assignments on the farm API.
 */
class AdminDevicesController extends \Controller
{
    /** @var array Parsed JSON/body payload */
    protected $payload = [];

    public function process()
    {
        header('Content-Type: application/json; charset=utf-8');

        $AuthUser = $this->getVariable("AuthUser");
        if (!$AuthUser || !method_exists($AuthUser, 'isAdmin') || !$AuthUser->isAdmin()) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Forbidden"], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $this->payload = $this->parsePayload();
        $action = isset($this->payload["action"]) ? $this->payload["action"] : (\Input::post("action") ?: \Input::get("action"));
        $action = trim((string)$action);

        try {
            switch ($action) {
                case "list_available":
                    $this->listAvailableDevices();
                    break;
                case "list_user_devices":
                    $this->listUserDevices();
                    break;
                case "assign_devices":
                    $this->assignDevices();
                    break;
                case "create_device":
                    $this->createDevice();
                    break;
                case "delete_device":
                    $this->deleteDevice();
                    break;
                default:
                    echo json_encode(["success" => false, "message" => "Invalid action"], JSON_UNESCAPED_UNICODE);
                    break;
            }
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                "success" => false,
                "message" => $e->getMessage(),
            ], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    /**
     * Parse JSON or form payload into an array.
     */
    protected function parsePayload()
    {
        $data = [];
        $ct = isset($_SERVER["CONTENT_TYPE"]) ? $_SERVER["CONTENT_TYPE"] : (isset($_SERVER["HTTP_CONTENT_TYPE"]) ? $_SERVER["HTTP_CONTENT_TYPE"] : "");
        if (strpos($ct, 'application/json') !== false) {
            $raw = file_get_contents("php://input");
            $json = json_decode($raw, true);
            if (is_array($json)) {
                $data = $json;
            }
        } else {
            $data = $_POST;
        }
        return is_array($data) ? $data : [];
    }

    /**
     * Helper to read a scalar parameter from payload or Input.
     */
    protected function param($name, $default = null)
    {
        if (isset($this->payload[$name])) {
            return $this->payload[$name];
        }
        $v = \Input::post($name);
        if ($v !== null && $v !== "") {
            return $v;
        }
        $v = \Input::get($name);
        return $v !== null && $v !== "" ? $v : $default;
    }

    protected function listAvailableDevices()
    {
        $base = $this->getFarmBaseUrl();
        if ($base === "") {
            echo json_encode(["success" => false, "message" => "Farm API URL is not configured."], JSON_UNESCAPED_UNICODE);
            return;
        }
        $url = rtrim($base, '/') . "/api/devices/list";
        $resp = $this->callFarm("GET", $url);
        echo json_encode($resp ?: ["success" => false, "message" => "Empty response"], JSON_UNESCAPED_UNICODE);
    }

    protected function listUserDevices()
    {
        $userId = (int) $this->param("user_id", 0);
        if ($userId <= 0) {
            echo json_encode(["success" => false, "message" => "user_id is required"], JSON_UNESCAPED_UNICODE);
            return;
        }
        $base = $this->getFarmBaseUrl();
        if ($base === "") {
            echo json_encode(["success" => false, "message" => "Farm API URL is not configured."], JSON_UNESCAPED_UNICODE);
            return;
        }
        $url = rtrim($base, '/') . "/api/devices/user/" . $userId;
        $resp = $this->callFarm("GET", $url);
        echo json_encode($resp ?: ["success" => false, "message" => "Empty response"], JSON_UNESCAPED_UNICODE);
    }

    protected function assignDevices()
    {
        $userId = (int) $this->param("user_id", 0);
        $count  = (int) $this->param("count", 1);
        if ($userId <= 0) {
            echo json_encode(["success" => false, "message" => "user_id is required"], JSON_UNESCAPED_UNICODE);
            return;
        }
        if ($count < 1) $count = 1;

        $base = $this->getFarmBaseUrl();
        if ($base === "") {
            echo json_encode(["success" => false, "message" => "Farm API URL is not configured."], JSON_UNESCAPED_UNICODE);
            return;
        }
        $url = rtrim($base, '/') . "/api/devices/assign";
        $body = [
            "user_id" => (string)$userId,
            "count"   => $count,
        ];
        $resp = $this->callFarm("POST", $url, $body);

        // After successful assignment, sync user's devices from farm into local DB
        if (is_array($resp) && !empty($resp["success"])) {
            $this->syncUserDevices($userId);
        }

        echo json_encode($resp ?: ["success" => false, "message" => "Empty response"], JSON_UNESCAPED_UNICODE);
    }

    protected function createDevice()
    {
        $deviceId = trim((string)$this->param("device_id", ""));
        $name     = trim((string)$this->param("name", ""));
        if ($deviceId === "" || $name === "") {
            echo json_encode(["success" => false, "message" => "device_id and name are required"], JSON_UNESCAPED_UNICODE);
            return;
        }

        $base = $this->getFarmBaseUrl();
        if ($base === "") {
            echo json_encode(["success" => false, "message" => "Farm API URL is not configured."], JSON_UNESCAPED_UNICODE);
            return;
        }
        $url = rtrim($base, '/') . "/api/devices/create";
        $body = [
            "device_id" => $deviceId,
            "name"      => $name,
        ];
        $resp = $this->callFarm("POST", $url, $body);
        echo json_encode($resp ?: ["success" => false, "message" => "Empty response"], JSON_UNESCAPED_UNICODE);
    }

    protected function deleteDevice()
    {
        $deviceId = trim((string)$this->param("device_id", ""));
        if ($deviceId === "") {
            echo json_encode(["success" => false, "message" => "device_id is required"], JSON_UNESCAPED_UNICODE);
            return;
        }

        $base = $this->getFarmBaseUrl();
        if ($base === "") {
            echo json_encode(["success" => false, "message" => "Farm API URL is not configured."], JSON_UNESCAPED_UNICODE);
            return;
        }
        $url = rtrim($base, '/') . "/api/devices/" . rawurlencode($deviceId);
        $resp = $this->callFarm("DELETE", $url);

        // If delete/unassign succeeded, remove local record
        if (is_array($resp) && !empty($resp["success"])) {
            try {
                \DB::table(TABLE_PREFIX . "rpa_assigned_devices")
                    ->where("device_id", "=", $deviceId)
                    ->delete();
            } catch (\Exception $e) {
                // ignore db cleanup failure
            }
        }

        echo json_encode($resp ?: ["success" => false, "message" => "Empty response"], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Pull devices for a user from farm API and mirror them into
     * local TABLE_PREFIX.'rpa_assigned_devices'.
     */
    protected function syncUserDevices($userId)
    {
        $base = $this->getFarmBaseUrl();
        if ($base === "") {
            return;
        }
        $url = rtrim($base, '/') . "/api/devices/user/" . $userId;
        $resp = $this->callFarm("GET", $url);
        if (!is_array($resp) || empty($resp["success"]) || empty($resp["devices"]) || !is_array($resp["devices"])) {
            return;
        }

        $table = TABLE_PREFIX . "rpa_assigned_devices";
        $now   = date("Y-m-d H:i:s");
        try {
            // Clear previous records for this user
            \DB::table($table)->where("user_id", "=", $userId)->delete();

            foreach ($resp["devices"] as $dev) {
                $deviceId   = isset($dev["device_id"]) ? (string)$dev["device_id"] : (isset($dev["id"]) ? (string)$dev["id"] : "");
                $deviceName = isset($dev["device_name"]) ? (string)$dev["device_name"] : (isset($dev["name"]) ? (string)$dev["name"] : "");
                if ($deviceId === "") {
                    continue;
                }
                \DB::table($table)->insert([
                    "user_id"     => (int)$userId,
                    "device_id"   => $deviceId,
                    "device_name" => $deviceName,
                    "created_at"  => $now,
                ]);
            }
        } catch (\Exception $e) {
            // ignore sync failures
        }
    }

    protected function getFarmBaseUrl()
    {
        $table = TABLE_PREFIX . "rpa_manager_settings";
        try {
            $rows = \DB::table($table)
                ->where("name", "=", "farm_api_url")
                ->limit(1)
                ->select(["value"])
                ->get();
            if (!empty($rows)) {
                $row = $rows[0];
                $val = is_array($row) ? ($row["value"] ?? "") : ($row->value ?? "");
                return trim((string)$val);
            }
        } catch (\Exception $e) {
            return "";
        }
        return "";
    }

    protected function callFarm($method, $url, $body = null)
    {
        $ch = curl_init();
        $opts = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
        ];
        $method = strtoupper($method);
        if ($method === "POST") {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = json_encode($body ?: []);
        } elseif ($method === "DELETE") {
            $opts[CURLOPT_CUSTOMREQUEST] = "DELETE";
            if ($body !== null) {
                $opts[CURLOPT_POSTFIELDS] = json_encode($body);
            }
        }

        curl_setopt_array($ch, $opts);
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ["success" => false, "message" => $err];
        }

        $data = $response !== '' ? json_decode($response, true) : null;
        if (!is_array($data)) {
            return ["success" => false, "message" => "Invalid JSON from farm API (HTTP $code)."];
        }
        return $data;
    }
}

