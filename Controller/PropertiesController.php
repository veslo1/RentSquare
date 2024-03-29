<?php

class PropertiesController extends AppController{
	function beforeFilter(){
		parent::beforeFilter();

		$this->Auth->deny('*');
	}

	function add(){
		//	Make sure the user is a property manager.
		$this->managerCheck();
    $this->loadModel('State');
    $this->set('states',$this->State->find('list', array('fields' => array('State.id', 'State.full_name'))));
    $this->set('one_month_date',$this->add_month(date("F j, Y"),1));
    $MONTHLY_25_OR_LESS = MONTHLY_25_OR_LESS;
    $MONTHLY_26_TO_50 = MONTHLY_26_TO_50;
    $MONTHLY_51_TO_100 = MONTHLY_51_TO_100;
    $MONTHLY_101_TO_200 = MONTHLY_101_TO_200;
    $MONTHLY_201_TO_300 = MONTHLY_201_TO_300;
    $MONTHLY_301_TO_400 = MONTHLY_301_TO_400;
    $MONTHLY_OVER_400 = MONTHLY_OVER_400;
    $this->set(compact('MONTHLY_25_OR_LESS','MONTHLY_26_TO_50','MONTHLY_51_TO_100','MONTHLY_101_TO_200','MONTHLY_201_TO_300','MONTHLY_301_TO_400','MONTHLY_OVER_400'));    
		if(!empty($this->request->data))
    {
      $this->Property->set( $this->request->data );
      if ($this->Property->validates()) {
    			$data = $this->request->data;
    			
    			//Set Empty values
    			if(!isset($data['Property']['state_inc']) || $data['Property']['state_inc'] == '' || is_null($data['Property']['state_inc'])) $data['Property']['state_inc'] = 1;
            if(!isset($data['Property']['business_started'])) $data['Property']['business_started'] = 0;
            if(!isset($data['Property']['ownership_started'])) $data['Property']['ownership_started'] = 0;
            if(isset($data['Property']['name'])) $data['Property']['legal_dba'] = $data['Property']['name'];


    			//Set last 4 digits of account to save
    			$data['Property']['bank_acct'] = substr($data['Property']['bank_acccount_num'], -4, 4);
    			
    			//Set Manager Id
    			$data['Property']['manager_id'] = $this->Auth->user('id');
    			
                        /*
                         * 2014-09-15 Wolff - commented out - fee_due_day will be set when property is activated
                         */
    			//Set fee_due_day
    			//$data['Property']['fee_due_day'] = date('j');
    			
    			//Get User Data
    			$this->loadModel('User');
    			$user = $this->User->findById($this->Auth->user('id'));
          $data['User'] = $user['User'];
    			
    			//Add Property Manager's bank account to the Vault
    			$paymentmethod = array();
    			$paymentmethod['pp_user'] = RENTSQUARE_MERCH_USER;
          $paymentmethod['pp_password'] = RENTSQUARE_MERCH_PASS;
          $paymentmethod['first_name'] = $data['User']['first_name'];
          $paymentmethod['last_name'] = $data['User']['last_name'];
          $paymentmethod['account_number'] = $data['Property']['bank_acccount_num'];
          $paymentmethod['routing_number'] = $data['Property']['routing_number'];
          $paymentmethod['bank_acct_type'] = $data['Property']['bank_acccount_type'];
          $paymentmethod['phone'] = $data['User']['phone'];
          $paymentmethod['email'] = $data['User']['email'];
          $paymentmethod['user_id'] = $this->Auth->user('id');
          $this->loadModel('PaymentMethod');
    			$bank_saved = $this->PaymentMethod->add_bank_to_vault($paymentmethod);
    			if($bank_saved['response'] != 1){
      			$this->Session->setFlash('Failed to save Bank Account to Secure Vault - '.$bank_saved['message'].'. Please contact admin.','flash_bad');
    			} else {
    			  $data['Property']['vault_id'] = $bank_saved['vault_id'];
      		  
      		  
      		  //Save Property Manager in Database
      			if($this->Property->save($data))
      			{            
                //Set variable for application function. Set up for multiple properties array
                $application['Property'][0] = $data['Property'];
                $application['User'] = $data['User'];
                
                //Call Function to Submit Application to Phoenix Payments
                $results = $this->User->submitPPApplication($application);
                
                //Response 600 Error Message
                //Response 500 Internal Server Error
                //Response 200 Application Accepted
                $all_passed = true;
                
                foreach($results as $result):
                  if($result["response"] != 200){
                    $all_passed = false;
                  }
                endforeach; //foreach $results
                
                if($all_passed){
                  $this->Session->setFlash('Property Successfully added. Please allow 24-48 hours for processing. ','flash_good');
                } else {
                  $this->Session->setFlash('Error adding property. Please contact RentSquare Support. Error:'.var_dump($results),'flash_bad');
                }
      					
      			}
      			else
      			{
        			 $this->Session->setFlash('Error saving new property. Please contact RentSquare Support.','flash_bad');
      				 $this->redirect(array('controller' => 'Property', 'action' => 'add'));
      			}
      			
    			}
    			
    		} else {
      		//$errors = $this->User->invalidFields();
      		$this->Session->setFlash('Please enter all required fields','flash_bad');      		
        }
    }

	}

	function select($id = null){
		if(!empty($this->request->data))
			$id = $this->request->data['Property']['id'];
		if($id){
			if($id == 'new')
				$this->redirect(array('controller' => 'Properties', 'action' => 'add'));
      
			$this->Property->contain();
			$property = $this->Property->get($id, $this->Auth->user('id'));
			if($property){
				$this->Session->write('current_property', $property['Property']['id']);
				$this->redirect(array('controller' => 'Users' , 'action' => 'index'));
			}
			else
				$this->Session->setFlash("Sorry, that property does not exist in our database");
		}

		$this->redirect($this->referer());
	}
	
	function maupdatetenantbilling(){
  	if(!empty($this->request->data)){
  	$data = $this->request->data;
  	$this->Property->id = $data['Property']['id'];
			if($this->Property->save($data, true, array('before_due_days', 'before_due_reminder', 'invoice_day','day_rent_late','auto_late_fee','auto_late_fee_amt'))){
				$this->Session->setFlash('Tenant Billing Settings updated successfully!','flash_good');
				$this->redirect(array('controller' => 'Users' , 'action' => 'myaccount','tenant_billing'));
			}
		}
	}
	
	function maupdatetransfee(){
  	if(!empty($this->request->data)){
  	$data = $this->request->data;
  	$this->Property->id = $data['Property']['id'];
			if($this->Property->save($data, true, array('prop_pays_ach_fee', 'prop_pays_cc_fee'))){
				$this->Session->setFlash('Transaction Fees Settings updated successfully!','flash_good');
				$this->redirect(array('controller' => 'Users' , 'action' => 'myaccount','transaction_fees'));
			}
		}
	}

	private function add_month($date_str, $months)
  {
      $date = new DateTime($date_str);
      $start_day = $date->format('j');
  
      $date->modify("+{$months} month");
      $end_day = $date->format('j');
  
      //if ($start_day != $end_day)
      //    $date->modify('last day of last month');
  
      return $date;
  }
  
};

?>
