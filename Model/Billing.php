<?php
App::uses('AppModel', 'Model');
App::uses('Payment', 'Model');
App::uses('BillingFee','Model');
App::uses('CakeEmail', 'Network/Email');
Configure::write('RentSquare.supportemail', 'support@rentsquare.co');

/**
 * Billing Model
 *
 * @property Unit $Unit
 */
class Billing extends AppModel {

public $useTable = 'billing';

public $actsAs = array('Containable');

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'id';

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	
public $belongsTo = array(
		'Unit' => array(
			'className' => 'Unit',
			'foreignKey' => 'unit_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);
	
	public $hasMany = array('Payment','BillingFee','FailedPayment');
	
	public function updatebillingstatus($bc_id = null){
  	if($bc_id !=null){ 
  	  $total_payments = $this->Payment->find('all',array('fields'=>'sum(Payment.amount) as total_payment', 'conditions' => array('Payment.billing_id' => intval($bc_id))));
  	  $billing_cycle = $this->find('first',array('conditions'=>array('Billing.id'=>intval($bc_id))));
  	  $this->id = $billing_cycle['Billing']['id'];
      $total_paid =  $total_payments[0][0]['total_payment'];
      if($total_paid > $billing_cycle['Billing']['rent_due']){
        //Over Paid
        //Mark as Paid
        $this->saveField('status','paid');
        //Add Credit [Add Code]
      } else if($total_paid == $billing_cycle['Billing']['rent_due']){
        //Paid In full
        //Mark as Paid
        $this->saveField('status','paid');
      }
      $this->saveField('balance',floatval(floatval($billing_cycle['Billing']['rent_due']) - floatval($total_paid)));
    }
    return true;  
	}
	
    public function addLateFee($billing_id,$amount,$is_auto = null){
        //Get rent_due based on billing id
        $rent_due = $this->find('first',array('conditions'=>array('Billing.id'=>$billing_id)));
    
        //Make sure auto rent hasn't been already added (only if script runs twice in a day)
        if($is_auto && !$rent_due['Billing']['auto_late_fee'] || $is_auto == null):
            //Add Late Fee to rent due
           $new_rent = floatval($rent_due['Billing']['rent_due']) + floatval($amount);   
           $new_balance = floatval($rent_due['Billing']['balance']) + floatval($amount);  
           //Save rent due
           $this->id=$billing_id;
           if ($this->saveField('rent_due',$new_rent) && $this->saveField('balance',$new_balance)) {
               $this->saveField('auto_late_fee',1);
               //Add fee to Billing Fee Table
               $billing_fee = array();
               $billing_fee["BillingFee"] = array(
                 'billing_id' => intval($billing_id),
                 'name' => 'Manual Late Fee',
                 'amount' => floatval($amount)
               );
               if($is_auto){
                 $billing_fee["BillingFee"]["name"] = 'Auto Late Fee';
               }
               $this->BillingFee->create();
               if($this->BillingFee->save($billing_fee)){          
                 $User = ClassRegistry::init('User');
                 $User->contain();
                 $tenants = $User->find('all', array('conditions' => array('unit_id' => $rent_due['Billing']['unit_id'])));
                 //$log = $User->getDataSource()->getLog(false, false);
                 //debug($log);
                 $cnt = 0;
                 foreach($tenants as $tenant)
                 {
                    if($tenant['User']['email_latefee_assessed'])
                    {
                       $cnt++;
                       if ($cnt == 1)
                       {
                          // Get property name
                          $Property = ClassRegistry::init('Property');
                          $Property->contain();
                          $bldg = $Property->findById($rent_due['Billing']['property_id']);
                       }
                       $data=array();
                       $data['unit_num'] = $rent_due['Unit']['number'];
                       $data['rent_due'] = $rent_due['Billing']['rent_due'];
                       $data['first_name'] = $tenant['User']['first_name'];
                       $data['billing_end'] = $rent_due['Billing']['billing_end'];
                       $data['billing_start'] = $rent_due['Billing']['billing_start'];
                       $data['rent_period'] = $rent_due['Billing']['rent_period'];
                       $data['property_name'] = $bldg['Property']['name'];
                       $data['current_balance'] = $new_balance;
                       $data['late_fee'] = $amount;
                   
                       $this->__sendLateFeeAssessMail($tenant['User']['email'],$data);
                    }
                 }
                 return true;
               }else{
                 return false;
               }
           } else {
              return false;
       }
       endif; //end $is_auto && !$rent_due['Billing']['auto_late_fee']
    return true;
    }
	
	/* Returns Total Balance for current user */
	public function getBalance($unit_id=null){
  	$billings = $this->find('all',array(
          'conditions'=>array(
            'Billing.unit_id'=> $unit_id,
            'status !='=>'paid'
          ),
          'contain' => array(
            'Payment'=>array( 'conditions'=>array('is_fee'=>0),
                'User'=>array('fields'=>'last_name,first_name')),
            'BillingFee'
          ),
          'order'=>array('Billing.id')));
    
    $total_balance = 0;
    foreach($billings as $billingcycle):
          $money_due = floatval($billingcycle['Billing']['rent_due']);
          foreach($billingcycle['Payment'] as $payment):
            $money_due = floatval($money_due) - floatval($payment['amount']);
          endforeach;
          $total_balance = floatval($total_balance) + floatval($money_due);  
    endforeach;
    if($total_balance >= 0)
      return $total_balance;
    else
      return 0;
	}


    private function __sendLateFeeAssessMail($email_address,$data)
    {
       try{
          $email = new CakeEmail(array('log' => false));
          $email->domain('rentsquaredev.com');
          $email->sender('noreply@rentsquaredev.com','RentSquare Support');
   
          $from = Configure::read('RentSquare.supportemail');
          $result = $email->template('latefee','generic')
                                    ->emailFormat('html')
                                    ->from(array($from => 'RentSquare Support'))
                                    ->to($email_address)
                                    ->subject("Late Fee - {$data['property_name']} - {$data['unit_num']}")
                                    ->viewVars(array(
                                            'unit_num'   => $data['unit_num'],
                                            'rent_due' => $data['rent_due'] ,
                                            'first_name' => $data['first_name'],
                                            'billing_start'=>$data['billing_end'],
                                            'billing_end'=>$data['rent_period'],
                                            'property_name'=>$data['property_name'],
                                            'current_balance'=>$data['current_balance'],
                                            'late_fee'=>$data['late_fee']
                                    ))
                                    ->send();
       } catch(Exception $e){
            return false;
       }
       return true;
    }
}


?>
