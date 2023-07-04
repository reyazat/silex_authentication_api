<?php
namespace Helper;
use \Mailgun\Mailgun;
class SendMail
{
	protected $app;
	
	public function __construct($app) {
		$this->app = $app;
	}
	
		
	public function sendMessage($params){
		$payLoad = [];
		
		$payLoad = $this->createparams($params);
		if ($payLoad['status'] === 'Success') {
			# Instantiate the client.
			$mgClient = Mailgun::create($this->app['config']['mail']['mailgun']['API_Key'], $this->app['config']['mail']['mailgun']['API_Base_URL']);
			$params = $payLoad['data'];
			$params['o:tracking']= 'false';
			//$params['o:testmode']= (string) $this->app['config']['parameters']['debug'];

			# Make the call to the client.
			$result = $mgClient->messages()->send($this->app['config']['mail']['mailgun']['domain'], $params);
			$this->app['monolog.debug']->warning('Result of send message in mailgun ',(array) $result);
			$payLoad = (['status' => 'Success', 'message' => '', 'code'=>200, 'data' =>(array) $result ]);
		}
		
		return $payLoad;		
	}
	
	private function createparams($params = []){
		$payLoad = [];
		
		if(!isset($params['to']) || 
		(isset($params['to']) && !$this->app['helper']('Utility')->notEmpty($params['to'])) || 
		!$this->app['helper']('Utility')->isEmail($params['to'])){
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'To'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		}elseif(isset($params['cc']) && !$this->app['helper']('Utility')->isEmail($params['cc'])){
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'CC'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		}elseif(isset($params['bcc']) && !$this->app['helper']('Utility')->isEmail($params['bcc'])){
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'BCC'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		}elseif(!isset($params['subject']) || (isset($params['subject']) && !$this->app['helper']('Utility')->notEmpty($params['subject']))){
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Subject'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		}elseif(!isset($params['content']) || (isset($params['content']) && !$this->app['helper']('Utility')->notEmpty($params['content']))){
			$msg = $this->app['translator']->trans('InvalidParametrs', array('%name%' => 'Content'));
            $payLoad = ['status' => 'Error', 'message' => $msg, 'code' => 400];
		}else{
			$fileds = [];
			$fileds['html'] = $params['content'];
			$fileds['subject'] = $this->getEncodeString($params['subject']);
			
			$fileds['to'] = $this->app['helper']('Utility')->secureInput($params['to']);
			if(isset($params['to_variables']) && is_array($params['to_variables']) && $this->app['helper']('Utility')->notEmpty($params['to_variables'])){
				$fileds['recipient-variables'][$fileds['to']] = $params['to_variables'];
			}
			
			if(isset($params['cc']))$fileds['cc'] = $this->app['helper']('Utility')->secureInput($params['cc']);
			if(isset($params['cc_variables']) && is_array($params['cc_variables']) && $this->app['helper']('Utility')->notEmpty($params['cc_variables'])){
				$fileds['recipient-variables'][$fileds['cc']] = $params['cc_variables'];
			}
			if(isset($params['bcc']))$fileds['bcc'] = $this->app['helper']('Utility')->secureInput($params['bcc']);
			if(isset($params['bcc_variables']) && is_array($params['bcc_variables']) && $this->app['helper']('Utility')->notEmpty($params['bcc_variables'])){
				$fileds['recipient-variables'][$fileds['bcc']] = $params['bcc_variables'];
			}
			
			if(isset($fileds['recipient-variables']) && $this->app['helper']('Utility')->notEmpty($fileds['recipient-variables'])){
				$fileds['recipient-variables'] = $this->app['helper']('Utility')->encodeJson($fileds['recipient-variables']);
			}
			$fileds['from'] = 	$this->app['config']['mail']['mailgun']['From'];
			$fileds['h:Reply-To'] = 	$this->app['config']['mail']['mailgun']['Reply'];
			$payLoad = (['status' => 'Success', 'message' => '', 'code'=>200, 'data' =>$fileds ]);
		}
		return $payLoad;
	}
	
	
	/**
     * @access public
     * @param string
     * @return string
     */
	public function getEncodeString($str){
		
		return $this->encodeHeader($this->app['helper']('Utility')->secureInput($str));

	}
	
	/**
     * Encode a header string optimally.
     * Picks shortest of Q, B, quoted-printable or none.
     * @access protected
     * @param string $str
     * @param string $position
     * @return string
     */
    protected function encodeHeader($str, $position = 'text')
    {
        $matchcount = 0;
		 switch (strtolower($position)) {
            case 'phrase':
            if (!preg_match('/[\200-\377]/', $str)) {
                    // Can't use addslashes as we don't know the value of magic_quotes_sybase
                    $encoded = addcslashes($str, "\0..\37\177\\\"");
                    if (($str == $encoded) && !preg_match('/[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~ -]/', $str)) {
                        return ($encoded);
                    } else {
                        return ("\"$encoded\"");
                    }
                }
                $matchcount = preg_match_all('/[^\040\041\043-\133\135-\176]/', $str, $matches);
				 break;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 'comment':
                $matchcount = preg_match_all('/[()"]/', $str, $matches);
                // Intentional fall-through
            case 'text':
            default:
                $matchcount += preg_match_all('/[\000-\010\013\014\016-\037\177-\377]/', $str, $matches);
                break;
		 }
        //There are no chars that need encoding
        if ($matchcount == 0) {
            return ($str);
        }
        $maxlen = 75 - 7 - strlen('utf-8');
            // More than a third of the content will need encoding, so B encoding will be most efficient
            $encoding = 'B';
            if (function_exists('mb_strlen') && $this->hasMultiBytes($str)) {
                // Use a custom function which correctly encodes and wraps long
                // multibyte strings without breaking lines within a character
                $encoded = $this->base64EncodeWrapMB($str, "\n");
            } else {
                $encoded = base64_encode($str);
                $maxlen -= $maxlen % 4;
                $encoded = trim(chunk_split($encoded, $maxlen, "\n"));
            }
        
        $encoded = preg_replace('/^(.*)$/m', ' =?' . 'utf-8' . "?$encoding?\\1?=", $encoded);
        $encoded = trim(str_replace("\n", "\n", $encoded));
        return $encoded;
    }


}
