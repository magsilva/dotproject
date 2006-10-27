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
 * Basic issue handler class. It gathers generic system information (usually
 * useful for any debugging purpose.
 */
abstract class IssueHandler
{
	protected function __construct()
	{
	}
	
	protected function __destruct()
	{
	}
	
	public function createReport()
	{
		$report = array();
		
		$report['PHP'] = array();
		$report['PHP']['Version'] = phpversion();
		$report['PHP']['Zend Version'] = zend_version();
		$report['PHP']['OS'] = php_uname('s') . ' ' . php_uname('r') . ' (' . php_uname('v') . ')';
		$report['PHP']['System architecture'] = php_uname('m');
		
		$filelist = php_ini_scanned_files();
		if ($filelist !== FALSE && strlen($filelist) > 0) {
			$report['PHP']['Configuration (INI) files'] = array();
			$files = explode(',', $filelist);
			foreach ($files as $file) {
				$report['PHP']['Configuration (INI) files'][] = trim($file);
			}
	    }
		$report['PHP']['Allows short tags (<? ?>)'] = (bool) ini_get('short_open_tag');
		$report['PHP']['Allows ASP tags (<% %>)'] = (bool) ini_get('asp_tags');
		$report['PHP']['Force CGI redirect'] = (bool) ini_get('cgi.force_redirect');
		$report['PHP']['Socket timeout'] = ini_get('default_socket_timeout');
		$report['PHP']['Maximum parsing time'] = ini_get('max_input_time');
		$report['PHP']['Maximum execution time'] = ini_get('max_execution_time');
		$report['PHP']['Max POST request size'] = ini_get('post_max_size');
		$report['PHP']['Open base directory'] = ini_get('open_basedir');
		// $report['PHP']['Temporary directory'] = sys_get_temp_dir();
		$report['PHP']['Safe mode enabled'] = (bool) ini_get('safe_mode');
		if (ini_get('safe_mode')) {
			$report['PHP']['Use uid to restrict access'] = (bool) ! ini_get('safe_mode_gid');
			$report['PHP']['Use gid to restrict access'] = (bool) ini_get('safe_mode_gid');
			$report['PHP']['Bypass check for files in the directory'] = ini_get('safe_mode_include_dir');
			$report['PHP']['Allow the execution of files in the directory'] = ini_get('safe_mode_exec_dir');
			$report['PHP']['Allowed environment variables'] = ini_get('safe_mode_allowed_env_vars');
			if (empty($report['PHP']['Allowed global variables'])) {
				$report['PHP']['Allowed global variables'] = array_keys($_ENV);
			}
			$report['PHP']['Write-protected environment variables'] = ini_get('safe_mode_protected_env_vars');
			$report['PHP']['Disabled functions'] = ini_get('disable_functions');
			$report['PHP']['Disabled classes'] = ini_get('disable_classes');
		}
		$report['PHP']['Open base directory'] = ini_get('open_basedir');
		$report['PHP']['Register RAW post data'] = (bool) ini_get('always_populate_raw_post_data');
		$report['PHP']['Allow WebDAV HTTP methods'] = (bool) ini_get('allow_webdav_methods');
		$report['PHP']['Register globals'] = (bool) ini_get('register_globals');
		$report['PHP']['Register long arrays'] = (bool) ini_get('register_long_arrays');
		$report['PHP']['Register command line arguments'] = (bool) ini_get('register_argc_argv');
		$report['PHP']['Register globals (SERVER and ENV) variables'] = (bool) ini_get('auto_globals_jit');
		if ($report['PHP']['Register globals'] || $report['PHP']['Register long arrays'] || $report['PHP']['Register command line arguments']) {
			$report['PHP']['Register globals (SERVER and ENV) variables'] = true;
		}
		$report['PHP']['SAPI'] = php_sapi_name();
		$report['PHP']['Loaded extensions'] = get_loaded_extensions();
		
		$report['System'] = array();
		$report['System']['Environment variables'] = $_ENV;
		if (function_exists('getrusage')) {
			$system_usage = getrusage();
			$report['System']['System usage'] = array();
			$report['System']['System usage']['# swaps'] = $system_usage['ru_nswap']; 
			$report['System']['System usage']['# page faults'] = $system_usage['ru_majflt']; 
			$report['System']['System usage']['User time used (seconds)'] = $system_usage['ru_utime.tv_sec'];
			$report['System']['System usage']['User time used (microsecond)'] = $system_usage['ru_utime.tv_usec'];
		}
		$report['System']['Memory utilization'] = array();
		$report['System']['Memory utilization']['Memory limit'] = ini_get('memory_limit');
		if (function_exists('memory_get_usage')) {
			$report['System']['Memory utilization']['Memory used'] = memory_get_usage();
		}
		if (function_exists('memory_get_peak_usage')) {
			$report['System']['Memory utilization']['Peak usage (bytes)'] = memory_get_peak_usage();
		}

		$report['Magic quotes'] = array();
		$report['Magic quotes']['GPC'] = (bool) get_magic_quotes_gpc();
		$report['Magic quotes']['Runtime'] = (bool) get_magic_quotes_runtime();
		$report['Magic quotes']['Sybase'] = (bool) ini_get('magic_quotes_sybase');
		
		$report['Current user'] = array();
		$report['Current user']['username'] = get_current_user();
		$report['Current user']['uid'] = getmyuid();
		$report['Current user']['gid'] = getmygid();
		$report['Current user']['pid'] = getmypid();
		
		$report['Files'] = array();
		// List of directories separated with a colon in Unix or semicolon in Windows.
		$report['Files']['Include path'] = get_include_path();
		$report['Files']['Prepend files'] = ini_get('auto_prepend_file');
		$report['Files']['Append files'] = ini_get('auto_append_file');
		$report['Files']['Included files'] = get_included_files();

		if (! ini_get('file_uploads')) {
			$report['File uploading'] = ini_get('file_uploads');
		} else {
			$report['File uploading'] = array();
			$report['File uploading']['Temporary directory'] = ini_get('upload_tmp_dir');
			$report['File uploading']['Maximum file size'] = min(ini_get('upload_max_filesize'), ini_get('post_max_size'));
		}
			
		
		$report['Constants'] = get_defined_constants(true); 
		$report['Variables'] = get_defined_vars();
		unset($report['Variables']['report']);
		 
		$report['Predefined variables'] = array();
		// Variables set by the web server or otherwise directly related to the
		// execution environment of the current script.
		$report['Predefined variables']['SERVER'] = $_SERVER;
		// Variables provided to the script via the GET, POST, and COOKIE input
		// mechanisms, and which therefore cannot be trusted. The presence and
		// order of variable inclusion in this array is defined according to the
		// PHP variables_order configuration directive.
		$report['Predefined variables']['HTTP Request variable inclusion order'] = ini_get('variables_order'); 
		$report['Predefined variables']['HTTP Request'] = $_REQUEST;
		$report['Predefined variables']['HTTP GET'] = $_GET;
		$report['Predefined variables']['HTTP POST'] = $_POST; 
		$report['Predefined variables']['HTTP Cookie'] = $_COOKIE; 
		$report['Predefined variables']['Uploaded files'] = $_FILES;
		$report['Predefined variables']['Session'] = $_SESSION;

		$report['Session'] = array();
		$report['Session']['Name'] = session_name();
		$report['Session']['Auto-start session'] = ini_get('session.auto_start');
		$report['Session']['Enable cookies'] = ini_get('session.use_cookies');
		$report['Session']['Enable only cookies'] = ini_get('session.use_only_cookies');
		$report['Session']['Cookie lifetime'] = ini_get('session.cookie_lifetime');
		$report['Session']['Cookie path'] = ini_get('session.cookie_path');
		$report['Session']['Cookie domain'] = ini_get('session.cookie_domain');
		$report['Session']['Mark the cookie accessible only by HTTP'] = ini_get('session.cookie_httponly');
		$report['Session']['Cache control method'] = ini_get('session.cache_limiter');
		$report['Session']['Cache timeout'] = ini_get('session.cache_expire');
		$report['Session']['Hash function'] = ini_get('session.hash_function');
		$report['Session']['Enable transparent SID'] = ini_get('session.use_trans_sid');
		$report['Session']['Tags that are rewriten if transparent SID is enabled'] = ini_get('url_rewriter.tags');
		$report['Session']['Expected referer prefix'] = ini_get('session.referer_check');
		$report['Session']['Maximum unused time before deletion'] = ini_get('session.gc_maxlifetime');
		$report['Session']['Handler function'] = ini_get('session.save_handler');
		$report['Session']['Handler argument'] = ini_get('session.save_path');
		$report['Session']['Serializer'] = ini_get('session.serialize_handler');

		$report['Request'] = array();
		if (ini_get('browscap')) {
			$report['Request']['Browser capability'] = get_browser(null, true);
		}
		
		$report['Response mimetype'] = ini_get('default_mimetype');
		$report['Response charset'] = ini_get('default_charset');

		// 0 - NORMAL, 1 - ABORTED, 2 - TIMEOUT
		$report['Connection status'] = connection_status();
		
		$report['Backtrace'] = debug_backtrace();
		unset($report['Backtrace'][0]);
		
		return $report;
	}
}
