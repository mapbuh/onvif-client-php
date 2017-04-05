<?php
/**
 * ONVIF PTZ client library class
 *
 * The class uses slightly modified ptz.wsdl where endpoint is defined and 
 * the reference to onvif.xsd is fixed. The endpoint is overwritten with the 
 * parameter but it is still needed
 * 
 * <code> 
 * include( 'inc/ONVIFPTZ.inc.php' );
 * ini_set( 'default_socket_timeout', 1800 ); 
 * $wsdl    = 'http://localhost/WSDL/ptz-mod.wsdl';
 * $service = 'http://192.168.0.1:888/onvif/device_service';
 * $username = 'username';
 * $password = 'password';
 * $client = new ONVIFPTZ( $wsdl, $service, $username, $password);
 * try {
 *	$res = $client->get_service_capabilities();
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
require_once( 'ONVIF.inc.php');
class ONVIFPTZ extends ONVIF {
	/**
	 * Returns the capabilities of the PTZ service.
	 */
	public function get_service_capabilities() {
		return $this->client->GetServiceCapabilities();
	}

	/**
	 * Get the descriptions of the available PTZ Nodes.
	 */
	public function get_nodes() {
		return $this->client->GetNodes();
	}

	/**
	 * Get a specific PTZ Node identified by a reference token or a name.
	 *
	 * @param string $node reference token or a name
	 */
	public function get_node($node) {
		return $this->client->GetNode(array(
			'NodeToken' => $node,
		));
	}

	/**
	 * Get a specific PTZonfiguration from the device, identified by its reference token or name.
	 *
	 * @param string $token reference token or name
	 */
	public function get_configuration($token) {
		return $this->client->GetConfiguration(array(
			'PTZConfigurationToken' => $token,
		));
	}

	/**
	 * Get all the existing PTZConfigurations from the device.
	 */
	public function get_configurations() {
		return $this->client->GetConfigurations();
	}

	public function set_configuration() {
		throw new Exception( "Not implemeted" );
	}

	/**
	 * List supported coordinate systems including their range limitations.
	 *
	 * @param string $token Token of an existing configuration that the options are intended for.
	 */
	public function get_configuration_options($token) {
		return $this->client->GetConfigurationOptions(array(
			'ConfigurationToken' => $token
		));
	}

	public function send_auxiliary_command() {
		throw new Exception( "Not implemented" );
	}

	/**
	 * Operation to request all PTZ presets for the PTZNode in the selected profile.
	 *
	 * @param string $profile A reference to the MediaProfile where the operation should take place.
	 */
	public function get_presets($profile) {
		return $this->client->GetPresets(array(
			'ProfileToken' => $profile
		));
	}

	public function set_preset() {
		throw new Exception("Not implemented");
	}

	public function remove_preset() {
		throw new Exception("Not implemented");
	}

	public function goto_preset() {
		throw new Exception("Not implemented");
	}

	
}
