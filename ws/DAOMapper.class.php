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


/**
 * Map a class to its DAO class.
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
	 * @arg $dir Directory to be used as base. May be left unassigned (the default
	 * value is the current directory name).
	 */
	function DAOMapper($dir = null)
	{
		$this->setDir($dir);
	}	

	/**
	 * Change the directory used as basedir.
	 */
	function setDir($dir)
	{
		if ($dir == null) {
			$dir = getcwd();
		}

		if (! is_dir($dir)) {
			throw new Exception("Directory does not exist.");
		}
		
		if ($dir{strlen($dir) - 1} != '/') {
			$dir .= '/';
		}
		
		$this->targetDir = $dir;
	}

	/**
	 * Find the PHP files that have DAO classes' definitions. Only one class definition
	 * per file is allowed. The DAO class filename _must_ be suffixed with '.dao.php'
	 * (actually, the this- >daoFileSuffix value).
	 */
	function findFiles()
	{
		if ($caching = false && $this->files == null) {
			return;
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
	 * Maps the class and its DAO class. It uses the filename as pattern. A class named
	 * 'TaskDAO' maps a DAO class for the 'Task' class.
	 * 
	 * The array format is 'ClassName' => 'DAOClassName' (e.g. 'Task' => 'TaskDAO').
	 */
	function mapDAO($files)
	{
		if ($caching = false && $this->classmap == null) {
			return;
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
	 * Create the mapping of DAO classes.
	 */
	function createMapping()
	{
		$files = $this->findFiles();
		$this->mapDAO($files);
	}
	
	/**
	 * Return the mapping of a class to its DAO class.
	 */
	function getMapping()
	{
		if ($this->classmap == null) {
			$this->createMapping();
		}
		return $this->classmap;
	}
	
}

?>