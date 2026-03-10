<?php
/**
 * RealPhone Controller
 */
class RealPhoneController extends Controller
{
    /**
     * @var object
     */
    protected $resp;

    /**
     * Process
     */
    public function process()
    {
        $AuthUser = $this->getVariable("AuthUser");
        $Route = $this->getVariable("Route");
        $EmailSettings = \Controller::model("GeneralData", "email-settings");

        // Auth
        if (!$AuthUser){
            header("Location: ".APPURL."/login");
            exit;
        } else if (
            !$AuthUser->isAdmin() && 
            !$AuthUser->isEmailVerified() &&
            $EmailSettings->get("data.email_verification")) 
        {
            header("Location: ".APPURL."/profile?a=true");
            exit;
        } else if ($AuthUser->isExpired()) {
            header("Location: ".APPURL."/expired");
            exit;
        }

        $this->resp = new \stdClass();
        $this->initialize();

        // Handle API requests
        if (Input::post("action")) {
            $action = Input::post("action");
            switch ($action) {
                case 'get-user-devices':
                    $this->getUserDevices();
                    break;
                case 'assign-account':
                    $this->assignAccountToDevice();
                    break;
                case 'remove-account':
                    $this->removeAccountFromDevice();
                    break;
                case 'login-account':
                    $this->loginAccountToDevice();
                    break;
                default:
                    $this->resp->msg = __("Invalid action");
                    $this->jsonecho();
                    break;
            }
        }

        // Get farm base URLs from user preferences (if set) or RPA Manager settings
        $farmBase     = $this->getFarmBaseUrl(1, $AuthUser);
        $farmBase2    = $this->getFarmBaseUrl(2, $AuthUser);

        // Get user devices for view
        $userId = $AuthUser->get("id");
        $apiUrl  = rtrim($farmBase, '/') . "/api/devices/user/" . $userId;
        $apiUrl2 = $farmBase2 ? rtrim($farmBase2, '/') . "/api/devices/user/" . $userId : null;

        $response = $this->makeApiRequest('GET', $apiUrl);
			
        $Devices = [];
        if (!empty($response->devices)) {
            $Devices = array_merge($Devices, $response->devices);
        }
        // Optionally merge from second farm on initial load as well
        if ($apiUrl2) {
            $response2 = $this->makeApiRequest('GET', $apiUrl2);
            if (!empty($response2->devices)) {
                $Devices = array_merge($Devices, $response2->devices);
            }
        }

        // Get accounts for assignment
        $Accounts = Controller::model("Accounts");
        $Accounts->setPage(Input::get("page"))->where("slave", "=", 0)
                ->where("user_id", "=", $AuthUser->get("id"))
                ->orderBy("id","DESC")
                ->fetchData();

        // Get RPA stats
        $RpaStats = Controller::model("Rpas");
        $RpaStats->orderBy("id", "DESC")
                ->fetchData();

        // Screen base URLs from user preferences (if set) or RPA Manager settings (for global device screen links)
        $screenBase  = $this->getScreenBaseUrl(1, $AuthUser);
        $screenBase2 = $this->getScreenBaseUrl(2, $AuthUser);
          $farm_1 = 0;
        // Set view variables
        $this->setVariable("Devices", $Devices)
             ->setVariable("Accounts", $Accounts)
             ->setVariable("RpaStats", $RpaStats)
			 ->setVariable("FarmNumber", $farm_1)
             ->setVariable("Settings", Controller::model("GeneralData", "settings"))
             ->setVariable("ScreenBaseUrl", $screenBase)
             ->setVariable("ScreenBaseUrl2", $screenBase2);

        $this->view("realphone");
    }

    /**
     * Initialize
     */
    private function initialize()
    {
        $this->resp->result = 0;
        $this->resp->msg = "";
    }

    /**
     * Get user devices from RPA API
     */
    private function getUserDevices()
    {
        $AuthUser = $this->getVariable("AuthUser");
        $userId = $AuthUser->get("id");

        try {
            $farmBase  = $this->getFarmBaseUrl(1, $AuthUser);
            $farmBase2 = $this->getFarmBaseUrl(2, $AuthUser);

            $apiUrl  = rtrim($farmBase, '/') . "/api/devices/user/" . $userId;
            $apiUrl2 = $farmBase2 ? rtrim($farmBase2, '/') . "/api/devices/user/" . $userId : null;

            $response  = $this->makeApiRequest('GET', $apiUrl);
            $Devices   = [];
            if (!empty($response->devices)) {
                $Devices = array_merge($Devices, $response->devices);
            }
            if ($apiUrl2) {
                $response2 = $this->makeApiRequest('GET', $apiUrl2);
                if (!empty($response2->devices)) {
                    $Devices = array_merge($Devices, $response2->devices);
                }
            }

            if ($Devices && isset($Devices)) {
                $this->resp->result = 1;
                $this->resp->devices = $Devices;
            } else {
                throw new Exception(__("Failed to fetch devices"));
            }
        } catch (Exception $e) {
            $this->resp->msg = $e->getMessage();
        }

        $this->jsonecho();
    }

    /**
     * Assign account to device
     */
    private function assignAccountToDevice()
    {
        if (!Input::post("account_id") || !Input::post("device_id")) {
            $this->resp->msg = __("Account ID and Device ID are required!");
            $this->jsonecho();
        }

        $Account = Controller::model("Account", Input::post("account_id"));
        $deviceId = Input::post("device_id");

        if (!$Account->isAvailable()) {
            $this->resp->msg = __("Invalid Account");
            $this->jsonecho();
        }
		$farmBase  = $this->getFarmBaseUrl(1, $AuthUser);
        $farmBase2 = $this->getFarmBaseUrl(2, $AuthUser);
		$apiUrl1   = rtrim($farmBase, '/') . "/api/devices/list";
		$check_server = $this->makeApiRequest('GET', $apiUrl1);
$devices = $check_server->devices;  // Your JSON decoded data array
$deviceFound = false;

foreach ($devices as $device) {
    if ($device->device_id === $deviceId) {
        // ✅ Device exists — perform your update or logic here
        $deviceFound = true;
        break;
    }
}

if (!$deviceFound && $farmBase2) {
    $apiUrl = rtrim($farmBase2, '/') . "/";
} else {
	$apiUrl = rtrim($farmBase, '/') . "/";
}

        try {
           
			

            
                // Save RPA stats
				
			$RpaStat = Controller::model("Rpa", $Account->get("username"));
				if (!$RpaStat->isAvailable()) 
            {
                $RpaStat = Controller::model("Rpa");
                $RpaStat->set("username", $Account->get("username"))
                    ->set("deviceid", $deviceId)
                    ->set("serverurl", $apiUrl)
                    ->save();
			} else {
				$RpaStat->delete();
				$RpaStat = Controller::model("Rpa");
				$RpaStat->set("username", $Account->get("username"))
                    ->set("deviceid", $deviceId)
                    ->set("serverurl", $apiUrl)
                    ->save();
			}
			$loginData = $this->prepareLoginData($Account);
            $response = $this->loginAccountToFarm($loginData, $deviceId, $Account);

                $this->resp->result = 1;
                $this->resp->msg = __("Account assigned successfully");
           
        } catch (Exception $e) {
            $this->resp->msg = $e->getMessage();
        }

        $this->jsonecho();
    }

    /**
     * Prepare login data for account
     */
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
            'username' => $Account->get("username"),
            'password' => $password,
            'email' => $Account->get("data.imap_username") ?: "",
            'email_password' => $Account->get("data.imap_password") ?: "",
        ];
    }

    /**
     * Login account to farm device
     */
    private function loginAccountToFarm($loginData, $deviceId, $Account)
    {
		$RpaStat = Controller::model("Rpa", $Account->get("username"));
		


   $apiUrl = $RpaStat->get("serverurl") ."api/instagram/login";
  
		
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
   

    /**
     * Make API request to RPA server
     */
   private function makeApiRequest($method, $url, $data = null)
{
    $ch = curl_init();

    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
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
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); // optional body
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

    /**
     * Helper: get farm base URL #1 or #2 from global RPA Manager settings.
     */
    private function getFarmBaseUrl($index = 1, $AuthUser = null)
    {
        $table = TABLE_PREFIX . "rpa_manager_settings";
        $key   = $index == 2 ? "farm_api_url_2" : "farm_api_url";
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
        } catch (Exception $e) {
            // ignore and return empty
        }
        return "";
    }

    /**
     * Helper: get screen base URL #1 or #2 from global RPA Manager settings.
     */
    private function getScreenBaseUrl($index = 1, $AuthUser = null)
    {
        $table = TABLE_PREFIX . "rpa_manager_settings";
        $key   = $index == 2 ? "screen_base_url_2" : "screen_base_url";
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
        } catch (Exception $e) {
            // ignore and return empty
        }
        return "";
    }

    /**
     * Remove account from device
     */
    private function removeAccountFromDevice()
    {
        if (!Input::post("username") || !Input::post("device_id")) {
            $this->resp->msg = __("Username and Device ID are required!");
            $this->jsonecho();
        }

        $username = Input::post("username");
        $deviceId = Input::post("device_id");
		 
      $RpaStat = Controller::model("Rpa", $username);
   $apiUrl = $RpaStat->get("serverurl") ."api/instagram/accounts/". $username;
        try {
            
            
            
            $response = $this->makeApiRequest('DELETE', $apiUrl);

            if ($response && isset($response->success) && $response->success) {
                // Remove RPA stats
                $RpaStat = Controller::model("Rpas");
                $RpaStat->where("username", "=", $username)
                    ->where("deviceid", "=", $deviceId)
                    ->delete();

                $this->resp->result = 1;
                $this->resp->msg = __("Account removed successfully");
            } else {
                throw new Exception(__("Failed to remove account"));
            }
        } catch (Exception $e) {
            $this->resp->msg = $e->getMessage();
        }

        $this->jsonecho();
    }

    /**
     * Login account to device
     */
    private function loginAccountToDevice()
    {
        if (!Input::post("account_id") || !Input::post("device_id")) {
            $this->resp->msg = __("Account ID and Device ID are required!");
            $this->jsonecho();
        }

        $Account = Controller::model("Account", Input::post("account_id"));
        $deviceId = Input::post("device_id");
        
        if (!$Account->isAvailable()) {
            $this->resp->msg = __("Invalid Account");
            $this->jsonecho();
        }

        try {
            $loginData = $this->prepareLoginData($Account);
            $response = $this->loginAccountToFarm($loginData, $deviceId, $Account);

            if ($response && isset($response->success) && $response->success) {
                // Update RPA stats
               

                $this->resp->result = 1;
                $this->resp->msg = __("Account log in process started successfully");
            } else {
                throw new Exception(__("Failed to log in process account"));
            }
        } catch (Exception $e) {
            $this->resp->msg = $e->getMessage();
        }

        $this->jsonecho();
    }
} 