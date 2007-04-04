
l<?php
// $Id: authenticator.class.php,v 1.13 2005/04/15 11:32:03 mosen Exp $

/**
 * Required by OpenID checkup.
 */
require_once('Auth/OpenID.php');
require_once('Services/Yadis/Yadis.php');

/**
 * Require the OpenID consumer code.
 */
require_once('Auth/OpenID/Consumer.php');
require_once('Auth/OpenID/FileStore.php');


function &getAuth($auth_mode)
{
	switch($auth_mode) {
		case "ldap":
			$auth = new LDAPAuthenticator();
			break;
		case "pn":
			$auth = new PostNukeAuthenticator();
			break;
		case "openid":
			$auth = new OpenIdAuthenticator();
			break;
		default:
			$auth = new SQLAuthenticator();
			break;
	}
	return $auth;
}

/*
 * Authenticator class
 */
class OpenIdAuthenticator extends SQLAuthenticator
{
	/**
	 * This is where the OpenID information will be stored.
	 */
	var $store_path = "/tmp/_php_consumer_test";
	
	var $store;
	
	var $consumer;

	var $user_id;
	var $username;
	
	function checkMath()
	{
		global $_Auth_OpenID_math_extensions;
		$ext = Auth_OpenID_detectMathLibrary($_Auth_OpenID_math_extensions);
		if (! isset($ext['extension']) || !isset($ext['class'])) {
			// Your PHP installation does not include big integer math
			// support. This support is required if you wish to run a
			// secure OpenID server without using SSL.
			return false;
		} else {
			switch ($ext['extension']) {
				case 'bcmath':
					break;
				case 'gmp':
					break;
				default:
					$class = $ext['class'];
					$lib = new $class();
					$one = $lib->init(1);
					$two = $lib->add($one, $one);
					$t = $lib->toString($two);
					if ($t != '2') {
						return false;
					}
			}
		}
		return true;
	}
		
	function checkRandom()
	{
		if (Auth_OpenID_RAND_SOURCE === null) {
			// Using (insecure) pseudorandom number source, because
			// Auth_OpenID_RAND_SOURCE has been defined as null.
			return false;
		}
		
		$numbytes = 6;
		$f = @fopen(Auth_OpenID_RAND_SOURCE, 'r');
		if ($f !== false) {
			$data = fread($f, $numbytes);
			$stat = fstat($f);
			$size = $stat['size'];
			fclose($f);
		} else {
			$data = null;
			$size = true;
		}
		
		if ($f !== false) {
			$dataok = (strlen($data) == $numbytes);
			$ok = $dataok && ! $size;
		} else {
	        $ok = false;
    	}
    	
    	return $ok;		
	}
	
	
	function checkStores()
	{
		foreach (array('sqlite', 'mysql', 'pgsql') as $dbext) {
			if (extension_loaded($dbext) || @dl($dbext . '.' . PHP_SHLIB_SUFFIX)) {
				$found[] = $dbext;
			}
		}
		
		if (count($found) == 0) {
			return false;
		}

		return true;
	}
	
	function checkfetcher()
	{
		$ok = true;
		$fetcher = Services_Yadis_Yadis::getHTTPFetcher();
		$fetch_url = 'http://www.openidenabled.com/resources/php-fetch-test';
		$expected_url = $fetch_url . '.txt';
		$result = $fetcher->get($fetch_url);
		
		if (isset($result)) {
			// list ($code, $url, $data) = $result;
			if ($result->status != '200') {
				$ok = false;
			}
			$url = $result->final_url;
			if ($url != $expected_url) {
				$ok = false;
			}
		} else {
			$ok = false;
		}
		
		return $ok;
	}
	
	
	function checkXml()
	{
		global $__Services_Yadis_xml_extensions;
		
		// Try to get an XML extension.
		$ext = Services_Yadis_getXMLParser();
		
		if ($ext !== null) {
			return true;
		} else {
			return false;
		}
	}

	function checkDependencies()
	{
		$result = true;
	
		$result &= $this->checkMath();
		$result &= $this->checkRandom();
		$result &= $this->checkStores();
		$result &= $this->checkFetcher();
		$result &= $this->checkXml();
		
		return $result;
	}
	
	function OpenIdAuthenticator()
	{
		$checklist = $this->checkDependencies();
		if ($checklist == false) {
			return null;
		}
		
		if (!file_exists($this->store_path) && ! mkdir($this->store_path)) {
			return null;
		}

		$this->store = new Auth_OpenID_FileStore($this->store_path);
		$this->consumer = new Auth_OpenID_Consumer($this->store);
	}
	
	function authenticate($username, $password, $phase, $redirect_url)
	{
		switch ($phase) {
			case 1:
				return $this->authenticate_phase1($username, $redirect_url);
				break;
			case 2:
				return $this->authenticate_phase2($username);
				break;
		}
		return false;
	}
	
	function authenticate_phase1($openid_url, $redirect_url)
	{
		if (empty($openid_url)) {
			return false;
		}
		
		$scheme = 'http';
		if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') {
			$scheme .= 's';
		}
		
		$process_url = sprintf("$scheme://%s:%s%s?login=openid&phase=2&username=$openid_url",
			$_SERVER['SERVER_NAME'],
			$_SERVER['SERVER_PORT'],
			$_SERVER['PHP_SELF']);
		
		$trust_root = sprintf("$scheme://%s:%s%s",
			$_SERVER['SERVER_NAME'],
			$_SERVER['SERVER_PORT'],
			dirname($_SERVER['PHP_SELF']));
			
		// Begin the OpenID authentication process.
		$auth_request = $this->consumer->begin($openid_url);

		// Handle failure status return values.
		if (! $auth_request) {
			return false;
		}

		$auth_request->addExtensionArg('sreg', 'optional', 'email');
		
		// Redirect the user to the OpenID server for authentication.  Store
		// the token for this authentication so we can verify the response.
		$redirect_url = $auth_request->redirectURL($trust_root, $process_url);
		
		header('Location: ' . $redirect_url);
	}

	function authenticate_phase2($username)
	{
		session_start();
		
		$response = $this->consumer->complete($_GET);
		if ($response->status == Auth_OpenID_CANCEL) {
	    	// This means the authentication was cancelled.
	    	$msg = 'Verification cancelled.';
	    	return false;
		} else if ($response->status == Auth_OpenID_FAILURE) {
	    	$msg = "OpenID authentication failed: " . $response->message;
	    	return false;
		} else if ($response->status == Auth_OpenID_SUCCESS) {
	    	// This means the authentication succeeded.
	    	$openid = $response->identity_url;
	    	$esc_identity = htmlspecialchars($openid, ENT_QUOTES);
	    	if ($response->endpoint->canonicalID) {
	        	$success .= '  (XRI CanonicalID: '.$response->endpoint->canonicalID.') ';
	    	}
	    	$sreg = $response->extensionResponse('sreg');

			$this->username = $username;	    	
			$this->user_id = $this->userExists($username); 
	    	if (! $this->user_id) {
				$this->createsqluser($username, '', '');
				$this->user_id = $this->userExists($username);  
			}
		
			return true;
		} else {
			return false;
		}
	}
	
	function userExists($username)
	{
		GLOBAL $db;
		$q  = new DBQuery;
		$result = false;
		$q->addTable('users');
		$q->addWhere("user_username = '$username'");
		$rs = $q->exec();
		if ($rs->RecordCount() > 0) 
		  $result = true;
		$q->clear();
		return $result;
	}

	function userId($username)
	{
		GLOBAL $db;
		$q  = new DBQuery;
		$q->addTable('users');
		$q->addWhere("user_username = '$username'");
		$rs = $q->exec();
		$row = $rs->FetchRow();
		$q->clear();
		return $row["user_id"];	
	}

	function createsqluser($username)
	{
		GLOBAL $db, $AppUI;
		
		$q = new DBQuery;
		$q->addTable('users');
		$q->addInsert('user_username', $username);
		$q->addInsert('user_type', '1');
		$q->exec();
		$user_id = $db->Insert_ID();
		$this->user_id = $user_id;
		$q->clear();

		$acl =& $AppUI->acl();
		$acl->insertUserRole($acl->get_group_id('anon'), $this->user_id);
	}
}
	

/**
 * PostNuke authentication has encoded information
 * passed in on the login request.  This needs to 
 * be extracted and verified.
 */
class PostNukeAuthenticator extends SQLAuthenticator
{
	function PostNukeAuthenticator()
	{
		global $dPconfig;
		$this->fallback = isset($dPconfig['postnuke_allow_login']) ? $dPconfig['postnuke_allow_login'] : false;
	}

	function authenticate($username, $password, $phase, $redirect)
	{
		global $db, $AppUI;
		if (!isset($_REQUEST['userdata'])) { // fallback to SQL Authentication if PostNuke fails.
			if (! $this->fallback) {
				die($AppUI->_('You have not configured your PostNuke site correctly'));
			}
		}

		if (! $compressed_data = base64_decode(urldecode($_REQUEST['userdata']))) {
			die($AppUI->_('The credentials supplied were missing or corrupted') . ' (1)');
		}
		if (! $userdata = gzuncompress($compressed_data)) {
			die($AppUI->_('The credentials supplied were missing or corrupted') . ' (2)');
		}
		if (! $_REQUEST['check'] = md5($userdata)) {
			die ($AppUI->_('The credentials supplied were issing or corrupted') . ' (3)');
		}
		$user_data = unserialize($userdata);

		// Now we need to check if the user already exists, if so we just
		// update.  If not we need to create a new user and add a default
		// role.
		$username = trim($user_data['login']);
		$this->username = $username;
		$names = explode(' ', trim($user_data['name']));
		$last_name = array_pop($names);
		$first_name = implode(' ', $names);
		$passwd = trim($user_data['passwd']);
		$email = trim($user_data['email']);
		
		$q  = new DBQuery;
		$q->addTable('users');
		$q->addQuery('user_id, user_password, user_contact');
		$q->addWhere("user_username = '$username'");
		if (! $rs = $q->exec()) {
			die($AppUI->_('Failed to get user details') . ' - error was ' . $db->ErrorMsg());
		}
		if ( $rs->RecordCount() < 1) {
			$q->clear();
			$this->createsqluser($username, $passwd, $email, $first_name, $last_name);
		} else {
			if (! $row = $rs->FetchRow())
				die($AppUI->_('Failed to retrieve user detail'));
			// User exists, update the user details.
			$this->user_id = $row['user_id'];
			$q->clear();
			$q->addTable('users');
			$q->addUpdate('user_password', $passwd);
			$q->addWhere("user_id = {$this->user_id}");
			if (! $q->exec()) {
				die($AppUI->_('Could not update user credentials'));
			}
			$q->clear();
			$q->addTable('contacts');
			$q->addUpdate('contact_first_name', $first_name);
			$q->addUpdate('contact_last_name', $last_name);
			$q->addUpdate('contact_email', $email);
			$q->addWhere("contact_id = {$row['user_contact']}");
			if (! $q->exec()) {
				die($AppUI->_('Could not update user details'));
			}
			$q->clear();
		}
		return true;
	}

	function createsqluser($username, $password, $email, $first, $last)
	{
		GLOBAL $db, $AppUI;

		require_once($AppUI->getModuleClass("contacts"));

		$c = New CContact();
		$c->contact_first_name = $first;
		$c->contact_last_name = $last;
		$c->contact_email = $email;
		$c->contact_order_by = "$last, $first";

		db_insertObject('contacts', $c, 'contact_id');
		$contact_id = ($c->contact_id == NULL) ? "NULL" : $c->contact_id;
		if (! $c->contact_id)
			die($AppUI->_('Failed to create user details'));

		$q  = new DBQuery;
		$q->addTable('users');
		$q->addInsert('user_username',$username );
		$q->addInsert('user_password', $password);
		$q->addInsert('user_type', '1');
		$q->addInsert('user_contact', $c->contact_id);
		if (! $q->exec())
			die($AppUI->_('Failed to create user credentials'));
		$user_id = $db->Insert_ID();
		$this->user_id = $user_id;
		$q->clear();

		$acl =& $AppUI->acl();
		$acl->insertUserRole($acl->get_group_id('anon'), $this->user_id);
	}
}

class SQLAuthenticator
{
	var $user_id;
	var $username;

	function authenticate($username, $password, $phase, $redirect)
	{
		GLOBAL $db, $AppUI;

		$this->username = $username;

		$q  = new DBQuery;
		$q->addTable('users');
		$q->addQuery('user_id, user_password');
		$q->addWhere("user_username = '$username'");
		if (!$rs = $q->exec()) {
			$q->clear();
			return false;
		}
		if (!$row = $q->fetchRow()) {
			$q->clear();
			return false;
		}

		$this->user_id = $row["user_id"];
		$q->clear();
		if (MD5($password) == $row["user_password"]) return true;
		return false;
	}

	function userId()
	{
		return $this->user_id;
	}
}

class LDAPAuthenticator extends SQLAuthenticator
{
	var $ldap_host;
	var $ldap_port;
	var $ldap_version;
	var $base_dn;
	var $ldap_search_user;
	var $ldap_search_pass;	
	var $filter;

	var $user_id;
	var $username;

	function LDAPAuthenticator()
	{
		GLOBAL $dPconfig;

		$this->fallback = isset($dPconfig['ldap_allow_login']) ? $dPconfig['ldap_allow_login'] : false;

		$this->ldap_host = $dPconfig["ldap_host"];
		$this->ldap_port = $dPconfig["ldap_port"];
		$this->ldap_version = $dPconfig["ldap_version"];
		$this->base_dn = $dPconfig["ldap_base_dn"];
		$this->ldap_search_user = $dPconfig["ldap_search_user"];
		$this->ldap_search_pass = $dPconfig["ldap_search_pass"];
		// Anonymous bind
		if (is_string($this->ldap_search_user) && strlen(trim($this->ldap_search_user)) == 0) {
			$this->ldap_search_user = NULL;
			$this->ldap_search_pass = NULL;
		}
		$this->filter = $dPconfig["ldap_user_filter"];
	}

	function authenticate($username, $password, $phase, $redirect)
	{
		GLOBAL $dPconfig;
		
		// Anonymous binding
		if (is_null($username) && ! is_null($password)) {
			return false;
		} 
		
		// User binding
		if (!is_null($username) && strlen($password) == 0) {
			return false;
		}
		
		$this->username = $username;

		$rs = @ldap_connect($this->ldap_host, $this->ldap_port);
		if (! $rs) {
			return false;
		}
		@ldap_set_option($rs, LDAP_OPT_PROTOCOL_VERSION, $this->ldap_version);
		@ldap_set_option($rs, LDAP_OPT_REFERRALS, 0);

		$ldap_bind_dn = $this->ldap_search_user;	
		$bindok = @ldap_bind($rs, $ldap_bind_dn, $this->ldap_search_pass);
		if (! $bindok) {
			// Uncomment for LDAP debugging
			/*	
			$error_msg = ldap_error($rs);
			die("Couldnt Bind Using ".$ldap_bind_dn."@".$this->ldap_host.":".$this->ldap_port." Because:".$error_msg);
			*/
			return false;
		}	else {
			$filter_r = str_replace("%USERNAME%", $username, $this->filter);
			$result = @ldap_search($rs, $this->base_dn, $filter_r);
			if (! $result) {
				return false; // ldap search returned nothing or error
			}
			
			$result_user = ldap_get_entries($rs, $result);
			if ($result_user["count"] == 0) {
				return false; // No users match the filter
			}

			$first_user = $result_user[0];
			$ldap_user_dn = $first_user["dn"];

			// Bind with the dn of the user that matched our filter (only one 
			// user should match sAMAccountName or uid etc..)
			$bind_user = @ldap_bind($rs, $ldap_user_dn, $password);
			if (! $bind_user) {
				/*
				$error_msg = ldap_error($rs);
				die("Couldnt Bind Using ".$ldap_user_dn."@".$this->ldap_host.":".$this->ldap_port." Because:".$error_msg);
				*/
				return false;
			} else {
				if ($this->userExists($username)) {
					return true;
				}	else {
					$this->createsqluser($username, $password, $first_user); 
				}
				return true;
			} 
		}
	}

	function createsqluser($username, $password, $ldap_attribs = Array())
	{
		GLOBAL $db, $AppUI;
		$hash_pass = MD5($password);

		require_once($AppUI->getModuleClass("contacts"));

		if (!count($ldap_attribs) == 0)
		{
			// Contact information based on the inetOrgPerson class schema
			$c = New CContact();
			$c->contact_first_name = $ldap_attribs["givenname"][0];
			$c->contact_last_name = $ldap_attribs["sn"][0];
			$c->contact_email = $ldap_attribs["mail"][0];
			$c->contact_phone = $ldap_attribs["telephonenumber"][0];
			$c->contact_mobile = $ldap_attribs["mobile"][0];
			$c->contact_city = $ldap_attribs["l"][0];
			$c->contact_country = $ldap_attribs["country"][0];
			$c->contact_state = $ldap_attribs["st"][0];
			$c->contact_zip = $ldap_attribs["postalcode"][0];
			$c->contact_job = $ldap_attribs["title"][0];

			//print_r($c); die();
			db_insertObject('contacts', $c, 'contact_id');
		}
		$contact_id = ($c->contact_id == NULL) ? "NULL" : $c->contact_id;

		$q  = new DBQuery;
		$q->addTable('users');
		$q->addInsert('user_username',$username );
		$q->addInsert('user_password', $hash_pass);
		$q->addInsert('user_type', '1');
		$q->addInsert('user_contact', $c->contact_id);
		$q->exec();
		$user_id = $db->Insert_ID();
		$this->user_id = $user_id;
		$q->clear();

		$acl =& $AppUI->acl();
		$acl->insertUserRole($acl->get_group_id('anon'), $this->user_id);
	}
}
?>
