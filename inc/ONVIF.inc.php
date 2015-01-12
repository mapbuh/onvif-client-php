<?php
/**
 * ONVIF client library class
 *
 * The class uses slightly modified devicemgmt.wsdl where endpoint is defined and the reference to onvif.xsd is fixed.
 * The endpoint is overwritten with the parameter but it is still needed
 * 
 * <code> 
 * include( 'inc/ONVIF.inc.php' );
 * ini_set( 'default_socket_timeout', 1800 ); 
 * $wsdl    = 'http://localhost/WSDL/devicemgmt-mod.wsdl';
 * $service = 'http://192.168.0.1:888/onvif/device_service';
 * $username = 'username';
 * $password = 'password';
 * $client = new ONVIF( $wsdl, $service, $username, $password);
 * try {
 *	$res = $client->get_network_interfaces();
 *	var_dump( $res );
 * } catch ( Exception $e ) {
 *	print "SOAP error occured\n";
 *	$res = $client->client->__getLastRequest();
 *	print "Last request:\n";
 *	print( $res . "\n" );
 *	$res = $client->client->__getLastResponse();
 *	print "Last response:\n";
 *	print( $res . "\n");
 * }
 * </code>
 *
 * @author Nickola Trupcheff <n.trupcheff@gmail.com>
 * @version 0.1
 */

class ONVIF {
	public $wsdl;
	public $version;
	private $login;
	public $client;
	
	/**
	 * If you have troubles authorizing try syncing the time with the camera...
	 * and crazy as it sounds - capitalizing the first letter of the username
	 * @param string $wsdl URL for the modified devicemgmt.wsdl WSDL included with the library
	 * @param string $service Camera ONVIF URL
	 * @param string $username 
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

	 	$this->client->__setSoapHeaders($this->soapClientWSSecurityHeader($username,$password));
		return;
	}

	public function set_hostname( $hostname ) {
		$this->client->SetHostname(array(
				'Name' => $hostname
		));
	}

	public function get_hostname() {
		$res = $this->client->GetHostname();
		return $res->HostnameInformation->Name;
	}

	public function get_capabilities() {
		return $this->client->GetCapabilities();
	}

	public function get_wsdl_url() {
		$res = $this->client->GetWsdlUrl();
		return $res->WsdlUrl;
	}

	public function get_device_information() {
		return $this->client->GetDeviceInformation();
	}

	public function get_endpoint_reference() {
		return $this->client->GetEndpointReference();
	}

	public function get_services() {
		return $this->client->GetServices();
	}

	public function get_service_capabilities() {
		return $this->client->GetServiceCapabilities();
	}

	public function get_remote_user() {
		return $this->client->GetRemoteUser();
	}


	/**
	 * Set camera date and time
	 * @param bool $use_ntp
	 * @param bool $daylight_savings 
	 * @param string $time_zone The TZ format is specified by POSIX, please refer to POSIX 1003.1 section 8.3 example:
	 *				example: CET-1CEST,M3.5.0/2,M10.5.0/3 
	 * @param int $utc_date_time <timestamp> in UTC
	 * <code>
	 * set_system_date_and_time( false, false, 'CET-1CEST,M3.5.0/2,M10.5.0/3', time() );
	 * </code>
	 */
	public function set_system_date_and_time($use_ntp, $daylight_savings, $time_zone, $utc_date_time) {
		$req = array(
			'DateTimeType'    => $use_ntp ? 'NTP' : 'Manual',
			'DaylightSavings' => $daylight_savings,
			'TimeZone'        => array(
				'TZ' => $time_zone,
			),
			'UTCDateTime'     => array(
				'Time' => array(
					'Hour'   => gmdate( 'H', $utc_date_time ),
					'Minute' => gmdate( 'i', $utc_date_time ),
					'Second' => gmdate( 's', $utc_date_time ),
				),
				'Date' => array(
					'Year'   => gmdate( 'Y', $utc_date_time ),
					'Month'  => gmdate( 'm', $utc_date_time ),
					'Day'    => gmdate( 'd', $utc_date_time ),
				),
			),
		);
		$this->client->SetSystemDateAndTime( $req );
	}

	public function get_system_date_and_time() {
		return $this->client->GetSystemDateAndTime();
	}

	public function set_system_factory_default( $hard = true ) {
		$this->client->SetSystemFactoryDefault( array(
			'FactoryDefault' => $hard ? 'Hard' : 'Soft',
		));
	}

	public function system_reboot() {
		$this->client->SystemReboot();
	}

	public function get_system_backup() {
		return $this->client->GetSystemBackup();
	}

	public function get_system_support_information() {
		return $this->client->GetSystemSupportInformation();
	}

	public function get_system_log() {
		return $this->client->GetSystemLog();
	}

	public function get_scopes() {
		return $this->client->GetScopes();
	}

	/**
	 * Sets the scope parameters of a device 
	 * @param array $scopes list of configurable scopes
	 */
	public function set_scopes($scopes) {
		$this->client->SetScopes(
			$scopes
		);
	}

	/**
	 * Adds new configurable scope parameters to a device 
	 * @param array $scopes list of configurable scopes
	 */
	public function add_scopes($scopes) {
		$this->client->AddScopes(
			$scopes
		);
	}

	/**
	 * Remove configurable scope parameters from a device 
	 * @param array $scopes list of configurable scopes
	 */
	public function remove_scopes($scopes) {
		$this->client->RemoveScopes(
			$scopes
		);
	}

	public function get_discovery_mode() {
		$res =  $this->client->GetDiscoveryMode();
		return $res->DiscoveryMode;
	}

	/**
	 * Sets the discovery mode operation of a device
	 * @param string $mode Discoverable|NonDiscoverable
	 */
	public function set_discovery_mode($mode) {
		$this->client->SetDiscoveryMode(array(
			'DiscoveryMode' => $mode
		));
	}

	public function get_remote_discovery_mode() {
		$res =  $this->client->GetRemoteDiscoveryMode();
		return $res->RemoteDiscoveryMode;
	}

	/**
	 * Sets the remote discovery mode operation of a device
	 * @param string $mode Discoverable|NonDiscoverable
	 */
	public function set_remote_discovery_mode($mode) {
		$this->client->SetRemoteDiscoveryMode(array(
			'RemoteDiscoveryMode' => $mode
		));
	}

	public function get_dp_address() {
		return $this->client->GetDPAddresses();
	}

	public function get_users() {
		return $this->client->GetUsers();
	}

	/**
	 * <code>
	 * create_users( array(
	 *				array(
	 *					'Username' => 'user1',
	 *					'Password' => 'pass1',
	 *					'UserLevel' => 'Operator',
	 *				),
	 *				array(
	 *					'Username' => 'user2',
	 *					'Password' => 'pass2',
	 *					'UserLevel' => 'Operator',
	 *				),
	 * ));
	 * </code>
	 */
	public function create_users($users) {
		$this->client->CreateUsers(
			array('User' => $users )
		);
	}

	/**
	 * create user 
	 * @param string $username
	 * @param string $password
	 * @param string $level Administrator|Operator|User|Anonymous|Extended
	 */
	public function create_user( $username, $password, $level ) {
		$this->create_users( array(
			array(
				'Username' => $username,
				'Password' => $password,
				'UserLevel' => $level,
			)
		));
	}

	/**
	 * delete users
	 * @param array $users array of usernames
	 */
	public function delete_users($users) {
		$this->client->DeleteUsers(
			array('Username' => $users )
		);
	}

	public function delete_user($user) {
		$this->delete_users( array( $user ) );
	}

	/**
	 * accepts same param as create_users
	 */
	public function set_user( $users ) {
		$this->client->SetUser(array(
			'User' => $users
		));
	}

	/**
	 * @param bool $active True if the hostname shall be obtained via DHCP. 
	 * @return mixed RebootNeeded - Indicates whether or not a reboot is required after configuration updates. 
	 */
	public function set_hostname_from_dhcp( $active ) {
		return $this->client->SetHostnameFromDHCP(array(
			'FromDHCP' => $active
		));
	}

	public function get_dns() {
		return $this->client->GetDNS();
	}

	/**
	 * Sets the DNS settings on a device 
	 * @param bool $from_dhcp Indicate if the DNS address is to be retrieved using DHCP. 
	 * @param array $search_domain DNS search domain 
	 * @param mixed array of IPAddress structures
	 * <code>
	 * set_dns( 
	 *	false, 
	 *	array( 'example.com', 'second.example.com' ), 
	 *	array( 
	 *		array(
	 *			'Type' => 'IPv4',
	 *			'IPv4Address' => '192.168.0.2'
	 *		),
	 *		array(
	 *			'Type' => 'IPv4',
	 *			'IPv4Address' => '192.168.0.20',
	 *		)
	 *	)
	 * );
	 * </code>
	 */
	public function set_dns($from_dhcp, $search_domain, $dns_manual) {
		$this->client->SetDNS(array(
			'FromDHCP' => $from_dhcp,
			'SearchDomain' => $search_domain,
			'DNSManual' => $dns_manual
		));
	}

	public function get_ntp() {
		return $this->client->GetNTP(array());
	}

	/**
	 * <code>
	 * set_ntp( 
	 *	false,
	 *	array(
	 *		'Type' => 'DNS',
	 *		'DNSName' => '0.europe.pool.ntp.org',
	 *	)
	 * );
	 * </code>
	 */
	public function set_ntp($from_dhcp, $ntp_manual) {
		$this->client->SetNTP(array(
			'FromDHCP' => $from_dhcp,
			'NTPManual' => $ntp_manual
		));
	}

	public function get_dynamic_dns() {
		return $this->client->GetDynamicDNS();
	}

	/**
	 * @param string $type NoUpdate|ClientUpdates|ServerUpdates
	 * <code>
	 * set_dynamic_dns( 'ClientUpdates', 'ns1.example.com', 1800 );
	 * </code>
	 */
	public function set_dynamic_dns() {
		$this->client->SetDynamicDNS(array(
			'Type' => $type,
			'Name' => $name,
			'TTL'  => $ttl
		));
	}

	public function get_network_interfaces() {
		return $this->client->GetNetworkInterfaces();
	}

	private function soapClientWSSecurityHeader($user, $password) {
		// Creating date using yyyy-mm-ddThh:mm:ssZ format
		$tm_created = gmdate('Y-m-d\TH:i:s\Z', time()  );
		$tm_expires = gmdate('Y-m-d\TH:i:s\Z', date('U') + 180 ); //only necessary if using the timestamp element

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
		$timestamp->addChild('wsu:Expires', $tm_expires, $ns_wsu);

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
