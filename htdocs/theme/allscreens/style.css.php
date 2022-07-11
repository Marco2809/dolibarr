<?php
/* Copyright (C) 2004-2013	Laurent Destailleur		<eldy@users.sourceforge.net>
 * Copyright (C) 2006		Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (C) 2007-2012	Regis Houssin			<regis.houssin@capnetworks.com>
 * Copyright (C) 2011		Philippe Grand			<philippe.grand@atoo-net.com>
 * Copyright (C) 2012		Juanjo Menent			<jmenent@2byte.es>
 *
 * Copyright (C) 2013-2014  Nicolas Rivera          <theme@creajutsu.com>
 * Copyright (C) 2015       Serge Azout             <contact@msmobile.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *		\file       htdocs/theme/allscreens/style.css.php
 *		\brief      File for CSS style sheet AllScreens
 */

//if (! defined('NOREQUIREUSER')) define('NOREQUIREUSER','1');	// Not disabled because need to load personalized language
//if (! defined('NOREQUIREDB'))   define('NOREQUIREDB','1');	// Not disabled to increase speed. Language code is found on url.
if (! defined('NOREQUIRESOC'))    define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN')) define('NOREQUIRETRAN','1');	// Not disabled because need to do translations
if (! defined('NOCSRFCHECK'))     define('NOCSRFCHECK',1);
if (! defined('NOTOKENRENEWAL'))  define('NOTOKENRENEWAL',1);
if (! defined('NOLOGIN'))         define('NOLOGIN',1);          // File must be accessed by logon page so without login
if (! defined('NOREQUIREMENU'))   define('NOREQUIREMENU',1);
if (! defined('NOREQUIREHTML'))   define('NOREQUIREHTML',1);
if (! defined('NOREQUIREAJAX'))   define('NOREQUIREAJAX','1');

session_cache_limiter(FALSE);

$res=@include("../../main.inc.php");             // For root directory
if (! $res && file_exists($_SERVER['DOCUMENT_ROOT']."/main.inc.php"))
    $res=@include($_SERVER['DOCUMENT_ROOT']."/main.inc.php"); // Use on dev env only
if (! $res) $res=@include("../../../main.inc.php");      // For "custom" directory

// Load user to have $user->conf loaded (not done into main because of NOLOGIN constant defined)
if (empty($user->id) && ! empty($_SESSION['dol_login'])) $user->fetch('',$_SESSION['dol_login']);


// Define css type
header('Content-type: text/css');
// Important: Following code is to avoid page request by browser and PHP CPU at
// each Dolibarr page access.
if (empty($dolibarr_nocache)) header('Cache-Control: max-age=3600, public, must-revalidate');
else header('Cache-Control: no-cache');

// On the fly GZIP compression for all pages (if browser support it). Must set the bit 3 of constant to 1.
if (isset($conf->global->MAIN_OPTIMIZE_SPEED) && ($conf->global->MAIN_OPTIMIZE_SPEED & 0x04)) { ob_start("ob_gzhandler"); }

if (GETPOST('lang')) $langs->setDefaultLang(GETPOST('lang'));	// If language was forced on URL
if (GETPOST('theme')) $conf->theme=GETPOST('theme');  // If theme was forced on URL
$langs->load("main",0,1);
$right=($langs->trans("DIRECTION")=='rtl'?'left':'right');
$left=($langs->trans("DIRECTION")=='rtl'?'right':'left');

$path='';    	// This value may be used in future for external module to overwrite theme
$theme='allscreens';	// Value of theme
if (! empty($conf->global->MAIN_OVERWRITE_THEME_RES)) { $path='/'.$conf->global->MAIN_OVERWRITE_THEME_RES; $theme=$conf->global->MAIN_OVERWRITE_THEME_RES; }



$dol_hide_topmenu=$conf->dol_hide_topmenu;
$dol_optimize_smallscreen=$conf->dol_optimize_smallscreen;
$dol_no_mouse_hover=$conf->dol_no_mouse_hover;
$dol_use_jmobile=$conf->dol_use_jmobile;


// Define reference colors
// Example: Light grey: $colred=235;$colgreen=235;$colblue=235;
// Example: Pink:       $colred=230;$colgreen=210;$colblue=230;
// Example: Green:      $colred=210;$colgreen=230;$colblue=210;
// Example: Ocean:      $colred=220;$colgreen=220;$colblue=240;
//$conf->global->THEME_ELDY_ENABLE_PERSONALIZED=0;
//$user->conf->THEME_ELDY_ENABLE_PERSONALIZED=0;
//var_dump($user->conf->THEME_ELDY_RGB);
$colred  =empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED)?(empty($conf->global->THEME_ELDY_RGB)?235:hexdec(substr($conf->global->THEME_ELDY_RGB,0,2))):(empty($user->conf->THEME_ELDY_RGB)?235:hexdec(substr($user->conf->THEME_ELDY_RGB,0,2)));
$colgreen=empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED)?(empty($conf->global->THEME_ELDY_RGB)?235:hexdec(substr($conf->global->THEME_ELDY_RGB,2,2))):(empty($user->conf->THEME_ELDY_RGB)?235:hexdec(substr($user->conf->THEME_ELDY_RGB,2,2)));
$colblue =empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED)?(empty($conf->global->THEME_ELDY_RGB)?235:hexdec(substr($conf->global->THEME_ELDY_RGB,4,2))):(empty($user->conf->THEME_ELDY_RGB)?235:hexdec(substr($user->conf->THEME_ELDY_RGB,4,2)));

// Colors
$isred=max(0,(2*$colred-$colgreen-$colblue)/2);        // 0 - 255
$isgreen=max(0,(2*$colgreen-$colred-$colblue)/2);      // 0 - 255
$isblue=max(0,(2*$colblue-$colred-$colgreen)/2);       // 0 - 255
$colorbackhmenu1=($colred-3).','.($colgreen-3).','.($colblue-3);         // topmenu
$colorbackhmenu2=($colred+5).','.($colgreen+5).','.($colblue+5);
$colorbackvmenu1=($colred+15).','.($colgreen+16).','.($colblue+17);      // vmenu
$colorbackvmenu1b=($colred+5).','.($colgreen+6).','.($colblue+7);        // vmenu (not menu)
$colorbackvmenu2=($colred-15).','.($colgreen-15).','.($colblue-15);
$colorbacktitle1=($colred-5).','.($colgreen-5).','.($colblue-5);    // title of array
$colorbacktitle2=($colred-15).','.($colgreen-15).','.($colblue-15);
$colorbacktabcard1=($colred+15).','.($colgreen+16).','.($colblue+17);  // card
$colorbacktabcard2=($colred-15).','.($colgreen-15).','.($colblue-15);
$colorbacktabactive=($colred-15).','.($colgreen-15).','.($colblue-15);
$colorbacklineimpair1=(244+round($isred/3)).','.(244+round($isgreen/3)).','.(244+round($isblue/3));    // line impair
$colorbacklineimpair2=(250+round($isred/3)).','.(250+round($isgreen/3)).','.(250+round($isblue/3));    // line impair
$colorbacklineimpairhover=(230+round(($isred+$isgreen+$isblue)/9)).','.(230+round(($isred+$isgreen+$isblue)/9)).','.(230+round(($isred+$isgreen+$isblue)/9));    // line impair
$colorbacklinepair1='255,255,255';    // line pair
$colorbacklinepair2='255,255,255';    // line pair
$colorbacklinepairhover=(230+round(($isred+$isgreen+$isblue)/9)).','.(230+round(($isred+$isgreen+$isblue)/9)).','.(230+round(($isred+$isgreen+$isblue)/9));
//$colorbackbody='#ffffff url('.$img_head.') 0 0 no-repeat;';
$colorbackbody='#fcfcfc';
$colortext='40,40,40';
$colortexttopmenu='#ffffff';
$fontsize=empty($conf->dol_optimize_smallscreen)?'12':'14';
$fontsizesmaller=empty($conf->dol_optimize_smallscreen)?'11':'14';

// Eldy colors
if (empty($conf->global->THEME_ELDY_ENABLE_PERSONALIZED)) {
    $conf->global->THEME_ELDY_TOPMENU_BACK1='140,160,185';    // topmenu
    $conf->global->THEME_ELDY_TOPMENU_BACK2='236,236,236';
    $conf->global->THEME_ELDY_VERMENU_BACK1='255,255,255';    // vmenu
    $conf->global->THEME_ELDY_VERMENU_BACK1b='230,232,232';   // vmenu (not menu)
    $conf->global->THEME_ELDY_VERMENU_BACK2='240,240,240';
    $conf->global->THEME_ELDY_BACKTITLE1='140,160,185';       // title of arrays
    $conf->global->THEME_ELDY_BACKTITLE2='230,230,230';
    $conf->global->THEME_ELDY_BACKTABCARD2='210,210,210';     // card
    $conf->global->THEME_ELDY_BACKTABCARD1='234,234,234';
    $conf->global->THEME_ELDY_BACKTABACTIVE='234,234,234';
    //$conf->global->THEME_ELDY_BACKBODY='#ffffff url('.$img_head.') 0 0 no-repeat;';
    $conf->global->THEME_ELDY_BACKBODY='#fcfcfc;';
    $conf->global->THEME_ELDY_LINEIMPAIR1='242,242,242';
    $conf->global->THEME_ELDY_LINEIMPAIR2='248,248,248';
    $conf->global->THEME_ELDY_LINEIMPAIRHOVER='238,246,252';
    $conf->global->THEME_ELDY_LINEPAIR1='255,255,255';
    $conf->global->THEME_ELDY_LINEPAIR2='255,255,255';
    $conf->global->THEME_ELDY_LINEPAIRHOVER='238,246,252';
    $conf->global->THEME_ELDY_TEXT='50,50,130';
    if ($dol_use_jmobile) {
        $conf->global->THEME_ELDY_BACKTABCARD1='245,245,245';    // topmenu
        $conf->global->THEME_ELDY_BACKTABCARD2='245,245,245';
        $conf->global->THEME_ELDY_BACKTABACTIVE='245,245,245';
    }
}

$colorbackhmenu1     =empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED)?(empty($conf->global->THEME_ELDY_TOPMENU_BACK1)?$colorbackhmenu1:$conf->global->THEME_ELDY_TOPMENU_BACK1)   :(empty($user->conf->THEME_ELDY_TOPMENU_BACK1)?$colorbackhmenu1:$user->conf->THEME_ELDY_TOPMENU_BACK1);
$colorbackhmenu2     =empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED)?(empty($conf->global->THEME_ELDY_TOPMENU_BACK2)?$colorbackhmenu2:$conf->global->THEME_ELDY_TOPMENU_BACK2)   :(empty($user->conf->THEME_ELDY_TOPMENU_BACK2)?$colorbackhmenu2:$user->conf->THEME_ELDY_TOPMENU_BACK2);
$colorbackvmenu1     =empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED)?(empty($conf->global->THEME_ELDY_VERMENU_BACK1)?$colorbackvmenu1:$conf->global->THEME_ELDY_VERMENU_BACK1)   :(empty($user->conf->THEME_ELDY_VERMENU_BACK1)?$colorbackvmenu1:$user->conf->THEME_ELDY_VERMENU_BACK1);
$colorbackvmenu1b    =empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED)?(empty($conf->global->THEME_ELDY_VERMENU_BACK1b)?$colorbackvmenu1:$conf->global->THEME_ELDY_VERMENU_BACK1b) :(empty($user->conf->THEME_ELDY_VERMENU_BACK1b)?$colorbackvmenu1b:$user->conf->THEME_ELDY_VERMENU_BACK1b);
$colorbackvmenu2     =empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED)?(empty($conf->global->THEME_ELDY_VERMENU_BACK2)?$colorbackvmenu2:$conf->global->THEME_ELDY_VERMENU_BACK2)   :(empty($user->conf->THEME_ELDY_VERMENU_BACK2)?$colorbackvmenu2:$user->conf->THEME_ELDY_VERMENU_BACK2);
$colorbacktitle1     =empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED)?(empty($conf->global->THEME_ELDY_BACKTITLE1)   ?$colorbacktitle1:$conf->global->THEME_ELDY_BACKTITLE1)      :(empty($user->conf->THEME_ELDY_BACKTITLE1)?$colorbacktitle1:$user->conf->THEME_ELDY_BACKTITLE1);
$colorbacktitle2     =empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED)?(empty($conf->global->THEME_ELDY_BACKTITLE2)   ?$colorbacktitle2:$conf->global->THEME_ELDY_BACKTITLE2)      :(empty($user->conf->THEME_ELDY_BACKTITLE2)?$colorbacktitle2:$user->conf->THEME_ELDY_BACKTITLE2);
$colorbacktabcard1   =empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED)?(empty($conf->global->THEME_ELDY_BACKTABCARD1) ?$colorbacktabcard1:$conf->global->THEME_ELDY_BACKTABCARD1)  :(empty($user->conf->THEME_ELDY_BACKTABCARD1)?$colorbacktabcard1:$user->conf->THEME_ELDY_BACKTABCARD1);
$colorbacktabcard2   =empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED)?(empty($conf->global->THEME_ELDY_BACKTABCARD2) ?$colorbacktabcard2:$conf->global->THEME_ELDY_BACKTABCARD2)  :(empty($user->conf->THEME_ELDY_BACKTABCARD2)?$colorbacktabcard2:$user->conf->THEME_ELDY_BACKTABCARD2);
$colorbacktabactive  =empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED)?(empty($conf->global->THEME_ELDY_BACKTABACTIVE)?$colorbacktabactive:$conf->global->THEME_ELDY_BACKTABACTIVE):(empty($user->conf->THEME_ELDY_BACKTABACTIVE)?$colorbacktabactive:$user->conf->THEME_ELDY_BACKTABACTIVE);
$colorbacklineimpair1=empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED)?(empty($conf->global->THEME_ELDY_LINEIMPAIR1)  ?$colorbacklineimpair1:$conf->global->THEME_ELDY_LINEIMPAIR1):(empty($user->conf->THEME_ELDY_LINEIMPAIR1)?$colorbacklineimpair1:$user->conf->THEME_ELDY_LINEIMPAIR1);
$colorbacklineimpair2=empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED)?(empty($conf->global->THEME_ELDY_LINEIMPAIR2)  ?$colorbacklineimpair2:$conf->global->THEME_ELDY_LINEIMPAIR2):(empty($user->conf->THEME_ELDY_LINEIMPAIR2)?$colorbacklineimpair2:$user->conf->THEME_ELDY_LINEIMPAIR2);
$colorbacklineimpairhover=empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED)?(empty($conf->global->THEME_ELDY_LINEIMPAIRHOVER)  ?$colorbacklineimpairhover:$conf->global->THEME_ELDY_LINEIMPAIRHOVER):(empty($user->conf->THEME_ELDY_LINEIMPAIRHOVER)?$colorbacklineimpairhover:$user->conf->THEME_ELDY_LINEIMPAIRHOVER);
$colorbacklinepair1  =empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED)?(empty($conf->global->THEME_ELDY_LINEPAIR1)    ?$colorbacklinepair1:$conf->global->THEME_ELDY_LINEPAIR1)    :(empty($user->conf->THEME_ELDY_LINEPAIR1)?$colorbacklinepair1:$user->conf->THEME_ELDY_LINEPAIR1);
$colorbacklinepair2  =empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED)?(empty($conf->global->THEME_ELDY_LINEPAIR2)    ?$colorbacklinepair2:$conf->global->THEME_ELDY_LINEPAIR2)    :(empty($user->conf->THEME_ELDY_LINEPAIR2)?$colorbacklinepair2:$user->conf->THEME_ELDY_LINEPAIR2);
$colorbacklinepairhover  =empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED)?(empty($conf->global->THEME_ELDY_LINEPAIRHOVER)    ?$colorbacklinepairhover:$conf->global->THEME_ELDY_LINEPAIRHOVER)    :(empty($user->conf->THEME_ELDY_LINEPAIRHOVER)?$colorbacklinepairhover:$user->conf->THEME_ELDY_LINEPAIRHOVER);
$colorbackbody       =empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED)?(empty($conf->global->THEME_ELDY_BACKBODY)     ?$colorbackbody:$conf->global->THEME_ELDY_BACKBODY)          :(empty($user->conf->THEME_ELDY_BACKBODY)?$colorbackbody:$user->conf->THEME_ELDY_BACKBODY);
$colortext           =empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED)?(empty($conf->global->THEME_ELDY_TEXT)         ?$colortext:$conf->global->THEME_ELDY_TEXT)                  :(empty($user->conf->THEME_ELDY_TEXT)?$colortext:$user->conf->THEME_ELDY_TEXT);
$fontsize            =empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED)?(empty($conf->global->THEME_ELDY_FONT_SIZE1)   ?$fontsize:$conf->global->THEME_ELDY_FONT_SIZE1)             :(empty($user->conf->THEME_ELDY_FONT_SIZE1)?$fontsize:$user->conf->THEME_ELDY_FONT_SIZE1);
$fontsizesmaller     =empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED)?(empty($conf->global->THEME_ELDY_FONT_SIZE2)   ?$fontsize:$conf->global->THEME_ELDY_FONT_SIZE2)             :(empty($user->conf->THEME_ELDY_FONT_SIZE2)?$fontsize:$user->conf->THEME_ELDY_FONT_SIZE2);
// No hover by default, we keep only if we set var THEME_ELDY_USE_HOVER
if ((! empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED) && empty($user->conf->THEME_ELDY_USE_HOVER))
    || (empty($user->conf->THEME_ELDY_ENABLE_PERSONALIZED) && empty($conf->global->THEME_ELDY_USE_HOVER)))
{
    $colorbacklineimpairhover='';
    $colorbacklinepairhover='';
}

// Set text color to black or white
$tmppart=explode(',',$colorbackhmenu1);
$tmpval=(! empty($tmppart[1]) ? $tmppart[1] : '')+(! empty($tmppart[2]) ? $tmppart[2] : '')+(! empty($tmppart[3]) ? $tmppart[3] : '');
if ($tmpval <= 360) $colortextbackhmenu='FFF';
else $colortextbackhmenu='444';
$tmppart=explode(',',$colorbackvmenu1);
$tmpval=(! empty($tmppart[1]) ? $tmppart[1] : '')+(! empty($tmppart[2]) ? $tmppart[2] : '')+(! empty($tmppart[3]) ? $tmppart[3] : '');
if ($tmpval <= 360) { $colortextbackvmenu='FFF'; }
else { $colortextbackvmenu='444'; }
$tmppart=explode(',',$colorbacktitle1);
$tmpval=(! empty($tmppart[1]) ? $tmppart[1] : '')+(! empty($tmppart[2]) ? $tmppart[2] : '')+(! empty($tmppart[3]) ? $tmppart[3] : '');
if ($tmpval <= 360) { $colortexttitle='FFF'; $colorshadowtitle='000'; }
else { $colortexttitle='444'; $colorshadowtitle='FFF'; }
$tmppart=explode(',',$colorbacktabcard1);
$tmpval=(! empty($tmppart[1]) ? $tmppart[1] : '')+(! empty($tmppart[2]) ? $tmppart[2] : '')+(! empty($tmppart[3]) ? $tmppart[3] : '');
if ($tmpval <= 340) { $colortextbacktab='FFF'; }
else { $colortextbacktab='444'; }


$usecss3=true;
if ($conf->browser->name == 'ie' && round($conf->browser->version,2) < 10) $usecss3=false;
elseif ($conf->browser->name == 'iceweasel') $usecss3=false;
elseif ($conf->browser->name == 'epiphany')  $usecss3=false;

foreach($conf->modules as $val) {
    $mainmenuused.=','.(isset($moduletomainmenu[$val])?$moduletomainmenu[$val]:$val);
}

$mainmenuusedarray=array_unique(explode(',',$mainmenuused));

$generic=1;
$divalreadydefined=array('home','companies','products','commercial','accountancy','project','tools','members','shop','agenda','holiday','bookmark','cashdesk','ecm','geoipmaxmind','gravatar','clicktodial','paypal','webservices','allscreens');

foreach($mainmenuusedarray as $val) {
    print "/* ------" . $val ."-------- */\n";
    if (empty($val) || in_array($val,$divalreadydefined)) continue;

    // Search img file in module dir
    $found=0; $url='';
    foreach($conf->file->dol_document_root as $dirroot) {
        if (file_exists($dirroot."/".$val."/img/".$val.".png")) {
            $url=dol_buildpath('/'.$val.'/img/'.$val.'.png', 1);
            $found=1;
            break;
        }
    }

    if (!$found) {
        $url=dol_buildpath($path.'/theme/'.$theme.'/img/menus/generic'.$generic.".png",1);
        $found=1;
        if ($generic < 4) $generic++;
        print "/* A mainmenu entry but img file ".$val.".png not found (check /".$val."/img/".$val.".png), so we use a generic one */\n";
    }

    if ($found) {
        print "div.mainmenu.".$val." {\n";
        print " background-image: url(".$url.");\n";
        print " height:1em;\n";
        print "}\n";
    }
}

if(extension_loaded('zlib')) ob_end_flush();
if (is_object($db)) $db->close();
?>
