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
