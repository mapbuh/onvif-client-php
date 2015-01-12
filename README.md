ONVIF client library class

The class uses slightly modified devicemgmt.wsdl where endpoint is defined and the reference to onvif.xsd is fixed.
The endpoint is overwritten with the parameter but it is still needed

example:
include( 'inc/ONVIF.inc.php' );
ini_set( 'default_socket_timeout', 1800 ); 
$wsdl    = 'http://localhost/WSDL/devicemgmt-mod.wsdl';
$service = 'http://192.168.0.1:888/onvif/device_service';
$username = 'username';
$password = 'password';
$client = new ONVIF( $wsdl, $service, $username, $password);
try {
	$res = $client->get_network_interfaces();
	var_dump( $res );
} catch ( Exception $e ) {
	print "SOAP error occured\n";
	$res = $client->client->__getLastRequest();
	print "Last request:\n";
	print( $res . "\n" );
	$res = $client->client->__getLastResponse();
	print "Last response:\n";
	print( $res . "\n");
}
