<?php
/**
 * ONVIF Media client library class
 *
 * The class uses slightly modified media.wsdl where endpoint is defined and 
 * the reference to onvif.xsd is fixed. The endpoint is overwritten with the 
 * parameter but it is still needed
 * 
 * <code> 
 * include( 'inc/ONVIFMedia.inc.php' );
 * ini_set( 'default_socket_timeout', 1800 ); 
 * $wsdl    = 'http://localhost/WSDL/media-mod.wsdl';
 * $service = 'http://192.168.0.1:888/onvif/device_service';
 * $username = 'username';
 * $password = 'password';
 * $client = new ONVIFMedia( $wsdl, $service, $username, $password);
 * try {
 *	$res = $client->get_profiles();
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
require_once('ONVIF.inc.php');
class ONVIFMedia extends ONVIF {
	/**
	 * Returns the capabilities of the media service.
	 */
	public function get_service_capabilities() {
		return $this->client->GetServiceCapabilities();
	}

	/**
	 * This command lists all available physical video inputs of the device.
	 */
	public function get_video_sources() {
		return $this->client->GetVideoSources();
	}

	/**
	 * This command lists all available physical audio inputs of the device.
	 */
	public function get_audio_sources() {
		return $this->client->GetAudioSources();
	}

	/**
	 * This command lists all available physical audio outputs of the device.
	 */
	public function get_audio_outputs() {
		return $this->client->GetAudioOutputs();
	}

	public function create_profile() {
		throw new Exception("Not implemented");
	}

	public function get_profile() {
		throw new Exception("Not implemented");
	}

	/**
	 * List all configured profiles in a device.
	 */
	public function get_profiles() {
		return $this->client->GetProfiles();
	}

	/**
	 * Request a URI that can be used to initiate a live media stream using RTSP as the control protocol.
	 *
	 * @param string $stream_type RTP-Unicast|RTP-Multicast
	 * @param string $protocol UDP|RTSP|HTTP
	 * @param string $profile The ProfileToken element indicates the media profile to use and will define the configuration 
	 * 				of the content of the stream. Can be found with get_profiles, look under Profiles/0/token
	 */
	public function get_stream_uri($stream_type, $protocol, $profile = null) {
		return $this->client->GetStreamUri(array(
			'StreamSetup' => array(
				'Stream' => $stream_type,
				'Transport'  => array(
					'Protocol' => $protocol,
				),
			),
			'ProfileToken' => $profile
		));
	}

	/**
	 * List all video analytics configurations of a device. 
	 */
	public function get_video_analytics_configurations() {
		return $this->client->GetVideoAnalyticsConfigurations();
	}

	/**
	 * List all existing metadata configurations.
	 */
	public function get_metadata_configurations() {
		return $this->client->GetMetadataConfigurations();
	}

	public function get_snapshot_uri($media_profile) {
		return $this->client->GetSnapshotUri(array(
			'ProfileToken' => $media_profile
		));
	}

	public function get_osds() {
		return $this->client->GetOSDs();
	}
}
