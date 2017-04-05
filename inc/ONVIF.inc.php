<?php
/**
 * ONVIF base class
 *
 *
 * @author Nickola Trupcheff <n.trupcheff@gmail.com>
 * @version 0.1
 */

/**
 * The ONVIF class
 *
 * Contains some needed functions it is used by the other classes. Not to be used directly.
 */
class ONVIF {
	public $wsdl;
	public $version;
	protected $login;
	public $client;
	
	/**
	 * If you have troubles authorizing try syncing the time with the camera...
	 * and crazy as it sounds - capitalizing the first letter of the username
	 *
	 * @param string $wsdl URL for the modified devicemgmt.wsdl WSDL included with the library
	 * @param string $service Camera ONVIF URL
	 * @param string $username Camera username
	 * @param string $password Camera password
	 */
	public function __construct( $wsdl, $service, $username, $password ) {
		$this->wsdl = $wsdl;

		$this->client = new SoapClient($this->wsdl, array(
			'trace' => 1, 
			'exceptions' => true, 
#			'cache_wsdl' => WSDL_CACHE_NONE,
			'ssl' => array(
				'verify_peer' => false,
				'allow_self_signed' => true
			),
			'soap_version' => SOAP_1_2,
		));
		$this->client->__setLocation($service);

		# unfortunately this extra can be used only in devicemgmt
		if ( get_called_class() == 'ONVIFDevicemgmt' ) {
			$camera_datetime = $this->get_system_date_and_time();
			$camera_ts = gmmktime( 
				$camera_datetime->SystemDateAndTime->UTCDateTime->Time->Hour,
				$camera_datetime->SystemDateAndTime->UTCDateTime->Time->Minute,
				$camera_datetime->SystemDateAndTime->UTCDateTime->Time->Second,
				$camera_datetime->SystemDateAndTime->UTCDateTime->Date->Month,
				$camera_datetime->SystemDateAndTime->UTCDateTime->Date->Day,
				$camera_datetime->SystemDateAndTime->UTCDateTime->Date->Year
			);
			$this->client->__setSoapHeaders($this->soapClientWSSecurityHeader($username,$password, $camera_ts));
		} else {
			$this->client->__setSoapHeaders($this->soapClientWSSecurityHeader($username,$password));
		}
		return;
	}

	protected function soapClientWSSecurityHeader($user, $password, $ts = 0) {
		if ( $ts == 0 ) {
			$ts = time();
		}
		// Creating date using yyyy-mm-ddThh:mm:ssZ format
		$tm_created = gmdate('Y-m-d\TH:i:s\Z', $ts  );
#		$tm_expires = gmdate('Y-m-d\TH:i:s\Z', $ts + 180 ); //only necessary if using the timestamp element

		// Generating and encoding a random number
		$simple_nonce = mt_rand();
		$encoded_nonce = base64_encode($simple_nonce);

		// Compiling WSS string
		$passdigest = base64_encode(sha1($simple_nonce . $tm_created . $password, true));

		// Initializing namespaces
		$ns_wsse = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd';
		$ns_wsu = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd';
		$password_type = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordDigest';
		$encoding_type = 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0#Base64Binary';

		// Creating WSS identification header using SimpleXML
		$root = new SimpleXMLElement('<root/>');

		$security = $root->addChild('wsse:Security', null, $ns_wsse);

		//the timestamp element is not required by all servers
		$timestamp = $security->addChild('wsu:Timestamp', null, $ns_wsu);
		$timestamp->addAttribute('wsu:Id', 'Timestamp-28');
		$timestamp->addChild('wsu:Created', $tm_created, $ns_wsu);
#		$timestamp->addChild('wsu:Expires', $tm_expires, $ns_wsu);

		$usernameToken = $security->addChild('wsse:UsernameToken', null, $ns_wsse);
		$usernameToken->addChild('wsse:Username', $user, $ns_wsse);
		$usernameToken->addChild('wsse:Password', $passdigest, $ns_wsse)->addAttribute('Type', $password_type);
		$usernameToken->addChild('wsse:Nonce', $encoded_nonce, $ns_wsse)->addAttribute('EncodingType', $encoding_type);
		$usernameToken->addChild('wsu:Created', $tm_created, $ns_wsu);

		// Recovering XML value from that object
		$root->registerXPathNamespace('wsse', $ns_wsse);
		$full = $root->xpath('/root/wsse:Security');
		$auth = $full[0]->asXML();

		return new SoapHeader($ns_wsse, 'Security', new SoapVar($auth, XSD_ANYXML), true);
	}

	function obj_dump($object, $level = 1) {
		foreach( $object as $okey => $oval ) {
			if ( is_array( $oval ) or is_object( $oval ) ) {
				for( $i = 0; $i < $level * 3; $i++ ) {
					print " ";
				}
				print $okey."\n";
				$this->obj_dump( $oval, $level + 1 );
			} elseif( is_bool($oval) ) {
				for( $i = 0; $i < $level * 3; $i++ ) {
					print " ";
				}
				printf( "%s: %s\n", $okey, $oval ? 'true' : 'false' );
			} else {
				for( $i = 0; $i < $level * 3; $i++ ) {
					print " ";
				}
				printf( "%s: %s\n", $okey, $oval );
			}
		}
	}

	function response_dump($name,$response) {
		print "================================================================================\n";
		print "$name\n";
		print "--------------------------------------------------------------------------------\n";
		$this->obj_dump( $response, 1 );
		print "================================================================================\n";
	}

}
