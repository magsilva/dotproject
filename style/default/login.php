<?php /* STYLE/DEFAULT $Id: login.php,v 1.32 2005/03/31 20:11:15 gregorerhardt Exp $ */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title><?php echo $dPconfig['page_title'];?></title>
	<meta http-equiv="Content-Type" content="text/html;charset=<?php echo isset( $locale_char_set ) ? $locale_char_set : 'UTF-8';?>" />
	<title><?php echo $dPconfig['company_name'];?> :: dotProject Login</title>
	<meta http-equiv="Pragma" content="no-cache" />
	<meta name="Version" content="<?php echo @$AppUI->getVersion();?>" />
	<link rel="stylesheet" type="text/css" href="./style/<?php echo $uistyle;?>/main.css" media="all" />
	<style type="text/css" media="all">@import "./style/<?php echo $uistyle;?>/main.css";</style>
	<link rel="shortcut icon" href="./style/<?php echo $uistyle;?>/images/favicon.ico" type="image/ico" />
	<meta http-equiv="X-XRDS-Location" content="<?php echo $baseUrl; ?>/dotproject.xml" />
</head>

<body onload="document.loginform.username.focus();">
<br />
<br />
<br />
<br />
<form method="post" action="<?php echo $loginFromPage; ?>" name="loginform">
	<input type="hidden" name="login" value="<?php echo time();?>" />
	<input type="hidden" name="lostpass" value="0" />
	<input type="hidden" name="redirect" value="<?php echo $redirect;?>" />

	<table align="center" border="0" width="250" cellpadding="6" cellspacing="0" class="std">
	<tr>
		<th colspan="2"><em><?php echo $dPconfig['company_name'];?></em></th>
	</tr>
	<tr>
		<td align="right" nowrap><?php echo $AppUI->_('Username');?>:</td>
		<label for="openid_url_comment_form">
		<td align="left" nowrap><input type="text" size="40" maxlength="100" name="username" class="text" id="openid_url_comment_form"/>
		</label>
		</td>
	</tr>
	<tr>
		<td align="right" nowrap><?php echo $AppUI->_('Password');?>:</td>
		<td align="left" nowrap><input type="password" size="25" maxlength="32" name="password" class="text" /></td>
	</tr>
	<tr>
		<td align="left" nowrap><a href="http://www.dotproject.net/"><img src="./style/default/images/dp_icon.gif" border="0" alt="dotProject logo" /></a></td>
		<td align="right" valign="bottom" nowrap><input type="submit" name="login" value="<?php echo $AppUI->_('login');?>" class="button" /></td>
	</tr>
	<tr>
		<td colspan="2"><a href="#" onclick="f=document.loginform;f.lostpass.value=1;f.submit();"><?php echo $AppUI->_('forgotPassword');?></a></td>
	</tr>
</table>
</form>

<?php if (@$AppUI->getVersion()) { ?>
<div align="center">
	<span style="font-size:7pt">Version <?php echo @$AppUI->getVersion();?></span>
</div>
<?php } ?>

<div align="center">
<?php
	echo '<span class="error">'.$AppUI->getMsg().'</span>';
?>
</div>

<div align="center">
	<?php echo "* ".$AppUI->_("You must have cookies enabled in your browser"); ?>
</div>

</body>

</html>