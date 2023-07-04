<?php 
namespace Helper;
use Ratchet\ConnectionInterface;
use Ratchet\Wamp\WampServerInterface;

class WebSocketApp implements WampServerInterface{
	
	protected $app;
	public function __construct($app){
		
		$this->app = $app; 
		
	}
	
	/**
     * A lookup of all the topics clients have subscribed to
     */
    protected $subscribedTopics = array();
	
    public function onSubscribe(ConnectionInterface $conn, $topic) {
		print_r($topic->getId());
		$this->subscribedTopics[$topic->getId()] = $topic;
    }
	
	/**
     * @param string JSON'ified string we'll receive from ZeroMQ
     */
    public function onBlogEntry($entry) {
		
        $entryData = json_decode($entry, true);

        // If the lookup topic object isn't set there is no one to publish to
        if (!array_key_exists($entryData['identify'], $this->subscribedTopics)) {
            return;
        }

		$topic = $this->subscribedTopics[$entryData['identify']];


        // re-send the data to all the clients subscribed to that category
        $topic->broadcast($entryData);
		
    }
	
    public function onUnSubscribe(ConnectionInterface $conn, $topic) {}
	
    public function onOpen(ConnectionInterface $conn) {
		echo'conected  ';
    }
	
    public function onClose(ConnectionInterface $conn) {
		echo'closed  ';
    }
	
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params) {
		
        // In this application if clients send data it's because the user hacked around in console
        $conn->callError($id, $topic, 'You are not allowed to make calls')->close();
		
    }
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible) {
		
		/*$expoIds = explode('-',$topic->getId());
		
		if(count($expoIds) == 2 && isset($event[0])){
			
			$this->app['component']('oAuth_Models_LiveUsers')->insertUser(['id_user'=>$expoIds[1],
																		   'id_company'=>$expoIds[0],
																		   'url'=>$event[0]]);
			
		}*/
		
		$expoIds = explode('-',$topic->getId());
		if(count($expoIds) == 2 && isset($event[0])){
			
			if($event[0] == 'counter'){
				
				$this->app['helper']('OutgoingRequest')->getRequest($this->app['config']['webservice']['view'].'system/counter',[],['id_user'=>$expoIds[1],'id_company'=>$expoIds[0]],false);
										
			}else{
				
				if(isset($event[1])){
					$this->app['component']('oAuth_Models_LiveUsersAcc')->insertUser(['id_user'=>$expoIds[1],
																					  'id_company'=>$expoIds[0],
																					  'company'=>$event[1],
																					  'url'=>$event[0]]);
				}else{
					$this->app['component']('oAuth_Models_LiveUsers')->insertUser(['id_user'=>$expoIds[1],
																			   'id_company'=>$expoIds[0],
																			   'url'=>$event[0]]);
				}
				
			}

		} 

        // In this application if clients send data it's because the user hacked around in console
        //$conn->close();
		
    }
    public function onError(ConnectionInterface $conn, \Exception $e) {}
}