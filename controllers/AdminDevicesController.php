<?php
namespace Plugins\RpaManager;

// Disable direct access
if (!defined('APP_VERSION')) {
    die("Yo, what's up?");
}

/**
 * Admin-only AJAX controller to manage device assignments on farm nodes.
 *
 * All device actions require a farm_node_id so the correct farm API is targeted.
 * Actions: list_available, list_user_devices, assign_devices, create_device, delete_device
 */
class AdminDevicesController extends \Controller
{
    /** @var array Parsed JSON/form payload */
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
        $action = trim((string)$this->param("action", ""));

        try {
            switch ($action) {
                case "list_available":   $this->listAvailableDevices();  break;
                case "list_user_devices": $this->listUserDevices();      break;
                case "assign_devices":   $this->assignDevices();         break;
                case "create_device":    $this->createDevice();          break;
                case "delete_device":    $this->deleteDevice();          break;
                default:
                    echo json_encode(["success" => false, "message" => "Invalid action"], JSON_UNESCAPED_UNICODE);
            }
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // ── Actions ──────────────────────────────────────────────────────────────

    protected function listAvailableDevices()
    {
        $base = $this->requireFarmNodeUrl();
        if ($base === null) return;

        $url  = rtrim($base, '/') . "/api/devices/list";
        $resp = $this->callFarm("GET", $url);
        echo json_encode($resp ?: ["success" => false, "message" => "Empty response"], JSON_UNESCAPED_UNICODE);
    }

    protected function listUserDevices()
    {
        $userId = (int)$this->param("user_id", 0);
        if ($userId <= 0) {
            echo json_encode(["success" => false, "message" => "user_id is required"], JSON_UNESCAPED_UNICODE);
            return;
        }

        $base = $this->requireFarmNodeUrl();
        if ($base === null) return;

        $url  = rtrim($base, '/') . "/api/devices/user/" . $userId;
        $resp = $this->callFarm("GET", $url);
        echo json_encode($resp ?: ["success" => false, "message" => "Empty response"], JSON_UNESCAPED_UNICODE);
    }

    protected function assignDevices()
    {
        $userId = (int)$this->param("user_id", 0);
        $count  = (int)$this->param("count", 1);

        if ($userId <= 0) {
            echo json_encode(["success" => false, "message" => "user_id is required"], JSON_UNESCAPED_UNICODE);
            return;
        }
        if ($count < 1) $count = 1;

        $base = $this->requireFarmNodeUrl();
        if ($base === null) return;

        $url  = rtrim($base, '/') . "/api/devices/assign";
        $body = ["user_id" => (string)$userId, "count" => $count];
        $resp = $this->callFarm("POST", $url, $body);
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

        $base = $this->requireFarmNodeUrl();
        if ($base === null) return;

        $url  = rtrim($base, '/') . "/api/devices/create";
        $body = ["device_id" => $deviceId, "name" => $name];
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

        $base = $this->requireFarmNodeUrl();
        if ($base === null) return;

        $url  = rtrim($base, '/') . "/api/devices/" . rawurlencode($deviceId);
        $resp = $this->callFarm("DELETE", $url);

        echo json_encode($resp ?: ["success" => false, "message" => "Empty response"], JSON_UNESCAPED_UNICODE);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Validate farm_node_id param, look up the URL, and return it.
     * On failure, outputs JSON error and returns null.
     */
    protected function requireFarmNodeUrl()
    {
        $nodeId = (int)$this->param("farm_node_id", 0);
        if ($nodeId <= 0) {
            echo json_encode(["success" => false, "message" => "farm_node_id is required. Please select a farm node."], JSON_UNESCAPED_UNICODE);
            return null;
        }

        $url = $this->getFarmNodeUrl($nodeId);
        if ($url === "") {
            echo json_encode(["success" => false, "message" => "Farm node #$nodeId not found or is inactive."], JSON_UNESCAPED_UNICODE);
            return null;
        }

        return $url;
    }

    /**
     * Look up the base URL for a specific farm node.
     */
    protected function getFarmNodeUrl(int $nodeId): string
    {
        if ($nodeId <= 0) return "";
        $table = TABLE_PREFIX . "rpa_farm_nodes";
        try {
            $rows = \DB::table($table)
                ->where("id", "=", $nodeId)
                ->where("is_active", "=", 1)
                ->limit(1)
                ->select(["url"])
                ->get();
            if (!empty($rows)) {
                $row = $rows[0];
                $val = is_array($row) ? ($row["url"] ?? "") : ($row->url ?? "");
                return trim((string)$val);
            }
        } catch (\Exception $e) {
            return "";
        }
        return "";
    }

    protected function parsePayload()
    {
        $ct = isset($_SERVER["CONTENT_TYPE"]) ? $_SERVER["CONTENT_TYPE"] : "";
        if (strpos($ct, 'application/json') !== false) {
            $json = json_decode(file_get_contents("php://input"), true);
            return is_array($json) ? $json : [];
        }
        return is_array($_POST) ? $_POST : [];
    }

    protected function param($name, $default = null)
    {
        if (array_key_exists($name, $this->payload)) {
            return $this->payload[$name];
        }
        $v = \Input::post($name);
        if ($v !== null && $v !== "") return $v;
        $v = \Input::get($name);
        return ($v !== null && $v !== "") ? $v : $default;
    }

    protected function callFarm($method, $url, $body = null)
    {
        $ch   = curl_init();
        $opts = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Accept: application/json',
            ],
        ];

        $method = strtoupper($method);
        if ($method === "POST") {
            $opts[CURLOPT_POST]       = true;
            $opts[CURLOPT_POSTFIELDS] = json_encode($body ?: []);
        } elseif ($method === "DELETE") {
            $opts[CURLOPT_CUSTOMREQUEST] = "DELETE";
            if ($body !== null) {
                $opts[CURLOPT_POSTFIELDS] = json_encode($body);
            }
        }

        curl_setopt_array($ch, $opts);
        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($err) {
            return ["success" => false, "message" => $err];
        }

        $data = ($response !== '' && $response !== false) ? json_decode($response, true) : null;
        if (!is_array($data)) {
            return ["success" => false, "message" => "Invalid JSON response from farm API (HTTP $code)."];
        }
        return $data;
    }
}
