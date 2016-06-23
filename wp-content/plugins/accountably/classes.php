<?php
$path = $_SERVER['DOCUMENT_ROOT'];

include_once $path . '/wp-config.php';
include_once $path . '/wp-load.php';
include_once $path . '/wp-includes/wp-db.php';
include_once $path . '/wp-includes/pluggable.php';

global $wpdb;

Class User {

   public $UserId = 0;
   public $CreateTime = 0;
   public $UpdateTime;
   public $WPId;
   public $FirstName;
   public $LastName;
   public $Email;
   public $Phone;
   public $Age;
   public $JobTitle;
   public $Industry;
   public $Location;
   public $Goal;
   public $TeamId;
   public $Active;
   public $Notes;
   public $Available;

   public function GetByUser($WPId) {
      global $wpdb;
      $dbResult = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'accountably_user WHERE wp_id = \'$WPId\' LIMIT 1;');
      $this->DBToParams($dbResult);

      return $this; 
   }

   public function Save() {
      global $wpdb;
      
    $check = intval($wpdb->get_var("SELECT count(wp_id) FROM ".$wpdb->prefix."accountably_user WHERE wp_id = ".$this->WPId.";"));
    if($check > 0) {      
         $wpdb->Sql = 'UPDATE '.$wpdb->prefix.'accountably_user SET 
          wp_id               = \'' . $this->WPId . '\',
          first_name               = \'' . $this->FirstName . '\',
          last_name            = \'' . $this->LastName .'\',
          email            = \'' . $this->Email .'\',
          phone            = \'' . $this->Phone .'\',
          age            = \'' . $this->Age .'\',
          job_title             = \'' . $this->JobTitle .'\',
          industry             = \'' . $this->Industry .'\',
          location                = \'' . $this->Location .'\',
          goal       = \'' . $this->Goal .'\',
          team_id       = \'' . $this->TeamId .'\',
          active       = \'' . $this->Active .'\',
          notes       = \'' . $this->Notes .'\',
          available       = \'' . $this->Available .'\'
          WHERE wp_id = \'' . $this->WPId  . '\';';
                
      }
      else {
         $wpdb->Sql = 'INSERT INTO '.$wpdb->prefix.'accountably_user 
                                  (wp_id, create_time, first_name, last_name, email, phone, age, job_title, industry, location, goal, active) 
                                  VALUES
                                  (
                                    \'' . $this->WPId . '\',
                                    \'' . $this->CreateTime . '\',
                                    \'' . $this->FirstName . '\',
                                    \'' . $this->LastName .'\',
                                    \'' . $this->Email .'\',
                                    \'' . $this->Phone .'\',
                                    \'' . $this->Age .'\',
                                    \'' . $this->JobTitle .'\',
                                    \'' . $this->Industry .'\',
                                    \'' . $this->Location . '\',
                                    \'' . $this->Goal . '\',
                                    \'' . $this->Active .'\');';                  
      }
      $wpdb->query($wpdb->Sql);
      
      return $this;
   }

   private function DBToParams($objInstance) {
      $this->UserId             = $objInstance[0]->user_id;
      $this->WPId        = $objInstance[0]->wp_id;
      $this->CreateTime        = $objInstance[0]->create_time;
      $this->UpdateTime        = $objInstance[0]->update_time;
      $this->FirstName    = $objInstance[0]->first_name;
      $this->LastName      = $objInstance[0]->last_name;
      $this->Email      = $objInstance[0]->email;
      $this->Phone      = $objInstance[0]->phone;
      $this->Age       = $objInstance[0]->age;
      $this->JobTitle       = $objInstance[0]->job_title;
      $this->Industry         = $objInstance[0]->industry;
      $this->Location        = $objInstance[0]->location;
      $this->Goal        = $objInstance[0]->goal;
      $this->TeamId        = $objInstance[0]->team_id;
      $this->Active        = $objInstance[0]->active;
      $this->Notes        = $objInstance[0]->notes;
      $this->Available        = $objInstance[0]->available;
   }
}

Class Users {
  private $outputArray = array();
  
  public function GetByUser($WPId) {
      global $wpdb;
      $dbResult = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."accountably_user WHERE wp_id = '$WPId';");
      $this->DBToObjectArray($dbResult);

      return $this->outputArray; 
    }
  
  public function GetById($UserId) {
      global $wpdb;
      $dbResult = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."accountably_user WHERE user_id = '$UserId';");
      $this->DBToObjectArray($dbResult);

      return $this->outputArray; 
    }
    
    public function GetAll() {
      global $wpdb;
      $dbResult = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."accountably_user;");
      $this->DBToObjectArray($dbResult);

      return $this->outputArray; 
    }
    

    // This function needs to be updated to be based on available -----------
    public function GetAvailable() {
      global $wpdb;
      $dbResult = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."accountably_user WHERE available = '1';");
      $this->DBToObjectArray($dbResult);

      return $this->outputArray; 
    }
  
  private function DBToObjectArray($objInstance) {
    foreach($objInstance as $index => $instance) {
      $MyUser = new User();
        $MyUser->UserId             = $instance->user_id;
        $MyUser->WPId        = $instance->wp_id;
        $MyUser->CreateTime        = $instance->create_time;
        $MyUser->UpdateTime        = $instance->update_time;
        $MyUser->FirstName    = $instance->first_name;
        $MyUser->LastName      = $instance->last_name;
        $MyUser->Email      = $instance->email;
        $MyUser->Phone      = $instance->phone;
        $MyUser->Age       = $instance->age;
        $MyUser->JobTitle       = $instance->job_title;
        $MyUser->Industry         = $instance->industry;
        $MyUser->Location        = $instance->location;
        $MyUser->Goal       = $instance->goal;
        $MyUser->TeamId       = $instance->team_id;
        $MyUser->Active       = $instance->active;
        $MyUser->Notes       = $instance->notes;
        $MyUser->Available       = $instance->available;
        // 

        $this->outputArray[$index] = $MyUser;
    }
  }
}

Class Partner {

   public $UserId;
   public $FirstName;
   public $LastName;
   public $WPId;
   public $Email;
   public $Phone;
   public $Age;
   public $JobTitle;
   public $Industry;
   public $Location;
   public $Goal;
   public $TeamId;
   public $Available;
   public $PartnershipId;
   public $CreateTime;
   public $UpdateTime;
   public $Active;
   public $Health;
   public $Notes;

   public function GetById($UserId) {
      global $wpdb;
      $dbResult = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'partnerships_v WHERE user_id = \'$UserId\' LIMIT 1;');
      $this->DBToParams($dbResult);

      return $this; 
   }

   private function DBToParams($objInstance) {
      $this->UserId             = $objInstance[0]->user_id;
      $this->FirstName        = $objInstance[0]->first_name;
      $this->LastName        = $objInstance[0]->last_name;
      $this->WPId        = $objInstance[0]->wp_id;
      $this->Email      = $objInstance[0]->email;
      $this->Phone      = $objInstance[0]->phone;
      $this->Age       = $objInstance[0]->age;
      $this->JobTitle       = $objInstance[0]->job_title;
      $this->Industry         = $objInstance[0]->industry;
      $this->Location        = $objInstance[0]->location;
      $this->Goal        = $objInstance[0]->goal;
      $this->TeamId        = $objInstance[0]->team_id;
      $this->Available        = $objInstance[0]->available;
      $this->PartnershipId        = $objInstance[0]->partnership_id;
      $this->CreateTime        = $objInstance[0]->create_time;
      $this->UpdateTime        = $objInstance[0]->update_time;
      $this->Active        = $objInstance[0]->active;
      $this->Health        = $objInstance[0]->health;
      $this->Notes        = $objInstance[0]->notes;
   }
}

Class Partners {
	private $outputArray = array();
	
	public function GetByUser($WPId) {
      global $wpdb;
      $dbResult = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."partnerships_v WHERE wp_id = '$WPId';");
      $this->DBToObjectArray($dbResult);

      return $this->outputArray; 
    }
  
  public function GetById($UserId) {
      global $wpdb;
      $dbResult = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."partnerships_v WHERE user_id = '$UserId';");
      $this->DBToObjectArray($dbResult);

      return $this->outputArray; 
    }
	
	public function GetCopartner($UserId, $PartnershipId) {
      // $MyPartners = new Partner();
      // $MyPartners->GetById($UserID);
      // foreach($MyPartners as $MyPartner) {
      // }
      global $wpdb;
      $dbResult = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."partnerships_v WHERE partnership_id = '$PartnershipId' AND user_id != '$UserId';");
      $this->DBToObjectArray($dbResult);

      return $this->outputArray; 
    }
    
    public function GetAll() {
      global $wpdb;
      $dbResult = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."partnerships_v;");
      $this->DBToObjectArray($dbResult);

      return $this->outputArray; 
    }
	
	private function DBToObjectArray($objInstance) {
		foreach($objInstance as $index => $instance) {
			$MyPartner = new Partner();
        $MyPartner->UserId             = $instance->user_id;
        $MyPartner->FirstName        = $instance->first_name;
        $MyPartner->LastName        = $instance->last_name;
        $MyPartner->WPId        = $instance->wp_id;
        $MyPartner->Email      = $instance->email;
        $MyPartner->Phone      = $instance->phone;
        $MyPartner->Age       = $instance->age;
        $MyPartner->JobTitle       = $instance->job_title;
        $MyPartner->Industry         = $instance->industry;
        $MyPartner->Location        = $instance->location;
        $MyPartner->Goal        = $instance->goal;
        $MyPartner->TeamId        = $instance->team_id;
        $MyPartner->Available        = $instance->available;
        $MyPartner->PartnershipId        = $instance->partnership_id;
        $MyPartner->CreateTime        = $instance->create_time;
        $MyPartner->UpdateTime        = $instance->update_time;
        $MyPartner->Active        = $instance->active;
        $MyPartner->Health        = $instance->health;
        $MyPartner->Notes        = $instance->notes;
     		$this->outputArray[$index] = $MyPartner;
		}
	}
}

Class Partnership {

   public $PartnershipId = 0;
   public $CreateTime = 0;
   public $UpdateTime;
   public $Active;
   public $Health;
   public $Notes;

   public function GetByPartnership($PartnershipId) {
      global $wpdb;
      $dbResult = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'accountably_partnership WHERE partnership_id = \'$PartnershipId\' LIMIT 1;');
      $this->DBToParams($dbResult);

      return $this; 
   }

   public function Save() {
      global $wpdb;
      
    $check = intval($wpdb->get_var("SELECT count(partnership_id) FROM ".$wpdb->prefix."accountably_partnership WHERE partnership_id = ".$this->PartnershipId.";"));
    if($check > 0) {      
         $wpdb->Sql = 'UPDATE '.$wpdb->prefix.'accountably_partnership SET 
          partnership_id               = \'' . $this->WPId . '\',
          create_time               = \'' . $this->CreateTime . '\',
          update_time            = \'' . $this->UpdateTime .'\',
          active            = \'' . $this->Active .'\',
          health            = \'' . $this->Health .'\',
          notes       = \'' . $this->Notes .'\'
          WHERE partnership_id = \'' . $this->PartnershipId  . '\';';
                
      }
      else {
         $wpdb->Sql = 'INSERT INTO '.$wpdb->prefix.'accountably_partnership 
                                  (create_time, active, health, notes) 
                                  VALUES
                                  (
                                    \'' . $this->CreateTime . '\',
                                    \'' . $this->Active . '\',
                                    \'' . $this->Health .'\',
                                    \'' . $this->Notes .'\');';                  
      }
      $wpdb->query($wpdb->Sql);
      
      return $this;
   }

   //--------------- Need to write function to update Health -------

   private function DBToParams($objInstance) {
      $this->PartnershipId             = $objInstance[0]->partnership_id;
      $this->CreateTime        = $objInstance[0]->create_time;
      $this->UpdateTime        = $objInstance[0]->update_time;
      $this->Active        = $objInstance[0]->active;
      $this->Health        = $objInstance[0]->health;
      $this->Notes        = $objInstance[0]->notes;
   }
}

Class Partnerships {
  private $outputArray = array();
  
  public function GetByPartnership($PartnershipId) {
      global $wpdb;
      $dbResult = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."accountably_partnership WHERE partnership_id = '$PartnershipId';");
      $this->DBToObjectArray($dbResult);

      return $this->outputArray; 
    }
  
  public function GetByHealthRange($HealthStart, $HealthEnd) {
      global $wpdb;
      $dbResult = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."accountably_partnership WHERE health BETWEEN '$HealthStart' AND '$HealthEnd';");
      $this->DBToObjectArray($dbResult);

      return $this->outputArray; 
    }
    
    public function GetAllActive() {
      global $wpdb;
      $dbResult = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."accountably_partnership WHERE active = 1;");
      $this->DBToObjectArray($dbResult);

      return $this->outputArray; 
    }
    
    public function GetAll() {
      global $wpdb;
      $dbResult = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."accountably_partnership;");
      $this->DBToObjectArray($dbResult);

      return $this->outputArray; 
    }
  
  private function DBToObjectArray($objInstance) {
    foreach($objInstance as $index => $instance) {
      $MyPartnership = new Partnership();
        $MyPartnership->PartnershipId             = $instance->partnership_id;
        $MyPartnership->CreateTime        = $instance->create_time;
        $MyPartnership->UpdateTime        = $instance->update_time;
        $MyPartnership->Active       = $instance->active;
        $MyPartnership->Health       = $instance->health;
        $MyPartnership->Notes       = $instance->notes;
        // 

        $this->outputArray[$index] = $MyPartnership;
    }
  }
}

Class Checkin {

   public $Id;
   public $CreateTime = 0;
   public $UpdateTime;
   public $UserId;
   public $PartnershipId;
   public $PhaseId;
   public $PhaseName;
   public $PhaseValue;

   public function GetByCheckin($Id) {
      global $wpdb;
      $dbResult = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'accountably_checkins WHERE id = \'$Id\' LIMIT 1;');
      $this->DBToParams($dbResult);

      return $this; 
   }

   public function Save() {
      global $wpdb;
      
    $check = intval($wpdb->get_var("SELECT count(user_id) FROM ".$wpdb->prefix."accountably_checkins WHERE user_id = ".$this->UserId." AND phase_id = ".$PhaseID.";"));
    if($check > 0) {      
         $wpdb->Sql = 'UPDATE '.$wpdb->prefix.'accountably_checkins SET 
          phase_value       = \'' . $this->PhaseValue .'\'
          WHERE user_id = \'' . $this->UserId  . '\' AND
          partnership_id = \'' . $this->PartnershipId . '\' AND
          phase_id = \'' . $this->PhaseId . '\';';
                
      }
      else {
         $wpdb->Sql = 'INSERT INTO '.$wpdb->prefix.'accountably_checkins 
                                  (create_time, user_id, partnership_id, phase_id, phase_name, phase_value) 
                                  VALUES
                                  (
                                    \'' . $this->CreateTime . '\',
                                    \'' . $this->UserId . '\',
                                    \'' . $this->PartnershipId .'\',
                                    \'' . $this->PhaseId .'\',
                                    \'' . $this->PhaseName .'\',
                                    \'' . $this->PhaseValue .'\');';                  
      }
      $wpdb->query($wpdb->Sql);
      
      return $this;
   }

   private function DBToParams($objInstance) {
      $this->Id             = $objInstance[0]->id;
      $this->CreateTime        = $objInstance[0]->create_time;
      $this->UpdateTime        = $objInstance[0]->update_time;
      $this->UserId        = $objInstance[0]->user_id;
      $this->PartnershipId             = $objInstance[0]->partnership_id;
      $this->PhaseId        = $objInstance[0]->phase_id;
      $this->PhaseName        = $objInstance[0]->phase_name;
      $this->PhaseValue        = $objInstance[0]->phase_value;
   }
}

Class Checkins {
  private $outputArray = array();
  
  public function GetByPartnership($PartnershipId) {
      global $wpdb;
      $dbResult = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."accountably_checkins WHERE partnership_id = '$PartnershipId';");
      $this->DBToObjectArray($dbResult);

      return $this->outputArray; 
    }
  
  public function GetByHealthRange($HealthStart, $HealthEnd) {
      global $wpdb;
      $dbResult = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."accountably_checkins WHERE health BETWEEN '$HealthStart' AND '$HealthEnd';");
      $this->DBToObjectArray($dbResult);

      return $this->outputArray; 
    }
    
    public function GetAllActive() {
      global $wpdb;
      $dbResult = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."accountably_checkins WHERE active = 1;");
      $this->DBToObjectArray($dbResult);

      return $this->outputArray; 
    }
    
    public function GetAll() {
      global $wpdb;
      $dbResult = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."accountably_checkins;");
      $this->DBToObjectArray($dbResult);

      return $this->outputArray; 
    }
  
  private function DBToObjectArray($objInstance) {
    foreach($objInstance as $index => $instance) {
      $MyPartnership = new Partnership();
        $MyPartnership->PartnershipId             = $instance->partnership_id;
        $MyPartnership->CreateTime        = $instance->create_time;
        $MyPartnership->UpdateTime        = $instance->update_time;
        $MyPartnership->Active       = $instance->active;
        $MyPartnership->Health       = $instance->health;
        $MyPartnership->Notes       = $instance->notes;
        // 

        $this->outputArray[$index] = $MyPartnership;
    }
  }
}
?>