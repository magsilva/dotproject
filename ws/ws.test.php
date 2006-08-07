<?php
include(dirname(__FILE__) . '/../../lib/nusoap/nusoap.php');

$wsdlfile = 'http://ws.serviceobjects.net/gc/GeoCash.axmx?WSDL';
$msg = 
'<GetATMLocation xmlns="http://www.serviceobjects.com/">
	<strInput>32804</strInput>
	<strLicenseKey>0</strLicenseKey>
</GetATMLocation>';

$s = new soapclient($wsdlfile, 'wsdl');
$s->call('GetATMLocations', array($msg));
var_dump($s->document);

/*
// $s->setHTTPProxy('proxy.mycompany.com', 8080);
// $s->setCredentials("admin", "admin");
$p = $s->getProxy();
$sq = $p->getQuote('ibm');

if (!$err = $p->getError()) {
	print "IBM current stock price: $sq.";
} else {
	print "ERROR: $err";
}
*/
?>
