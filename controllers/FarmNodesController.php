<?php
namespace Plugins\RpaManager;

// Disable direct access
if (!defined('APP_VERSION')) {
    die("Yo, what's up?");
}

/**
 * Admin-only AJAX controller to manage farm nodes (phone farm API URLs).
 *
 * Actions:
 *   list   → return all nodes
 *   create → insert new node (name, url, is_active)
 *   update → update node    (id, name, url, is_active)
 *   delete → delete node    (id)
 */
class FarmNodesController extends \Controller
{
    const TABLE_NODES = 'rpa_farm_nodes';

    /** @var array Parsed request payload */
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
                case "list":   $this->listNodes();   break;
                case "create": $this->createNode();  break;
                case "update": $this->updateNode();  break;
                case "delete": $this->deleteNode();  break;
                default:
                    echo json_encode(["success" => false, "message" => "Invalid action"], JSON_UNESCAPED_UNICODE);
            }
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(["success" => false, "message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
        exit;
    }

    protected function listNodes()
    {
        $table = TABLE_PREFIX . self::TABLE_NODES;
        try {
            $rows = \DB::table($table)
                ->select(["id", "name", "url", "screen_url", "is_active", "created_at", "updated_at"])
                ->orderBy("id", "ASC")
                ->get();
        } catch (\Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
            return;
        }

        $nodes = [];
        foreach ((array)$rows as $row) {
            $nodes[] = $this->rowToNode($row);
        }

        echo json_encode(["success" => true, "nodes" => $nodes], JSON_UNESCAPED_UNICODE);
    }

    protected function createNode()
    {
        $name      = trim((string)$this->param("name", ""));
        $url       = trim((string)$this->param("url", ""));
        $screenUrl = trim((string)$this->param("screen_url", ""));
        $isActive  = $this->param("is_active", 1) ? 1 : 0;

        if ($name === "" || $url === "") {
            echo json_encode(["success" => false, "message" => "name and url are required"], JSON_UNESCAPED_UNICODE);
            return;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            echo json_encode(["success" => false, "message" => "url must be a valid URL"], JSON_UNESCAPED_UNICODE);
            return;
        }

        if ($screenUrl !== "" && !filter_var($screenUrl, FILTER_VALIDATE_URL)) {
            echo json_encode(["success" => false, "message" => "screen_url must be a valid URL"], JSON_UNESCAPED_UNICODE);
            return;
        }

        $table = TABLE_PREFIX . self::TABLE_NODES;
        $now   = date("Y-m-d H:i:s");

        try {
            \DB::table($table)->insert([
                "name"       => $name,
                "url"        => $url,
                "screen_url" => $screenUrl !== "" ? $screenUrl : null,
                "is_active"  => $isActive,
                "created_at" => $now,
                "updated_at" => $now,
            ]);

            $pdo  = \DB::pdo();
            $newId = (int)$pdo->lastInsertId();

            echo json_encode([
                "success" => true,
                "message" => "Farm node created.",
                "node"    => [
                    "id"         => $newId,
                    "name"       => $name,
                    "url"        => $url,
                    "screen_url" => $screenUrl !== "" ? $screenUrl : null,
                    "is_active"  => $isActive,
                    "created_at" => $now,
                    "updated_at" => $now,
                ],
            ], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    protected function updateNode()
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
            echo json_encode(["success" => false, "message" => "name and url are required"], JSON_UNESCAPED_UNICODE);
            return;
        }
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            echo json_encode(["success" => false, "message" => "url must be a valid URL"], JSON_UNESCAPED_UNICODE);
            return;
        }
        if ($screenUrl !== null && $screenUrl !== "" && !filter_var($screenUrl, FILTER_VALIDATE_URL)) {
            echo json_encode(["success" => false, "message" => "screen_url must be a valid URL"], JSON_UNESCAPED_UNICODE);
            return;
        }

        $table = TABLE_PREFIX . self::TABLE_NODES;
        $now   = date("Y-m-d H:i:s");

        $data = ["name" => $name, "url" => $url, "updated_at" => $now];
        if ($screenUrl !== null) {
            $data["screen_url"] = $screenUrl !== "" ? $screenUrl : null;
        }
        if ($isActive !== null) {
            $data["is_active"] = $isActive;
        }

        try {
            \DB::table($table)->where("id", "=", $id)->update($data);

            // Reload the row
            $rows = \DB::table($table)
                ->where("id", "=", $id)
                ->limit(1)
                ->select(["id", "name", "url", "screen_url", "is_active", "created_at", "updated_at"])
                ->get();

            $node = !empty($rows) ? $this->rowToNode($rows[0]) : null;

            echo json_encode(["success" => true, "message" => "Farm node updated.", "node" => $node], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    protected function deleteNode()
    {
        $id = (int)$this->param("id", 0);
        if ($id <= 0) {
            echo json_encode(["success" => false, "message" => "id is required"], JSON_UNESCAPED_UNICODE);
            return;
        }

        $table = TABLE_PREFIX . self::TABLE_NODES;

        try {
            // Check if any accounts in np_rpa are still linked to this node
            $rpaTable = TABLE_PREFIX . "rpa";
            $assigned = \DB::table($rpaTable)
                ->where("farm_node_id", "=", $id)
                ->select(["id"])
                ->get();

            if (!empty($assigned)) {
                echo json_encode([
                    "success" => false,
                    "message" => "Cannot delete: " . count($assigned) . " account(s) are still linked to this farm node. Unassign them first.",
                ], JSON_UNESCAPED_UNICODE);
                return;
            }

            \DB::table($table)->where("id", "=", $id)->delete();
            echo json_encode(["success" => true, "message" => "Farm node deleted."], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            echo json_encode(["success" => false, "message" => $e->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    protected function rowToNode($row)
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
            "updated_at" => (string)$get("updated_at"),
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
