m<?php
/*
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 * 
 * Copyright (C) 2006 magsilva
 */


require_once("../classes/dotproject.class.php");
include_once('daomapper.class.php');

class DotprojectWS
{
	public function ping()
	{
		return TRUE;
	}

	public function GetTask($id)
	{
		return TaskDAO($id);
	}
}

ini_set("soap.wsdl_cache_enabled", "0");
ini_set("session.auto_start", "0");
ini_set("default_socket_timeout", "30");

// session_start();
$mapper = new DAOMapper();
$classmap = $mapper->getMapping();

$server = new SoapServer("dotproject.wsdl", array('classmap' => $classmap));
$server->setClass("DotprojectWS");
$server->setPersistence(SOAP_PERSISTENCE_SESSION);
$server->handle();

?>
