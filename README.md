ONVIF client library classes

ONVIFDevicemgmt

The class uses slightly modified devicemgmt.wsdl where endpoint is defined and the reference to onvif.xsd is fixed.
The endpoint is overwritten with the parameter but it is still needed

example:
```php
include( 'inc/ONVIFDevicemgmt.inc.php' );
ini_set( 'default_socket_timeout', 1800 ); 
$wsdl    = 'http://localhost/WSDL/devicemgmt-mod.wsdl';
$service = 'http://192.168.0.1:888/onvif/device_service';
$username = 'username';
$password = 'password';
$client = new ONVIFDevicemgmt( $wsdl, $service, $username, $password);
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
```

ONVIF PTZ client library class

The class uses slightly modified ptz.wsdl where endpoint is defined and the reference to onvif.xsd is fixed. 
The endpoint is overwritten with the parameter but it is still needed

```php
include( 'inc/ONVIFPTZ.inc.php' );
ini_set( 'default_socket_timeout', 1800 ); 
$wsdl    = 'http://localhost/WSDL/ptz-mod.wsdl';
$service = 'http://192.168.0.1:888/onvif/device_service';
$username = 'username';
$password = 'password';
$client = new ONVIFPTZ( $wsdl, $service, $username, $password);
try {
	$client->response_dump( 'GetServiceCapabilities', $client->get_service_capabilities() );
	$client->response_dump( 'GetNodes', $client->get_nodes() );
	$client->response_dump( 'GetNode', $client->get_node('ptz0') );
	$client->response_dump( 'GetConfigurations', $client->get_configurations() );
	$client->response_dump( 'GetConfiguration', $client->get_configuration('ptzconf0') );
	$client->response_dump( 'GetConfigurationOptions', $client->get_configuration_options('ptzconf0') );
} catch ( Exception $e ) {
	print "SOAP error occured\n";
	$res = $client->client->__getLastRequest();
	print "Last request:\n";
	print( $res . "\n" );
	$res = $client->client->__getLastResponse();
	print "Last response:\n";
	print( $res . "\n");
}
```


ONVIF Media client library class  

The class uses slightly modified media.wsdl where endpoint is defined and  
the reference to onvif.xsd is fixed. The endpoint is overwritten with the  
parameter but it is still needed

```php
include( 'inc/ONVIFMedia.inc.php' );
ini_set( 'default_socket_timeout', 1800 ); 
$wsdl    = 'http://localhost/WSDL/media-mod.wsdl';
$service = 'http://192.168.0.1:888/onvif/device_service';
$username = 'username';
$password = 'password';
$client = new ONVIFMedia( $wsdl, $service, $username, $password);
try {
	$client->response_dump( 'GetProfiles', $client->get_profiles() );
} catch ( Exception $e ) {
	print "SOAP error occured\n";
	$res = $client->client->__getLastRequest();
	print "Last request:\n";
	print( $res . "\n" );
	$res = $client->client->__getLastResponse();
	print "Last response:\n";
	print( $res . "\n");
}
```
