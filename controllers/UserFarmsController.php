<?php
namespace Plugins\RpaManager;

// Disable direct access
if (!defined('APP_VERSION')) {
    die("Yo, what's up?");
}

/**
 * AJAX controller for user-owned farm connections.
 * Any authenticated user (not admin-only) can manage their own farms.
 *
 * Actions: list, create, update, delete
 */
class UserFarmsController extends \Controller
{
    const TABLE = 'rpa_user_farms';

    /** @var array Parsed request payload */
    protected $payload = [];

    public function process()
    {
        header('Content-Type: application/json; charset=utf-8');

        $AuthUser = $this->getVariable("AuthUser");
        if (!$AuthUser) {
            http_response_code(403);
            echo json_encode(["success" => false, "message" => "Login required"], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $userId = (int)$AuthUser->get("id");

        $this->payload = $this->parsePayload();
        $action = trim((string)$this->param("action", ""));

        try {
            switch ($action) {
                case "list":   $this->listFarms($userId);   break;
                case "create": $this->createFarm($userId);  break;
                case "update": $this->updateFarm($userId);  break;
                case "delete": $this->deleteFarm($userId);  break;
                default:
                    echo json_encode(["success" => false, "message" => "Invalid action"], JSON_UNESCAPED_UNICODE);
            }
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    protected function listFarms($userId)
    {
        $table = TABLE_PREFIX . self::TABLE;
        try {
            $rows = \DB::table($table)
                ->where("user_id", "=", $userId)
                ->select(["id", "name", "url", "screen_url", "is_active", "created_at"])
                ->orderBy("id", "ASC")
                ->get();
        } catch (\Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            return;
        }

        $farms = [];
        foreach ((array)$rows as $row) {
            $farms[] = $this->rowToFarm($row);
        }
        echo json_encode(["success" => true, "farms" => $farms], JSON_UNESCAPED_UNICODE);
    }

    protected function createFarm($userId)
    {
        $name      = trim((string)$this->param("name", ""));
        $url       = trim((string)$this->param("url", ""));
        $screenUrl = trim((string)$this->param("screen_url", ""));

        if ($name === "" || $url === "") {
            echo json_encode(["success" => false, "message" => "Name and Farm API URL are required"], JSON_UNESCAPED_UNICODE);
            return;
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            echo json_encode(["success" => false, "message" => "Farm API URL must be a valid URL"], JSON_UNESCAPED_UNICODE);
            return;
        }
        if ($screenUrl !== "" && !filter_var($screenUrl, FILTER_VALIDATE_URL)) {
            echo json_encode(["success" => false, "message" => "Screen URL must be a valid URL"], JSON_UNESCAPED_UNICODE);
            return;
        }

        $table = TABLE_PREFIX . self::TABLE;
        $now   = date("Y-m-d H:i:s");

        try {
            \DB::table($table)->insert([
                "user_id"    => $userId,
                "name"       => $name,
                "url"        => $url,
                "screen_url" => $screenUrl !== "" ? $screenUrl : null,
                "is_active"  => 1,
                "created_at" => $now,
                "updated_at" => $now,
            ]);

            $newId = (int)\DB::pdo()->lastInsertId();

            echo json_encode([
                "success" => true,
                "message" => "Farm connected successfully.",
                "farm"    => [
                    "id"         => $newId,
                    "name"       => $name,
                    "url"        => $url,
                    "screen_url" => $screenUrl !== "" ? $screenUrl : null,
                    "is_active"  => 1,
                    "created_at" => $now,
                ],
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    protected function updateFarm($userId)
    {
        $id        = (int)$this->param("id", 0);
        $name      = trim((string)$this->param("name", ""));
        $url       = trim((string)$this->param("url", ""));
        $screenUrl = $this->param("screen_url") !== null ? trim((string)$this->param("screen_url")) : null;
        $isActive  = $this->param("is_active") !== null ? ($this->param("is_active") ? 1 : 0) : null;

        if ($id <= 0) {
            echo json_encode(["success" => false, "message" => "id is required"], JSON_UNESCAPED_UNICODE);
            return;
        }
        if ($name === "" || $url === "") {
            echo json_encode(["success" => false, "message" => "Name and Farm API URL are required"], JSON_UNESCAPED_UNICODE);
            return;
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            echo json_encode(["success" => false, "message" => "Farm API URL must be a valid URL"], JSON_UNESCAPED_UNICODE);
            return;
        }
        if ($screenUrl !== null && $screenUrl !== "" && !filter_var($screenUrl, FILTER_VALIDATE_URL)) {
            echo json_encode(["success" => false, "message" => "Screen URL must be a valid URL"], JSON_UNESCAPED_UNICODE);
            return;
        }

        $table = TABLE_PREFIX . self::TABLE;
        $now   = date("Y-m-d H:i:s");

        // Ownership check: user can only update their own farms
        $data = ["name" => $name, "url" => $url, "updated_at" => $now];
        if ($screenUrl !== null) {
            $data["screen_url"] = $screenUrl !== "" ? $screenUrl : null;
        }
        if ($isActive !== null) {
            $data["is_active"] = $isActive;
        }

        try {
            \DB::table($table)
                ->where("id", "=", $id)
                ->where("user_id", "=", $userId)
                ->update($data);

            $rows = \DB::table($table)
                ->where("id", "=", $id)
                ->where("user_id", "=", $userId)
                ->limit(1)
                ->select(["id", "name", "url", "screen_url", "is_active", "created_at"])
                ->get();

            $farm = !empty($rows) ? $this->rowToFarm($rows[0]) : null;
            echo json_encode(["success" => true, "message" => "Farm updated.", "farm" => $farm], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    protected function deleteFarm($userId)
    {
        $id = (int)$this->param("id", 0);
        if ($id <= 0) {
            echo json_encode(["success" => false, "message" => "id is required"], JSON_UNESCAPED_UNICODE);
            return;
        }

        $table = TABLE_PREFIX . self::TABLE;
        try {
            // Ownership check: only delete the user's own farm
            \DB::table($table)
                ->where("id", "=", $id)
                ->where("user_id", "=", $userId)
                ->delete();

            echo json_encode(["success" => true, "message" => "Farm disconnected."], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    protected function rowToFarm($row)
    {
        $get = function ($key) use ($row) {
            return is_array($row) ? ($row[$key] ?? null) : ($row->$key ?? null);
        };
        return [
            "id"         => (int)$get("id"),
            "name"       => (string)$get("name"),
            "url"        => (string)$get("url"),
            "screen_url" => (string)($get("screen_url") ?? ""),
            "is_active"  => (int)$get("is_active"),
            "created_at" => (string)$get("created_at"),
        ];
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
}
