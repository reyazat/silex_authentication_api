<?php
 
namespace Command;
 
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
 
class WebSocketServer extends Command
{
	
	protected $app;
	public function __construct($app){
		
		$this->app = $app; 
		parent::__construct();
		
	}
	
    protected function configure()
    {
        $this
            ->setName('WebSocket:server')
            ->setDescription('open 8010 port on server for real time action on browser using websocket.');
    }
	
	protected function interact(InputInterface $input, OutputInterface $output){}
 
    protected function execute(InputInterface $input, OutputInterface $output)
    {

		/*$loop   = \React\EventLoop\Factory::create();
		$pusher = $this->app['helper']('WebSocketApp');

		// Listen for the web server to make a ZeroMQ push after an ajax request
		$context = new \React\ZMQ\Context($loop);
		$pull = $context->getSocket(\ZMQ::SOCKET_PULL);
		$pull->bind('tcp://0.0.0.0:5555'); // Binding to 127.0.0.1 means the only client that can connect is itself
		$pull->on('message', array($pusher, 'onBlogEntry'));

		// Set up our WebSocket server for clients wanting real-time updates
		$webSock = new \React\Socket\Server($loop);
		$webSock->listen(8010, '0.0.0.0'); // Binding to 0.0.0.0 means remotes can connect
		$webSock = new \React\Socket\SecureServer($webSock, $loop, ['local_cert' => '/var/www/clients/client1/web24/ssl/crm.smartysoftware.net.pem']);

		$webServer = new \Ratchet\Server\IoServer(
			new \Ratchet\Http\HttpServer(
				new \Ratchet\WebSocket\WsServer(
					new \Ratchet\Wamp\WampServer(
						$pusher
					)
				)
			),
			$webSock
		);

		$loop->run();*/
		
		$loop   = \React\EventLoop\Factory::create();
		$pusher = $this->app['helper']('WebSocketApp');

		// ZMQ binding
		$context = new \React\ZMQ\Context($loop);
		$pull = $context->getSocket(\ZMQ::SOCKET_PULL);
		$pull->bind('tcp://0.0.0.0:5555'); // Binding to 127.0.0.1 means the only client that can connect is itself (127.0.0.1 || 0.0.0.0)
		$pull->on('error', array($pusher, 'onError'));
		$pull->on('message', array($pusher, 'onBlogEntry'));

		// Set up secure React server
		$webSock = new \React\Socket\SecureServer(
			new \React\Socket\Server($loop),
			$loop,
			array(
				'local_cert' => '/etc/letsencrypt/live/crm.smartysoftware.net/fullchain.pem',
				'local_pk'          => '/etc/letsencrypt/live/crm.smartysoftware.net/privkey.pem', // path to your server private key
				'allow_self_signed' => TRUE, // Allow self signed certs (should be false in production)
				'verify_peer' => false,
			)
		);
		$webSock->listen(8010, '0.0.0.0'); // Binding to 0.0.0.0 means remotes can connect (127.0.0.1 || 0.0.0.0)

		// Ratchet magic
		$webServer = new \Ratchet\Server\IoServer(
			new \Ratchet\Http\HttpServer(
				new \Ratchet\WebSocket\WsServer(
					new \Ratchet\Wamp\WampServer(
						$pusher
					)
				)
			),
			$webSock
		);

		$loop->run();
		
		
	}
}