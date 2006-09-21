<?php
/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Copyright (C) 2006 Marco Aurélio Graciotto Silva <magsilva@gmail.com>
*/

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
