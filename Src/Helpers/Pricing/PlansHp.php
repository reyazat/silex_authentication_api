<?php
namespace Helper\Pricing;

class PlansHp{
	
	protected $app;
	
	public function __construct($app){		
		$this->app = $app;	
 
	}
	
	
	public function getPlans($serviceId , $request =[]){
		$payLoad = $where = [];
		$select = ['*']; // serviceId,softwareName,plan,identify,prices,trialStatus,trialDays,annualyDiscount,limitation,coreFeatures,newFeatures,automate,integrations,addons,mobileapp,paymentStatus
			
		if($this->app['helper']('Utility')->notEmpty($serviceId)){
			
			$fields = $request->get('fields');
			$fields = $this->app['helper']('Utility')->secureInput($fields);
			
			$planId = $request->get('Identify');
			$planId = $this->app['helper']('Utility')->secureInput($planId);

			$paymentStatus = $request->get('Status');
			$paymentStatus = $this->app['helper']('Utility')->secureInput($paymentStatus);

			if($this->app['helper']('Utility')->notEmpty($fields)){
				
				$old= array( 'serviceId','softwareName','plan','identify','prices','trialStatus','trialDays','annualyDiscount','limitation','coreFeatures','newFeatures','automate','integrations','addons','mobileapp','paymentStatus');
				
				$new = array('service_id','service_name','name','identify','prices','trial_status','trial_days','annualy_discount','limitation','corefeatures','newfeatures','automate','integrations','addons','mobileapp','payment_status');

				$fields = str_replace($old, $new, $fields);
				
				$select = explode(',',$fields);
							
			}
			$where[] = ['service_id','=',$serviceId];
			
			if($this->app['helper']('Utility')->notEmpty($planId)){
				$where[] = ['identify','=',$planId];
			}
			if($this->app['helper']('Utility')->notEmpty($paymentStatus)){
				$where[] = ['payment_status','=',$paymentStatus];
			}
			
			$rows = $this->app['load']('Models_Plans')->returnRows($where , $select);
			
			if($this->app['helper']('Utility')->notEmpty($rows)){
				$result= [];
				foreach($rows as $row){
				
					$detiles = [];

					if(isset($row['service_id']))$detiles['serviceId'] = $row['service_id'];
					if(isset($row['service_name']))$detiles['softwareName'] = $row['service_name'];

					if(isset($row['name']))$detiles['plan'] = $row['name'];

					if(isset($row['identify']))$detiles['identify'] = $row['identify'];
					
					if(isset($row['prices']) && $this->app['helper']('Utility')->notEmpty($row['prices'])){
						$detiles['prices'] = json_decode($row['prices']);
					}
					if(isset($row['trial_status']))$detiles['trialStatus'] = $row['trial_status'];

					if(isset($row['trial_days']))$detiles['trialDays'] = $row['trial_days'];

					if(isset($row['annualy_discount']))$detiles['annualyDiscount'] = $row['annualy_discount'];
					
					if(isset($row['limitation']) && $this->app['helper']('Utility')->notEmpty($row['limitation'])){
						$detiles['limitation'] =  json_decode($row['limitation']);
					}
					
					if(isset($row['corefeatures']) && $this->app['helper']('Utility')->notEmpty($row['corefeatures'])){
						$detiles['coreFeatures'] = json_decode($row['corefeatures']);
					}
					
					if(isset($row['newfeatures']) && $this->app['helper']('Utility')->notEmpty($row['newfeatures'])){
						$detiles['newFeatures'] = json_decode($row['newfeatures']);
					}
					
					if(isset($row['automate']) && $this->app['helper']('Utility')->notEmpty($row['automate'])){
						$detiles['automate'] = json_decode($row['automate']);
					}
					
					if(isset($row['integrations']) && $this->app['helper']('Utility')->notEmpty($row['integrations'])){
						$detiles['integrations'] = json_decode($row['integrations']);
					}

					if(isset($row['addons']) && $this->app['helper']('Utility')->notEmpty($row['addons'])){
						$detiles['addons'] = json_decode($row['addons']);
					}

					if(isset($row['mobileapp']) && $this->app['helper']('Utility')->notEmpty($row['mobileapp'])){
						$detiles['mobileapp'] = json_decode($row['mobileapp']);
					}
					if(isset($row['payment_status']))$detiles['paymentStatus'] = $row['payment_status'];

					$result[] = $detiles;
				}

				$payLoad = ['status'=>'success','result'=>$result];
				
			}else{
				$payLoad = ['status'=>'success','result'=>$this->app['translator']->trans('NotFound')];
			}
		}else{
			$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'RequiredsEmpty',array('%name%' => 'serviceId'))];
		}
		return $payLoad;

			
		}
	
	
	public function deletePlans($request = [] ,$serviceId ,$plansId){
		$payLoad = [];
		
		$where[] = ['identify','=',$plansId];
		$where[] = ['service_id','=',$serviceId];
		$exist = $this->app['load']('Models_Plans')->existOneRow($where);

		if($this->app['helper']('Utility')->notEmpty($exist)){
			$where=[];
			$where[] = ['identify','=',$exist['identify']];
			$where[] = ['service_id','=',$serviceId];
			$this->app['load']('Models_Plans')->deleteRows($where);
			$payLoad = ['status'=>'success','message'=>$this->app['translator']->trans('200')];
		}else{
			$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans('NotFound')];
		}
		
		return $payLoad;
				
	}
	
	
	public function savePlans($request = [] ,$serviceId){
		$payLoad = [];
		
		$postParameter = $this->app['helper']('RequestParameter')->postParameter($request);
		
		$payLoad = $this->validateParams($postParameter);
		
		if($payLoad['status']==='error'){
			return $payLoad;
		}else{
			
			$where[] = ['identify','=',$payLoad['result']['identify']];
			
			$exist = $this->app['load']('Models_Plans')->existOneRow($where);
			if($this->app['helper']('Utility')->notEmpty($exist)){
				$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'AlreadyExists',array('%name%' => 'Identify'))];
			}else{
				$payLoad = $this->app['load']('Models_Plans')->insert($payLoad['result']);
				if($payLoad['status']==='success'){
					$msg = $this->app['translator']->trans('200');
					$payLoad['message'] = $msg ;
				}
			}
			
		}
		
		return $payLoad;
	
	}
	
	
	public function updatePlans($request = [] ,$serviceId , $plansId){
		$payLoad = [];
		$postParameter = $this->app['helper']('RequestParameter')->postParameter($request);
		$where[] = ['identify','=',$plansId];
			
		$exist = $this->app['load']('Models_Plans')->existOneRow($where);
		if($this->app['helper']('Utility')->notEmpty($exist)){
			
			$payLoad = $this->validateUpdateParams($postParameter);
	
			if($payLoad['status']==='error'){
				return $payLoad;
			}else{
				$where = [];
				$where[] = ['identify','=',$plansId];
				$payLoad = $this->app['load']('Models_Plans')->updateRows($payLoad['result'] , $where);
				if($payLoad['status']==='success'){
					$msg = $this->app['translator']->trans('200');
					$payLoad['message'] = $msg ;
				}
			}
			
		}else{
			$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
			'NotFound')];
		}
		
		return $payLoad;
	}
	
	public function validateParams($params){
		$payLoad = [];
		$response  = [];
		if($this->app['helper']('ArrayFunc')->isArray($params) && $this->app['helper']('Utility')->notEmpty($params)){
			/*****************************/
			if(isset($params['plan']) && $this->app['helper']('Utility')->notEmpty($params['plan'])){
				
				$plan = $this->app['helper']('Utility')->secureInput($params['plan']);
				$response['name'] = $plan;
				
			}else{
				$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'RequiredsEmpty',array('%name%' => ' plan parameter'))];
			}
			/*****************************/
			if(isset($params['identify']) && $this->app['helper']('Utility')->notEmpty($params['identify'])){
				
				$identify = $this->app['helper']('Utility')->secureInput($params['identify']);
				$response['identify'] = $identify;
				
			}else{
				$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'RequiredsEmpty',array('%name%' => ' identify parameter'))];
			}
			/*****************************/
			if(isset($params['serviceId']) && $this->app['helper']('Utility')->notEmpty($params['serviceId'])){
				
				$serviceId = $this->app['helper']('Utility')->secureInput($params['serviceId']);
				$response['service_id'] = $serviceId;
				$where=[];
				$where[] = ['service_id','=',$serviceId];

				$exist = $this->app['load']('Models_SoftwareDetails')->existOneRow($where);
				if(!$this->app['helper']('Utility')->notEmpty($exist)){
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'RequiredsEmpty',array('%name%' => ' serviceId parameter'))];
				}
				
				$response['service_name'] = $exist['name'];
				
			}else{
				$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'RequiredsEmpty',array('%name%' => ' serviceId parameter'))];
			}
			/*****************************/
			if(isset($params['prices'])){
				$params['prices'] = trim($params['prices']);
				
				if($this->app['helper']('Utility')->notEmpty($params['prices']) && $this->app['helper']('Utility')->isJSON($params['prices'])){

					$response['prices'] = $params['prices'];
 
				}elseif(strtolower($params['prices'])=='null'){
					$response['prices'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' prices parameter'))];
				}
			}
			/*****************************/
			if(isset($params['trialStatus']) && $this->app['helper']('Utility')->notEmpty($params['trialStatus'])){
				
				$trialStatus = $this->app['helper']('Utility')->secureInput($params['trialStatus']);
				$trialStatus = $this->app['helper']('Utility')->trm($trialStatus);
				if($trialStatus === 'Enable' || $trialStatus === 'Disable'){
					$response['trial_status'] = ucfirst($trialStatus);
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'RequiredsEmpty',array('%name%' => ' trialStatus parameter'))];
				}
				
				
			}else{
				$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'RequiredsEmpty',array('%name%' => ' trialStatus parameter'))];
			}
			/*****************************/
			if(isset($params['trialDays'])){
				$params['trialDays'] = trim($params['trialDays']);
				
				if($this->app['helper']('Utility')->notEmpty($params['trialDays']) && $this->app['helper']('Utility')->isNumber($params['trialDays'])){

					$response['trial_days'] = $params['trialDays'];
 
				}elseif(strtolower($params['trialDays'])=='null'){
					$response['trial_days'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' trialDays parameter'))];
				}
			}
			/*****************************/
			if(isset($params['annualyDiscount'])){
				$params['annualyDiscount'] = trim($params['annualyDiscount']);
				
				if($this->app['helper']('Utility')->notEmpty($params['annualyDiscount']) && strtolower($params['annualyDiscount'])!=='null'){

					$response['annualy_discount'] = $params['annualyDiscount'];
 
				}elseif(strtolower($params['annualyDiscount'])=='null'){
					$response['annualy_discount'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' annualyDiscount parameter'))];
				}
			}
			/*****************************/
			if(isset($params['limitation'])){
				$params['limitation'] = trim($params['limitation']);
				
				if($this->app['helper']('Utility')->notEmpty($params['limitation']) && $this->app['helper']('Utility')->isJSON($params['limitation'])){

					$response['limitation'] = $params['limitation'];
 
				}elseif(strtolower($params['limitation'])=='null'){
					$response['limitation'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' limitation parameter'))];
				}
			}
			/*****************************/
			if(isset($params['coreFeatures'])){
				$params['coreFeatures'] = trim($params['coreFeatures']);
				
				if($this->app['helper']('Utility')->notEmpty($params['coreFeatures']) && $this->app['helper']('Utility')->isJSON($params['coreFeatures'])){

					$response['corefeatures'] = $params['coreFeatures'];
 
				}elseif(strtolower($params['coreFeatures'])=='null'){
					$response['corefeatures'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' coreFeatures parameter'))];
				}
			}
			/*****************************/
			if(isset($params['newFeatures'])){
				$params['newFeatures'] = trim($params['newFeatures']);
				
				if($this->app['helper']('Utility')->notEmpty($params['newFeatures']) && $this->app['helper']('Utility')->isJSON($params['newFeatures'])){

					$response['newfeatures'] = $params['newFeatures'];
 
				}elseif(strtolower($params['newFeatures'])=='null'){
					$response['newfeatures'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' newFeatures parameter'))];
				}
			}
			/*****************************/
			if(isset($params['automate'])){
				$params['automate'] = trim($params['automate']);
				
				if($this->app['helper']('Utility')->notEmpty($params['automate']) && $this->app['helper']('Utility')->isJSON($params['automate'])){

					$response['automate'] = $params['automate'];
 
				}elseif(strtolower($params['automate'])=='null'){
					$response['automate'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' automate parameter'))];
				}
			}
			/*****************************/
			if(isset($params['integrations'])){
				$params['integrations'] = trim($params['integrations']);
				
				if($this->app['helper']('Utility')->notEmpty($params['integrations']) && $this->app['helper']('Utility')->isJSON($params['integrations'])){

					$response['integrations'] = $params['integrations'];
 
				}elseif(strtolower($params['integrations'])=='null'){
					$response['integrations'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' integrations parameter'))];
				}
			}
			/*****************************/
			if(isset($params['addons'])){
				$params['addons'] = trim($params['addons']);
				
				if($this->app['helper']('Utility')->notEmpty($params['addons']) && $this->app['helper']('Utility')->isJSON($params['addons'])){

					$response['addons'] = $params['addons'];
 
				}elseif(strtolower($params['addons'])=='null'){
					$response['addons'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' addons parameter'))];
				}
			}
			/*****************************/
			if(isset($params['mobileApp'])){
				$params['mobileApp'] = trim($params['mobileApp']);
				
				if($this->app['helper']('Utility')->notEmpty($params['mobileApp']) && $this->app['helper']('Utility')->isJSON($params['mobileApp'])){

					$response['mobileapp'] = $params['mobileApp'];
 
				}elseif(strtolower($params['mobileApp'])=='null'){
					$response['mobileapp'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' mobileApp parameter'))];
				}
			}
			/*****************************/
			if(isset($params['paymentStatus']) && $this->app['helper']('Utility')->notEmpty($params['paymentStatus'])){
				
				$paymentStatus = $this->app['helper']('Utility')->secureInput($params['paymentStatus']);
				$response['payment_status'] = $paymentStatus;
				
			}else{
				$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'RequiredsEmpty',array('%name%' => ' paymentStatus parameter'))];
			}
			/*****************************/
			
			if(!isset($payLoad['status']) || $payLoad['status']!=='error'){
				
				$payLoad = ['status'=>'success','result'=>$response];
			}
		}else{
			$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'RequiredsEmpty',array('%name%' => 'all of them'))];
		}
		
		return $payLoad;
	}
	
	
	
	public function validateUpdateParams($params){
		$payLoad = [];
		$response  = [];
		if($this->app['helper']('ArrayFunc')->isArray($params) && $this->app['helper']('Utility')->notEmpty($params)){
			/*****************************/
			if(isset($params['plan']) && $this->app['helper']('Utility')->notEmpty($params['plan'])){
				
				$plan = $this->app['helper']('Utility')->secureInput($params['plan']);
				$response['name'] = $plan;
				
			}
			
			/*****************************/
			
			if(isset($params['serviceId']) && $this->app['helper']('Utility')->notEmpty($params['serviceId'])){
				
				$serviceId = $this->app['helper']('Utility')->secureInput($params['serviceId']);
				$response['service_id'] = $serviceId;
				$where=[];
				$where[] = ['service_id','=',$serviceId];

				$exist = $this->app['load']('Models_SoftwareDetails')->existOneRow($where);
				if(!$this->app['helper']('Utility')->notEmpty($exist)){
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'RequiredsEmpty',array('%name%' => ' serviceId parameter'))];
				}
				
				$response['service_name'] = $exist['name'];
				
			}
			/*****************************/
			if(isset($params['prices'])){
				$params['prices'] = trim($params['prices']);
				
				if($this->app['helper']('Utility')->notEmpty($params['prices']) && $this->app['helper']('Utility')->isJSON($params['prices'])){

					$response['prices'] = $params['prices'];
 
				}elseif(strtolower($params['prices'])=='null'){
					$response['prices'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' prices parameter'))];
				}
			}
			/*****************************/
			if(isset($params['trialStatus']) && $this->app['helper']('Utility')->notEmpty($params['trialStatus'])){
				
				$trialStatus = $this->app['helper']('Utility')->secureInput($params['trialStatus']);
				$trialStatus = $this->app['helper']('Utility')->trm($trialStatus);
				if($trialStatus === 'Enable' || $trialStatus === 'Disable'){
					$response['trial_status'] = ucfirst($trialStatus);
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'RequiredsEmpty',array('%name%' => ' trialStatus parameter'))];
				}
				
				
			}
			/*****************************/
			if(isset($params['trialDays'])){
				$params['trialDays'] = trim($params['trialDays']);
				
				if($this->app['helper']('Utility')->notEmpty($params['trialDays']) && $this->app['helper']('Utility')->isNumber($params['trialDays'])){

					$response['trial_days'] = $params['trialDays'];
 
				}elseif(strtolower($params['trialDays'])=='null'){
					$response['trial_days'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' trialDays parameter'))];
				}
			}
			/*****************************/
			if(isset($params['annualyDiscount'])){
				$params['annualyDiscount'] = trim($params['annualyDiscount']);
				
				if($this->app['helper']('Utility')->notEmpty($params['annualyDiscount']) && strtolower($params['annualyDiscount'])!=='null'){

					$response['annualy_discount'] = $params['annualyDiscount'];
 
				}elseif(strtolower($params['annualyDiscount'])=='null'){
					$response['annualy_discount'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' annualyDiscount parameter'))];
				}
			}
			/*****************************/
			if(isset($params['limitation'])){
				$params['limitation'] = trim($params['limitation']);
				
				if($this->app['helper']('Utility')->notEmpty($params['limitation']) && $this->app['helper']('Utility')->isJSON($params['limitation'])){

					$response['limitation'] = $params['limitation'];
 
				}elseif(strtolower($params['limitation'])=='null'){
					$response['limitation'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' limitation parameter'))];
				}
			}
			/*****************************/
			if(isset($params['coreFeatures'])){
				$params['coreFeatures'] = trim($params['coreFeatures']);
				
				if($this->app['helper']('Utility')->notEmpty($params['coreFeatures']) && $this->app['helper']('Utility')->isJSON($params['coreFeatures'])){

					$response['corefeatures'] = $params['coreFeatures'];
 
				}elseif(strtolower($params['coreFeatures'])=='null'){
					$response['corefeatures'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' coreFeatures parameter'))];
				}
			}
			/*****************************/
			if(isset($params['newFeatures'])){
				$params['newFeatures'] = trim($params['newFeatures']);
				
				if($this->app['helper']('Utility')->notEmpty($params['newFeatures']) && $this->app['helper']('Utility')->isJSON($params['newFeatures'])){

					$response['newfeatures'] = $params['newFeatures'];
 
				}elseif(strtolower($params['newFeatures'])=='null'){
					$response['newfeatures'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' newFeatures parameter'))];
				}
			}
			/*****************************/
			if(isset($params['automate'])){
				$params['automate'] = trim($params['automate']);
				
				if($this->app['helper']('Utility')->notEmpty($params['automate']) && $this->app['helper']('Utility')->isJSON($params['automate'])){

					$response['automate'] = $params['automate'];
 
				}elseif(strtolower($params['automate'])=='null'){
					$response['automate'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' automate parameter'))];
				}
			}
			/*****************************/
			if(isset($params['integrations'])){
				$params['integrations'] = trim($params['integrations']);
				
				if($this->app['helper']('Utility')->notEmpty($params['integrations']) && $this->app['helper']('Utility')->isJSON($params['integrations'])){

					$response['integrations'] = $params['integrations'];
 
				}elseif(strtolower($params['integrations'])=='null'){
					$response['integrations'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' integrations parameter'))];
				}
			}
			/*****************************/
			if(isset($params['addons'])){
				$params['addons'] = trim($params['addons']);
				
				if($this->app['helper']('Utility')->notEmpty($params['addons']) && $this->app['helper']('Utility')->isJSON($params['addons'])){

					$response['addons'] = $params['addons'];
 
				}elseif(strtolower($params['addons'])=='null'){
					$response['addons'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' addons parameter'))];
				}
			}
			/*****************************/
			if(isset($params['mobileApp'])){
				$params['mobileApp'] = trim($params['mobileApp']);
				
				if($this->app['helper']('Utility')->notEmpty($params['mobileApp']) && $this->app['helper']('Utility')->isJSON($params['mobileApp'])){

					$response['mobileapp'] = $params['mobileApp'];
 
				}elseif(strtolower($params['mobileApp'])=='null'){
					$response['mobileapp'] = NULL;
				}else{
					$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
					'RequiredsEmpty',array('%name%' => ' mobileApp parameter'))];
				}
			}
			/*****************************/
			if(isset($params['paymentStatus']) && $this->app['helper']('Utility')->notEmpty($params['paymentStatus'])){
				
				$paymentStatus = $this->app['helper']('Utility')->secureInput($params['paymentStatus']);
				$response['payment_status'] = $paymentStatus;
				
			}
			/*****************************/
			
			if(!isset($payLoad['status']) || $payLoad['status']!=='error'){
				
				$payLoad = ['status'=>'success','result'=>$response];
			}
		}else{
			$payLoad = ['status'=>'error','message'=>$this->app['translator']->trans(
				'RequiredsEmpty',array('%name%' => 'all of them'))];
		}
		
		return $payLoad;
	}
		
}
	