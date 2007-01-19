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
 * Map a class to its DAO class.
 * 
 * Some classes have data stored in files or databases. We want to isolate
 * the application logic from that. So, we implemented the DAO (Data Access
 * Object) pattern. For every class that store data somewhere else, there
 * must be a DAO class. For example, a class 'Example' would be defined in
 * 'Example.class.php' and its DAO class would be named 'ExampleDAO' and
 * defined in 'Example.dao.php'.
 * 
 * @author Marco Aurelio Graciotto Silva
 * @license GPL
 * @since November/2006
 * @package FailureHandler
 */
class DAOMapper
{
	private $targetDir;
	
	private $classmap;
	
	private $files;
	
	private $daoFileSuffix = '.dao.php';
	
	private $daoClassSuffix = 'DAO';
	
	private $caching = true;
	
	/**
	 * Create a new DAOMapper.
	 * 
	 * @arg string $dir Directory to be used as home. May be left unassigned
	 * (the default value is the current directory's name).
	 */
	public function __construct($dir = null)
	{
		$this->setDir($dir);
		$this->mapDAO();
	}	

	/**
	 * Change the directory used as basedir.
	 * 
	 * @param string $dir Directory be used as home. May be left unassigned (the
	 * default value is the current directory's name).
	 * @throws Exception If the directory does not exist, an exception is thrown.
	 */
	public function setDir($dir)
	{
		if ($dir == null) {
			$dir = getcwd();
		}

		if (! is_dir($dir)) {
			throw new Exception("Directory does not exist.");
		}
		
		/*
		 * Always end the dirname with a slash (extra '/' are never a problem
		 * when appending a file or directory name to it, but a missing '/' can
		 * be troublesome.
		 */
		if ($dir{strlen($dir) - 1} != '/') {
			$dir .= '/';
		}
		
		$this->targetDir = $dir;
	}

	/**
	 * Find the PHP files that have DAO classes' definitions and load (include) their
	 * content.
	 * 
	 * Find the PHP files that have DAO classes' definitions. Only one class definition
	 * per file is allowed. The DAO class filename _must_ be suffixed with '.dao.php'
	 * (actually, the this- >daoFileSuffix value).
	 * 
	 * All the files found are included (include_once), so the DAO classes defined in
	 * those files are visible for the application.
	 * 
	 * @return array The DAO class files found.
	 */
	public function findFiles()
	{
		if ($this->caching == true && $this->files != null) {
			return $this->files;
		}
		
		$daoFiles = glob($this->targetDir . '*' . $this->daoFileSuffix, GLOB_NOSORT | GLOB_NOESCAPE);
		if ($daoFiles == FALSE) {
			$daoFiles = array();
		}
		
		foreach ($daoFiles as $file) {
			include_once($file);
		}
		
		$this->files = $daoFiles;
		return $this->files;
	}

	/**
	 * Maps the class and its DAO class.
	 * 
	 * Maps the class and its DAO class. It uses the filename as pattern. A
	 * class named 'TaskDAO' maps a DAO class for the 'Task' class.
	 * 
	 * The array format is 'ClassName' => 'DAO ClassName' (e.g. 'Task' => 'TaskDAO').
	 * 
	 * @return array The map between class and dao class files.
	 */
	public function mapDAO($files = null)
	{
		if ($files == null) {
			$files = $this->findFiles();
		}
		
		if ($this->caching == true && $this->classmap != null) {
			return $this->classmap;
		}

		$this->classmap = array();
		foreach ($files as $daoFile) {
			$re = '|^' . preg_quote($this->targetDir) . '(\w*)' . preg_quote($this->daoFileSuffix) . '$|';
			preg_match($re, $daoFile, $matches);
			$targetClass = $matches[1];
			$daoClass = $targetClass . $this->daoClassSuffix;
		}
		
		if (! isset($this->classmap[$targetClass])) { 
			$this->classmap[$targetClass] = $daoClass;
		}
	}
	
	/**
	 * Return the mapping of a class to its DAO class.
	 * 
	 * @return array Mapping of classes/DAO classes.
	 */
	public function getMapping()
	{
		if ($this->classmap == null) {
			$this->mapDAO();
		}
		return $this->classmap;
	}
}

?>