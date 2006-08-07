<?php

$client = new SoapClient("dotproject.wsdl");

try {
	$return = $client->ping();
	echo("Ping: " . $return);
} catch (SoapFault $exception) {
	var_dump($exception);
} 

?>
