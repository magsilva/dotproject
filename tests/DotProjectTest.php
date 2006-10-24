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

require_once( 'TestCase.class.php' );
require_once( dirname( __FILE__ ) . '/../classes/dotproject.class.php' );

class DotProjectTest extends TestCase
{
	protected $dP;
 
	protected function setUp()
	{
	}
	
    public function testGlobalsBeforeMethod1()
    {
    	global $AppUI, $dPconfig, $db, $baseDir, $baseUrl;
    	
		$this->assertNull($AppUI);
    	$this->assertNull($dPconfig);
		$this->assertNull($db);
		$this->assertNull($baseDir);
		$this->assertNull($baseUrl);
		
		$this->dP = new DotProject();
		$this->dP->start();
		$this->dP->stop();
    }


    public function testGlobalsBeforeMethod2()
    {
		$this->assertNull($GLOBALS['AppUI']);
    	$this->assertNull($GLOBALS['dPconfig']);
		$this->assertNull($GLOBALS['db']);
		$this->assertNull($GLOBALS['baseDir']);
		$this->assertNull($GLOBALS['baseUrl']);
		
		$this->dP = new DotProject();
		$this->dP->start();
		$this->dP->stop();
	}

	public function testGlobalsAfterMethod1()
    {
    	global $AppUI, $dPconfig, $db, $baseDir, $baseUrl;

		$this->dP = new DotProject();
		$this->dP->start();
    	
		$this->assertNotNull($AppUI);
    	$this->assertNotNull($dPconfig);
		$this->assertNotNull($db);
		$this->assertNotNull($baseDir);
		$this->assertNotNull($baseUrl);

		$this->dP->stop();
    }

    public function testGlobalsAfterMethod2()
    {
		$this->dP = new DotProject();
		$this->dP->start();
		
		$this->assertNotNull($GLOBALS['AppUI']);
    	$this->assertNotNull($GLOBALS['dPconfig']);
		$this->assertNotNull($GLOBALS['db']);
		$this->assertNotNull($GLOBALS['baseDir']);
		$this->assertNotNull($GLOBALS['baseUrl']);
		
		$this->dP->stop();
    }
    
    public function testGlobalsAroundMethod1()
    {
    	global $AppUI, $dPconfig, $db, $baseDir, $baseUrl;
    	
		$this->assertNull($AppUI);
    	$this->assertNull($dPconfig);
		$this->assertNull($db);
		$this->assertNull($baseDir);
		$this->assertNull($baseUrl);
		
		$this->dP = new DotProject();
		$this->dP->start();

		$this->assertNotNull($AppUI);
    	$this->assertNotNull($dPconfig);
		$this->assertNotNull($db);
		$this->assertNotNull($baseDir);
		$this->assertNotNull($baseUrl);

		$this->dP->stop();

		$this->assertNull($AppUI);
    	$this->assertNull($dPconfig);
		$this->assertNull($db);
		$this->assertNull($baseDir);
		$this->assertNull($baseUrl);

    }

	public function testGlobalsAroundMethod2()
    {
		$this->assertNull($GLOBALS['AppUI']);
    	$this->assertNull($GLOBALS['dPconfig']);
		$this->assertNull($GLOBALS['db']);
		$this->assertNull($GLOBALS['baseDir']);
		$this->assertNull($GLOBALS['baseUrl']);
		
		$this->dP = new DotProject();
		$this->dP->start();

		$this->assertNotNull($GLOBALS['AppUI']);
    	$this->assertNotNull($GLOBALS['dPconfig']);
		$this->assertNotNull($GLOBALS['db']);
		$this->assertNotNull($GLOBALS['baseDir']);
		$this->assertNotNull($GLOBALS['baseUrl']);

		$this->dP->stop();

		$this->assertNull($GLOBALS['AppUI']);
    	$this->assertNull($GLOBALS['dPconfig']);
		$this->assertNull($GLOBALS['db']);
		$this->assertNull($GLOBALS['baseDir']);
		$this->assertNull($GLOBALS['baseUrl']);
	}

    
/**
 *     public function testDisableMagicQuotesGPC()
    {
    	$this->assertEquals(0, get_magic_quotes_gpc() );
    }
 */	
}


?>