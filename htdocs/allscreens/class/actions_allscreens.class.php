<?php

class ActionsAllscreens
{
	function printTopRightMenu($parameters, $object, $action)
	{
		global $conf, $langs, $db, $user;
		$langs->load("allscreens@allscreens");

		echo '
		<script type="text/javascript">
		var dol_url_root = "' . DOL_URL_ROOT .'";
		var dol_version = "' . DOL_VERSION .'";
		var cashdesk_installed = "' . MAIN_MODULE_CASHDESK .'";
		';

		if ( ALLSCREENS_FIXED_MENU ) echo '$("body").addClass("fixed-menu");';

		if ( DOL_VERSION  >= "3.7.0") echo '$("body").addClass("v3_7-up");';
		if ( DOL_VERSION  < "3.7.0") echo '$("body").addClass("v3_7-down");';

		echo '</script>';

	}
}

?>