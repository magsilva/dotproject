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

Copyright (C) 2006 Marco Aurelio Graciotto Silva <magsilva@gmail.com>
*/

/**
 * Basic web service class.
 * 
 * Basic web service class. Any class that want to provide web services, must
 * extend this class
 *  
 * @package WebService
 * @author Marco Aurelio Graciotto Silva
 * @license GPL
 * @since November/2006
 * @package FailureHandler
 */
abstract class WebService
{
	/**
	 * Previous WSDL caching policy.
	 * @var bool
	 */
	private $previous_wsdl_cache;
	
	/**
	 * Previous HTTP session auto-start setting.
	 * @var bool
	 */
	private $previous_session_auto_start;
	
	/**
	 * Previous socket timeout.
	 * @var int
	 */
	private $previous_default_socket_timeout;
	
	
	/**
	 * Initialize the web service.
	 * 
	 * Initialize the web service and set some systems settings that affect
	 * web services (wsdl cache, HTTP session auto-start, socket timeout).
	 */
	protected function __construct()
	{
		$this->previous_wsdl_cache = ini_set("soap.wsdl_cache_enabled", "0");
		$this->previous_session_auto_start = ini_set("session.auto_start", "0");
		$this->previous_default_socket_timeout = ini_set("default_socket_timeout", "30");
	}
	
	/**
	 * Finalize the web service.
	 * 
	 * Finalize the web service, restoring all the system settings to its
	 * original values.
	 */
	protected function __destruct()
	{
		 ini_set("soap.wsdl_cache_enabled", $this->previous_wsdl_cache);
		 ini_set("session.auto_start", $this->previous_session_auto_start);
		 ini_set("default_socket_timeout", $this->previous_default_socket_timeout);
	}
	
	/**
	 * Start the web service.
	 */
	protected function start()
	{
		if ($this->get_mapping() != null) {
			$server = new SoapServer($this->get_wsdl());
		} else {
			$server = new SoapServer($this->get_wsdl(), array('classmap' => $this->get_mapping()));
		}
		$server->setClass(get_class($this));
		$server->setPersistence(SOAP_PERSISTENCE_SESSION);
		$server->handle();
	}

	/**
	 * Get the WSDL required by {@link start()}.
	 * 
	 * The WSDL is an XML file that describe the Web Services made available
	 * by the application.
	 * 
	 * @return string The WSDL filename.
	 */
	abstract protected function get_wsdl();
	
	/**
	 * Get the mapping between classes and XML elements.
	 * 
	 * @return array The mapping between classes and XML elements or the value
	 * NULL (if no mapping must be done).
	 */
	abstract protected function get_mapping();
}
?>
