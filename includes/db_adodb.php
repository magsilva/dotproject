<?php /* $Id: db_adodb.php,v 1.24 2005/03/25 04:49:14 ajdonnison Exp $ */
/*
	Based on Leo West's (west_leo@yahooREMOVEME.com):
	lib.DB
	Database abstract layer
	-----------------------
	ADODB VERSION
	-----------------------
	A generic database layer providing a set of low to middle level functions
	originally written for WEBO project, see webo source for "real life" usages
*/

require_once("$baseDir/lib/adodb/adodb.inc.php");

global $db;
$db = db_connect($dPconfig['dbtype'], $dPconfig['dbhost'], $dPconfig['dbname'], $dPconfig['dbuser'], $dPconfig['dbpass'], $dPconfig['dbpersist']);


function db_connect($dbdriver = 'mysql', $host = 'localhost', $dbname, $user = 'root', $passwd = '', $persist = false)
{
	global $ADODB_FECTH_MODE;
	
	$db = NewADOConnection($dbdriver);
	
	if ($persist) {
		$db->PConnect($host, $user, $passwd, $dbname) or die('FATAL ERROR: Connection to database server failed');
	} else {
		$db->Connect($host, $user, $passwd, $dbname) or die('FATAL ERROR: Connection to database server failed');
	}
	$ADODB_FETCH_MODE = ADODB_FETCH_BOTH;
	
	return $db;
}

function db_close()
{
	global $db;
	
	$db->Close();
}

function db_error()
{
	global $db;
	if (! is_object($db)) {
		dprint(__FILE__, __LINE__, 0, "Database object does not exist");
	}
	return $db->ErrorMsg();
}

function db_errno()
{
	global $db;
	if (! is_object($db)) {
		dprint(__FILE__, __LINE__, 0, "Database object does not exist");
	}
	return $db->ErrorNo();
}

function db_insert_id()
{
	global $db;
	if (! is_object($db)) {
		dprint(__FILE__, __LINE__, 0, "Database object does not exist");
	}
	return $db->Insert_ID();
}

function db_exec($sql)
{
	global $db, $baseDir;
	if (! is_object($db)) {
		dprint(__FILE__,__LINE__, 0, "Database object does not exist");
	}
	$qid = $db->Execute($sql);
	dprint(__FILE__, __LINE__, 10, $sql);
	if ($msg = db_error()) {
		dprint(__FILE__, __LINE__, 0, "Error executing: <pre>$sql</pre>");
		// Useless statement, but it is being executed only on error,
		// and it stops infinite loop.
		$db->Execute( $sql );
		if (!db_error())
			echo '<script language="JavaScript"> location.reload();</script>';
        }
		if (! $qid && preg_match('/^\<select\>/i', $sql)) {
			dprint(__FILE__, __LINE__, 0, $sql);
		}
		return $qid;
}

function db_free_result($cur)
{
	// TODO: mysql_free_result( $cur );   (Maybe it's done my Adodb)
	if (! is_object($cur)) {
		dprint(__FILE__, __LINE__, 0, "Invalid object passed to db_free_result");
	}
	$cur->Close();
}

function db_num_rows($qid)
{
	if (! is_object($qid)) {
	  dprint(__FILE__, __LINE__, 0, "Invalid object passed to db_num_rows");
	}
	return $qid->RecordCount();
	//return $db->Affected_Rows();
}

function db_fetch_row(&$qid)
{
	if (! is_object($qid)) {
		dprint(__FILE__, __LINE__, 0, "Invalid object passed to db_fetch_row");
	}
	return $qid->FetchRow();
}

function db_fetch_assoc(&$qid)
{
	if (! is_object($qid)) {
		dprint(__FILE__, __LINE__, 0, "Invalid object passed to db_fetch_assoc");
	}
	return $qid->FetchRow();
}

function db_fetch_array(&$qid)
{
	if (! is_object($qid)) {
		dprint(__FILE__, __LINE__, 0, "Invalid object passed to db_fetch_array");
	}
	$result = $qid->FetchRow();
	// Ensure there are numerics in the result.
	if ($result && ! isset($result[0])) {
		$ak = array_keys($result);
		foreach ($ak as $k => $v) {
			$result[$k] = $result[$v];
		}
	}
	return $result;
}

function db_fetch_object($qid)
{
	if (! is_object($qid))
	  dprint(__FILE__, __LINE__, 0, "Invalid object passed to db_fetch_object");
	return $qid->FetchNextObject(false);
}

function db_escape($str)
{
	global $db;
	return substr($db->qstr( $str ), 1, -1);
}

function db_version()
{
	return "ADODB";
}

function db_unix2dateTime($time)
{
	global $db;
	return $db->DBDate($time);
}

function db_dateTime2unix($time)
{
	global $db;
	return $db->UnixDate($time);
}

/**
* This global function loads the first field of the first row returned by the query.
*
* @param string The SQL query
* @return The value returned in the query or null if the query failed.
*/
function db_loadResult($sql)
{
	$cur = db_exec( $sql );
	$cur or exit(db_error());
	$ret = null;
	if ($row = db_fetch_row($cur)) {
		$ret = $row[0];
	}
	db_free_result($cur);
	return $ret;
}

/**
* This global function loads the first row of a query into an object
*
* If an object is passed to this function, the returned row is bound to the existing elements of <var>object</var>.
* If <var>object</var> has a value of null, then all of the returned query fields returned in the object. 
* @param string The SQL query
* @param object The address of variable
*/
function db_loadObject( $sql, &$object, $bindAll = false , $strip = true)
{
	if ($object != null) {
		$hash = array();
		if( !db_loadHash($sql, $hash)) {
			return false;
		}
		bindHashToObject($hash, $object, null, $strip, $bindAll);
		return true;
	} else {
		$cur = db_exec($sql);
		$cur or exit(db_error());
		if ($object = db_fetch_object($cur)) {
			db_free_result($cur);
			return true;
		} else {
			$object = null;
			return false;
		}
	}
}

/**
* This global function return a result row as an associative array 
*
* @param string The SQL query
* @param array An array for the result to be return in
* @return <b>True</b> is the query was successful, <b>False</b> otherwise
*/
function db_loadHash($sql, &$hash)
{
	$cur = db_exec($sql);
	$cur or exit(db_error());
	$hash = db_fetch_assoc($cur);
	db_free_result($cur);
	if ($hash == false) {
		return false;
	} else {
		return true;
	}
}

/**
* Document::db_loadHashList()
*
* { Description }
*
* @param string $index
*/
function db_loadHashList( $sql, $index='' ) {
	$cur = db_exec( $sql );
	$cur or exit( db_error() );
	$hashlist = array();
	while ($hash = db_fetch_array( $cur )) {
		$hashlist[$hash[$index ? $index : 0]] = $index ? $hash : $hash[1];
	}
	db_free_result( $cur );
	return $hashlist;
}

/**
* Document::db_loadList()
*
* { Description }
*
* @param [type] $maxrows
*/
function db_loadList( $sql, $maxrows=NULL ) {
	GLOBAL $AppUI;
	if (!($cur = db_exec( $sql ))) {;
		$AppUI->setMsg( db_error(), UI_MSG_ERROR );
		return false;
	}
	$list = array();
	$cnt = 0;
	while ($hash = db_fetch_assoc( $cur )) {
		$list[] = $hash;
		if( $maxrows && $maxrows == $cnt++ ) {
			break;
		}
	}
	db_free_result( $cur );
	return $list;
}

/**
* Document::db_loadColumn()
*
* { Description }
*
* @param [type] $maxrows
*/
function db_loadColumn( $sql, $maxrows=NULL ) {
	GLOBAL $AppUI;
	if (!($cur = db_exec( $sql ))) {;
		$AppUI->setMsg( db_error(), UI_MSG_ERROR );
		return false;
	}
	$list = array();
	$cnt = 0;
	$row_index = null;
	while ($row = db_fetch_row( $cur )) {
		if (! isset($row_index)) {
			if (isset($row[0])) {
				$row_index = 0;
			} else {
				$row_indices = array_keys($row);
				$row_index = $row_indices[0];
			}
		}
		$list[] = $row[$row_index];
		if( $maxrows && $maxrows == $cnt++ ) {
			break;
		}
	}
	db_free_result( $cur );
	return $list;
}

/* return an array of objects from a SQL SELECT query
 * class must implement the Load() factory, see examples in Webo classes
 * @note to optimize request, only select object oids in $sql
 */
function db_loadObjectList( $sql, $object, $maxrows = NULL ) {
	$cur = db_exec( $sql );
	if (!$cur) {
		die( "db_loadObjectList : " . db_error() );
	}
	$list = array();
	$cnt = 0;
	$row_index = null;
	while ($row = db_fetch_array( $cur )) {
		if (! isset($row_index)) {
			if (isset($row[0]))
				$row_index = 0;
			else {
				$row_indices = array_keys($row);
				$row_index = $row_indices[0];
			}
		}
		$object->load( $row[$row_index] );
		$list[] = $object;
		if( $maxrows && $maxrows == $cnt++ ) {
			break;
		}
	}
	db_free_result( $cur );
	return $list;
}


/**
* Document::db_insertArray()
*
* { Description }
*
* @param [type] $verbose
*/
function db_insertArray( $table, &$hash, $verbose=false ) {
	$fmtsql = "insert into $table ( %s ) values( %s ) ";
	foreach ($hash as $k => $v) {
		if (is_array($v) or is_object($v) or $v == NULL) {
			continue;
		}
		$fields[] = $k;
		$values[] = "'" . db_escape(htmlspecialchars( $v )) . "'";
	}
	$sql = sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) );

	($verbose) && print "$sql<br />\n";

	if (!db_exec( $sql )) {
		return false;
	}
	$id = db_insert_id();
	return true;
}

/**
* Document::db_updateArray()
*
* { Description }
*
* @param [type] $verbose
*/
function db_updateArray( $table, &$hash, $keyName, $verbose=false ) {
	$fmtsql = "UPDATE $table SET %s WHERE %s";
	foreach ($hash as $k => $v) {
		if( is_array($v) or is_object($v) or $k[0] == '_' ) // internal or NA field
			continue;

		if( $k == $keyName ) { // PK not to be updated
			$where = "$keyName='" . db_escape( $v ) . "'";
			continue;
		}
		if ($v == '') {
			$val = 'NULL';
		} else {
			$val = "'" . db_escape(htmlspecialchars( $v )) . "'";
		}
		$tmp[] = "$k=$val";
	}
	$sql = sprintf( $fmtsql, implode( ",", $tmp ) , $where );
	($verbose) && print "$sql<br />\n";
	$ret = db_exec( $sql );
	return $ret;
}

/**
* Document::db_delete()
*
* { Description }
*
*/
function db_delete( $table, $keyName, $keyValue ) {
	$keyName = db_escape( $keyName );
	$keyValue = db_escape( $keyValue );
	$ret = db_exec( "DELETE FROM $table WHERE $keyName='$keyValue'" );
	return $ret;
}


/**
* Document::db_insertObject()
*
* { Description }
*
* @param [type] $keyName
* @param [type] $verbose
*/
function db_insertObject( $table, &$object, $keyName = NULL, $verbose=false ) {
	$fmtsql = "INSERT INTO `$table` ( %s ) VALUES ( %s ) ";
	foreach (get_object_vars( $object ) as $k => $v) {
		if (is_array($v) or is_object($v) or $v == NULL) {
			continue;
		}
		if ($k[0] == '_') { // internal field
			continue;
		}
		$fields[] = $k;
		$values[] = "'" . db_escape(htmlspecialchars( $v )) . "'";
	}
	$sql = sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) );
	($verbose) && print "$sql<br />\n";
	if (!db_exec( $sql )) {
		return false;
	}
	$id = db_insert_id();
	($verbose) && print "id=[$id]<br />\n";
	if ($keyName && $id)
		$object->$keyName = $id;
	return true;
}

/**
* Document::db_updateObject()
*
* { Description }
*
* @param [type] $updateNulls
*/
function db_updateObject( $table, &$object, $keyName, $updateNulls=true ) {
	$fmtsql = "UPDATE `$table` SET %s WHERE %s";
	foreach (get_object_vars( $object ) as $k => $v) {
		if( is_array($v) or is_object($v) or $k[0] == '_' ) { // internal or NA field
			continue;
		}
		if( $k == $keyName ) { // PK not to be updated
			$where = "$keyName='" . db_escape( $v ) . "'";
			continue;
		}
		if ($v === NULL && !$updateNulls) {
			continue;
		}
		if( $v == '' ) {
			$val = "''";
		} else {
			$val = "'" . db_escape(htmlspecialchars( $v )). "'";
		}
		$tmp[] = "$k=$val";
	}
	if (count ($tmp)) {
		$sql = sprintf( $fmtsql, implode( ",", $tmp ) , $where );
		return db_exec( $sql );
	} else {
		return true;
	}
}

/**
* Document::db_dateConvert()
*
* { Description }
*
*/
function db_dateConvert( $src, &$dest, $srcFmt ) {
	$result = strtotime( $src );
	$dest = $result;
	return ( $result != 0 );
}

/**
* Document::db_datetime()
*
* { Description }
*
* @param [type] $timestamp
*/
function db_datetime( $timestamp = NULL ) {
	if (!$timestamp) {
		return NULL;
	}
	if (is_object($timestamp)) {
		return $timestamp->toString( '%Y-%m-%d %H:%M:%S');
	} else {
		return strftime( '%Y-%m-%d %H:%M:%S', $timestamp );
	}
}

/**
* Document::db_dateTime2locale()
*
* { Description }
*
*/
function db_dateTime2locale( $dateTime, $format ) {
	if (intval( $dateTime)) {
		$date = new CDate( $dateTime );
		return $date->format( $format );
	} else {
		return null;
	}
}

/*
* copy the hash array content into the object as properties
* only existing properties of object are filled. when undefined in hash, properties wont be deleted
* @param array the input array
* @param obj byref the object to fill of any class
* @param string
* @param boolean
* @param boolean
*/
function bindHashToObject( $hash, &$obj, $prefix=NULL, $checkSlashes=true, $bindAll=false )
{
	is_array( $hash ) or die( "bindHashToObject : hash expected" );
	is_object( $obj ) or die( "bindHashToObject : object expected" );

	if ($bindAll) {
		foreach ($hash as $k => $v) {
			$obj->$k = ($checkSlashes && get_magic_quotes_gpc()) ? stripslashes( $hash[$k] ) : $hash[$k];
		}
	} else if ($prefix) {
		foreach (get_object_vars($obj) as $k => $v) {
			if (isset($hash[$prefix . $k ])) {
				$obj->$k = ($checkSlashes && get_magic_quotes_gpc()) ? stripslashes( $hash[$k] ) : $hash[$k];
			}
		}
	} else {
		foreach (get_object_vars($obj) as $k => $v) {
			if (isset($hash[$k])) {
				$obj->$k = ($checkSlashes && get_magic_quotes_gpc()) ? stripslashes( $hash[$k] ) : $hash[$k];
			}
		}
	}
	//echo "obj="; print_r($obj); exit;
}


?>
