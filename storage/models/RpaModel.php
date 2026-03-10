<?php 
	/**
	 * Account Model
	 *
	 * @version 1.0
	 * @author Onelab <hello@onelab.co> 
	 * 
	 */
	
	class RpaModel extends DataEntry
	{	
		  /**
     * Extend parents constructor and select entry
     * @param mixed $uniqid Value of the unique identifier
     */
    public function __construct($uniqid=0)
    {
        parent::__construct();
        $this->select($uniqid);
    }

    /**
     * Select entry with uniqid
     * @param  int|string $uniqid Value of the any unique field
     * @return self
     */
    public function select($uniqid)
    {
        if (is_int($uniqid) || ctype_digit($uniqid)) {
            $col = $uniqid > 0 ? "id" : null;
        } else {
            $col = "username";
        }

        if ($col) {
            $query = \DB::table("np_rpa")
                ->where($col, "=", $uniqid)
                ->limit(1)
                ->select("*");
            if ($query->count() == 1) {
                $resp = $query->get();
                $r = $resp[0];

                foreach ($r as $field => $value)
                    $this->set($field, $value);

                $this->is_available = true;
            } else {
                $this->data = array();
                $this->is_available = false;
            }
        }

        return $this;
    }


    /**
     * Extend default values
     * @return self
     */
    public function extendDefaults()
    {
        $defaults = array(
            "username" => "",
            "serverurl" => "",
            "deviceid" => "",
            "follow" =>  0,
			 "data" =>  0,
			 "sync_time" => date("Y-m-d H:i:s"),
            "story_view" => 0,
			"story_like" => "",
			"start_time" => "",
			"end_time" => "",
			"account_problems" =>  '[]',
			"data_send" =>  date("Y-m-d H:i:s")
        );


        foreach ($defaults as $field => $value) {
            if (is_null($this->get($field)))
                $this->set($field, $value);
        }
    }


    /**
     * Insert Data as new entry
     */
    public function insert()
    {
        if ($this->isAvailable())
            return false;

        $this->extendDefaults();

        $id = \DB::table(TABLE_PREFIX."rpa")
            ->insert(array(
                "id" => null,
                 "username" => $this->get("username"),
            "serverurl" => $this->get("serverurl"),
            "deviceid" => $this->get("deviceid"),
            "follow" =>  $this->get("follow"),
            "story_view" => $this->get("story_view"),
			"data" =>  $this->get("data"),
			 "sync_time" => $this->get("sync_time"),
			"story_like" => $this->get("story_like"),
			"start_time" => $this->get("start_time"),
			"end_time" => $this->get("end_time"),
			"account_problems" =>  $this->get("account_problems"),
			"data_send" =>   $this->get("data_send")
            ));

        $this->set("id", $id);
        $this->markAsAvailable();
        return $this->get("id");
    }


    /**
     * Update selected entry with Data
     */
    public function update()
    {
        if (!$this->isAvailable())
            return false;

        $this->extendDefaults();

        $id = \DB::table("np_rpa")
            ->where("id", "=", $this->get("id"))
            ->update(array(
                "username" => $this->get("username"),
            "serverurl" => $this->get("serverurl"),
            "deviceid" => $this->get("deviceid"),
            "follow" =>  $this->get("follow"),
			"data" =>  $this->get("data"),
			 "sync_time" => $this->get("sync_time"),
            "story_view" => $this->get("story_view"),
			"story_like" => $this->get("story_like"),
			"start_time" => $this->get("start_time"),
			"end_time" => $this->get("end_time"),
			"account_problems" =>  $this->get("account_problems"),
			"data_send" =>   $this->get("data_send")
            ));

        return $this;
    }


    /**
     * Remove selected entry from database
     */
    public function delete()
    {
        if(!$this->isAvailable())
            return false;

        \DB::table("np_rpa")->where("id", "=", $this->get("id"))->delete();
        $this->is_available = false;
        return true;
    }
}
?>