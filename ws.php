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
 
Copyright (C) 2007 Marco Aurelio Graciotto Silva <magsilva@gmail.com>
*/

include_once(dirname(__FILE__) . '/lib/nusoap/class.soap_client.php');

/**
 * http://www.beanizer.org/index.php3?page=openjms4php&pnum=3
 * http://xfire.codehaus.org/PHP+Interoperation
 * http://arsenalist.com/2007/01/19/php-client-for-web-services/
 */
class OpenJms4PHP
{
	var $proxy;
	var $params;
	var $sess;
	var $client;
	
	function OpenJms4PHP($endpoint)
	{
		$this->client = new soap_client($endpoint, true);
		
		/*
		$client = new SoapClient('http://localhost:8191/JMSService?wsdl',
			array(
				'trace' => 1,
				'soap_version' => SOAP_1_1,
				'style' => SOAP_DOCUMENT,
				'encoding' => SOAP_LITERAL
			)
		);
		*/
		
		$this->proxy = $this->client->getProxy();
	}
	
	function PostMsgToQueue($queueName,$msg)
	{
		return $this->proxy->PostMsgToQueue(array("in0"=>$queueName,"in1"=>$msg));
	}
	
	function PostMsgToTopic($topicName,$msg)
	{
		return $this->proxy->PostMsgToTopic(array("in0"=>$topicName,"in1"=>$msg));
	}
	
	function getMsgFromTopic($topicName,$consumerName)
	{
		return $this->proxy->getMsgFromTopic(array("in0"=>$topicName,"in1"=>$consumerName));
	}
	
	function getMsgFromQueue($queueName)
	{
		return $this->proxy->getMsgFromQueue(array("in0"=>$queueName));
	}               
}

$jms = new OpenJms4PHP('http://localhost:8191/JMSService?wsdl');
$result=$jms->PostMsgToQueue('queue/testQueue', 'test message');
echo '<pre>';
print_r($result);
echo '</pre>';

/*
$response = $client->echo(array("in0" => "come back to me"));
$str = $response->out;
$str == "come back to me";
*/
?>
