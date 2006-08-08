<?php


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
		if ($dir == null || ! is_dir($dir)) {
			$dir = getcwd();
		}
		
		if ($dir{strlen($dir)} != '/') {
			$dir{strlen($dir)} = '/';
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