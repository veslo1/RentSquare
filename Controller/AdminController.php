<?php
App::uses('Sanitize', 'Utility');
App::uses('Security', 'Utility');
Configure::write('RentSquare.supportemail', 'support@rentsquare.co');

//Security::rijndael($user['Property']['pp_pass'], Configure::read('Security.salt2'),'encrypt')

class AdminController extends AppController  
{  
    var $uses = array();  
    var $components = array('RequestHandler');  

    function index(){  
        $this->adminCheck();
        $this->loadModel('Property');
        $this->paginate = array(
            //'conditions' => $conditions,
            'contain' => array(
              'Manager','State','Tenant'
            ),
            'limit' => 15,
            'order' => array(
                'Property.created' => 'desc'
            ),
            'conditions'=>array('Property.active'=>1)
        );
        $this->Paginator->settings = $this->paginate;
    		$this->set('properties', $this->Paginator->paginate('Property'));
    		$this->set('inactive_properties', $this->Property->find('count',array('conditions'=>array('Property.active'=>0))));

        $this->set('subheader','subnav_property');
        $this->set('activate_property',true);

    }  

    function properties($manager_id = null)
    {
       $this->adminCheck();
       if($manager_id != null)
       {
          $this->loadModel('User');
          $this->User->contain();
          $this->set('manager',$this->User->findById($manager_id));
 
          $this->loadModel('Property');
          $this->paginate = array(
             'conditions' => array('Property.manager_id'=>$manager_id,'Property.active'=>'1'),
             'contain' => array('State'),
             'limit' => 10,
             'order' => array(
                 'Property.name' => 'asc'
             ));
          $this->Paginator->settings = $this->paginate;
          $this->set('properties', $this->Paginator->paginate('Property'));
       }
    }
    
    function payments($property_id = null,$prop_name = null){
      $this->adminCheck();
      if($property_id != null){
      $this->loadModel('Property');
        $this->set('manager',$this->Property->find('all',array('conditions'=>array('Property.id'=>$property_id),'contain'=>'Manager')));
        $this->loadModel('Payment');
        $this->paginate = array(
            'conditions' => array('Property.id'=>$property_id),
            'joins' => array(
              array(
                'alias' => 'RSUnit',
                'table' => 'units',
                'type' => 'INNER',
                'conditions' => 'RSUnit.id = Payment.unit_id'
              ),
              array(
                'alias' => 'Property',
                'table' => 'properties',
                'type' => 'INNER',
                'conditions' => 'Property.id = RSUnit.property_id'
              )
            ),

            'contain' => array(
              'User'=>array('fields'=>array('first_name','last_name')),
              'Unit'=>array('fields'=>array('number')),
            ),
            'limit' => 25,
            'order' => array(
                'Payment.id' => 'desc'
            )
        );
        $this->Paginator->settings = $this->paginate;
    		$this->set('payments', $this->Paginator->paginate('Payment'));
    		$this->set('prop_name',$prop_name);
    		$this->set('property_id',$property_id);
      }
    }
    
    function billing($id = null){
      $this->adminCheck();
      $this->loadModel('Billing');
      $this->Billing->id = $id;
  		if (!$this->Billing->exists()) {
  			throw new NotFoundException(__('Invalid billing cycle'));
  		}
  		$this->set('Billing', $this->Billing->find('first', array(
  		  'contain' => array(
            'Payment'=>array('User'),
            'Unit'=>array('Tenant','FreeRent','UnitFee'),
            'BillingFee'
          ),
           'conditions' => array('Billing.id'=>$id),
  		)
  		));  
    }
    
    /* Edit Merchant account associated with property */
    function editmerchantacct($id = null){
      $this->adminCheck();
      $this->loadModel('Property');
      if($id != null){
        $this->set('property', $this->Property->findById($id));  
      }
      if(!empty($this->request->data)){
          $data = $this->request->data;
          $this->Property->id = $data['Property']['id'];
          $data['Property']['pp_pass'] = Security::rijndael($data['Property']['pp_pass'], Configure::read('Security.salt2'),'encrypt');
          if($this->Property->save($data)){
            $this->Session->setFlash('Merchant Account Saved.','flash_good');
            $this->redirect( $this->referer());
          } else {
            $this->Session->setFlash('Error Saving Merchant Account.','flash_bad');
            $this->redirect( $this->referer());
          }
      }    
    }

     /* 
      *  Deactivate a tenant 
      */
     function deactivatetenant($tenant_id = null, $unit_id = null)
     {
        $this->loadModel('User');

        $this->User->id = $tenant_id;
        $usrdata['User']['is_activated'] = 0;
        $usrdata['User']['activation_key'] = '';
        $usrdata['User']['invitebyemail'] = 0;
        $usrdata['User']['previoustenant'] = 1;
        $usrdata['User']['unit_id'] = 0;
        $usrdata['User']['property_id'] = 0;
        if ($this->User->save($usrdata, true, array('is_activated', 'activation_key','invitebyemail','previoustenant','property_id','unit_id')))
        {
           $this->User->updateUnitOccupied($unit_id);
           $usrmsg = 'User Deactivated.';
        }
        else
        {
           $usrmsg = 'User Deactivatioin failed.';
        }

        $this->redirect(array('controller' => 'Users', 'action' => 'logout'));
     }

     /* 
      *  Deactivate a property 
      *   If last or only property in system for property manager, deactivate PM as well
      */
     function deactivateproperty($property_id = null, $manager_id = null)
     {
        $this->adminCheck();
        $this->loadModel('Property');

        $managedProperties = $this->Property->find('count',array('conditions'=>array('manager_id'=>$manager_id)));

        $usrmsg = "";
        $this->Property->id = $property_id;
        $propdata['Property']['active'] = 0;
        if($this->Property->save($propdata))
        {
            if ($managedProperties == 1)
            {
               // Only property, so let's delete user record
               $this->loadModel('User');
               $this->User->id = $manager_id;
               $usrdata['User']['is_activated'] = 0;
               $usrdata['User']['activation_key'] = '';
               if ($this->User->save($usrdata, true, array('is_activated', 'activation_key')))
               {
                  $usrmsg = 'User Deactivated as well.';
               }
               else
               {
                  $usrmsg = 'User Deactivatioin failed.';
               }
            }
            $this->Session->setFlash("Property Deleted Successfully.  $usrmsg",'flash_good');
        }
        else
        {
            $this->Session->setFlash('Property NOT Deleted.','flash_bad');
        }
        $this->redirect(array('controller' => 'Admin', 'action' => 'properties', $manager_id));
     }
    
    function deleteproperty($propid=""){
     //   $this->layout = "ajax";
        $this->adminCheck();
        $this->loadModel('Property');

        /*
         *  Prior to deleting, check to see if property manager has other properties in the system.  If not, we will
         *   also delete the property manager (user) record
         *  Using 'subQuery' to essentially say "select * from properties where manager_id IN (select manager_id from 
         *   properties where property.id = 'arg id')"
         */

        $this->Property->contain();
        $conditionsSubQuery['Property2.id'] = "$propid";
        $db = $this->Property->getDataSource();
        $subQuery = $db->buildStatement(
             array('fields' => array('Property2.manager_id'),
                   'table'  => $db->fullTableName($this->Property),
                   'alias'  => 'Property2',
                   'conditions' => $conditionsSubQuery
                  ),
                  $this->Property
             );
        $subQuery = ' Property.manager_id IN (' . $subQuery . ') ';
        $subQueryExpression = $db->expression($subQuery);
        $conditions[] = $subQueryExpression;

        // Dont use 'count' method in find as we need manager_id value possibly, so get data and count afterwards
        $propsel = $this->Property->find('all', compact('conditions'));
        $managedProperties = count($propsel);

        $usrmsg = "";
        $this->Property->contain();
        $delrsl = $this->Property->delete($propid);
        if ($delrsl)
        {
            if ($managedProperties == 1)
            {
               /* Only property, so let's delete user record */
               $this->loadModel('User');
               $deluser = $this->User->delete($propsel['0']['Property']['manager_id']);
               if ($deluser)
               {
                  $usrmsg = 'User Deleted as well.';
               }
            }
            $this->Session->setFlash("Property Deleted Successfully.  $usrmsg",'flash_good');
        }
        else
        {
            $this->Session->setFlash('Property NOT Deleted.','flash_bad');
        }
        $this->redirect(array('controller' => 'Admin', 'action' => 'activateproperty'));
    }

    function activateproperty(){
        $this->adminCheck();
        $this->loadModel('Property');
        $property = $this->Property->find('list',array('conditions'=>array('Property.active'=>0)));
        $this->set(compact('property'));
        if(!empty($this->request->data)){
          $data = $this->request->data;
          $this->Property->id = $data['Property']['id'];
          $data['Property']['pp_pass'] = Security::rijndael($data['Property']['pp_pass'], Configure::read('Security.salt2'),'encrypt');
          $data['Property']['active'] = 1;
          //active property only after success payment.
          if($this->Property->save($data)){
              
              //Wave One Time Fee?
              if($data['Property']['no_one_time_fee']){
                 $this->Session->setFlash('Property Activated Successfully. Waved signup fee.','flash_good');
                  $this->redirect(array('action'=>'index'));
              }
              //Charge One Time Setup Fee 
              $this->loadModel('Payment');
              
              //Get Property Data
              $property_data = $this->Property->findById($this->Property->id);
              
              //Determine Cost base on number of units. If actual unit_count in system is greater
              //than num_units provided by user, charge actual number
              if($property_data['Property']['num_units'] > $property_data['Property']['unit_count']){
                $amount = intval($this->get_monthly_fee($property_data['Property']['num_units'])) * 2;
              } else {
                $amount = intval($this->get_monthly_fee($property_data['Property']['unit_count'])) * 2;
              }
              
              //Process One Time Fee Payment to RentSquare
              $result = $this->Payment->processPayment($amount,$property_data['Property']['vault_id'],RENTSQUARE_MERCH_USER,RENTSQUARE_MERCH_PASS);
  		      
              parse_str($result);
              
        			if(isset($response) && $response == 1) {
        			    $this->Property->id = $property_data['Property']['id'];
        			    $this->Property->saveField('active',1);

                                    // 2014-09-15 - Wolff - Set fee_due_day
                                    $this->Property->saveField('fee_due_day', date('j'));

        			    $this->__sendPropertyActivation($property_data);
        			    
        			    $signup_fee = array();
      			      $signup_fee['Payment']['ppresponse'] = $response;
      			      $signup_fee['Payment']['ppresponsetext'] = $responsetext;
                  $signup_fee['Payment']['ppauthcode'] = $authcode;
                  $signup_fee['Payment']['pptransactionid']	= $transactionid;
                  $signup_fee['Payment']['ppresponse_code'] = $response_code;
            			$signup_fee['Payment']['status'] = 'Complete';
            			$signup_fee['Payment']['notes'] = 'Signup Fee';
            			$signup_fee['Payment']['user_id'] = $property_data['Property']['manager_id'];
            			$signup_fee['Payment']['billing_id'] = 0;
            			$signup_fee['Payment']['unit_id'] = 0;
            			$signup_fee['Payment']['amount'] = $amount;
            			$signup_fee['Payment']['is_fee'] = 0;
            			$signup_fee['Payment']['amt_processed'] = floatval($amount);
            			$signup_fee['Payment']['total_bill'] = floatval($amount);
            			
                  //Add to Payments Table
                  $this->Payment->create();
                  $this->Payment->save($signup_fee);
    
                  $this->Session->setFlash('Property Activated Successfully and One Time Fee Processed','flash_good');
                  $this->redirect(array('action'=>'index'));
              }else{
                  $this->loadModel('FailedPayment');
                  $failed=$property_data;
                  $failed['FailedPayment']['billing_id'] = '0';
                  $failed['FailedPayment']['unit_id'] = '0';
                  $failed['FailedPayment']['user_id'] = $property_data['Property']['manager_id'];
                  $failed['FailedPayment']['amount'] = $amount;
                  $failed['FailedPayment']['ppresponse'] = $response;
                  $failed['FailedPayment']['ppresponsetext'] = $responsetext;
                  $failed['FailedPayment']['ppauthcode'] = $authcode;
                  $failed['FailedPayment']['pptransactionid']	= $transactionid;
                  $failed['FailedPayment']['ppresponse_code'] = $response_code;
                  $failed['FailedPayment']['notes'] = "Failed on One Time Fee Charge. Property id ".$property_data['Property']['id'];
                  if ($this->FailedPayment->save($failed)) {
              				$this->Session->setFlash(__('Payment has failed with error '. $responsetext),'flash_bad');
              			} else {
              				$this->Session->setFlash(__('Payment has failed with error '. $responsetext),'flash_bad');
              			}
                }
          }else { 
              $this->Session->setFlash('Error activating property.','flash_bad');
          }
        }
    
        }
        
        function get_monthly_fee($num_units){
            //0 - 25 units - $25
            if($num_units <= 25){
              return MONTHLY_25_OR_LESS;
            }
            //26 - 50 units - $50
            if($num_units <= 50){
              return MONTHLY_26_TO_50;
            }
            //51 - 100 units - $75
            elseif($num_units <= 100){
              return MONTHLY_51_TO_100;
            }
            //101 - 200 units - $125
            elseif($num_units <= 200){
              return MONTHLY_101_TO_200;
            }
            //201 - 300 units - $175
            elseif($num_units <= 300){
              return MONTHLY_201_TO_300;
            }
            //301 - 400 units - $225
            elseif($num_units <= 400){
              return MONTHLY_301_TO_400;
            }
            //400++ units - $275
            elseif($num_units > 400){
              return MONTHLY_OVER_400;
            }
            else{
              return 25;
            }
        }
      private function __sendPropertyActivation($prop_data) 
    	{
    			$from = Configure::read('RentSquare.supportemail');
    	  
          try{
            $email = new CakeEmail();
      			$email->domain('rentsquaredev.com');
      			$email->sender('noreply@rentsquaredev.com','RentSquare Support');
      			$email->template('approveproperty', 'generic')
      				->emailFormat('html')
      				->from(array($from => 'RentSquare Support'))
      				->to($prop_data['Manager']['email'])
      				->subject('Property Approved')
              ->viewVars(array(
    					  'prop_data' => $prop_data
    					))
      				->send();
          } catch(Exception $e){
            return false;
          }
      			    
      			return true;
      			
      }

    
}
