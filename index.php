<?php

session_start();

function str_starts_with($haystack, $needle) {
	return (string)$needle !== '' && strncmp($haystack, $needle, strlen($needle)) === 0;
}

require_once('phpagi-asmanager.php');


$ini_array = parse_ini_file("config.php");

$myphone="";
$namefilter="";
$cidfilter="";
$cid="";
$name = "";
$sort = "name_asc";


if (isset($_GET['sort'])) {
	$sort = $_GET['sort'];
	$_SESSION['sort'] = $sort;
} else 
	if (isset($_SESSION['sort']))
		$sort = $_SESSION['sort'];

if (isset($_COOKIE['myphone'])) {
	$myphone = $_COOKIE['myphone'];
}

if (isset($_GET['cid'])) {
		$cid = $_GET['cid'];
		$_SESSION['cid'] = $cid;
	}
else
if (isset($_SESSION['cid'])) {
	$cid = $_SESSION['cid'];
	/*unset($_SESSION['cid']);*/
}
	


if (isset($_GET['namefilter'])) {
		$namefilter = $_GET['namefilter'];
		$_SESSION['namefilter'] = $namefilter;
} else 
	if (isset($_SESSION['namefilter'])) {
		$namefilter = $_SESSION['namefilter'];
	
} 
$namefilter = strtolower($namefilter);

if (isset($_GET['cidfilter'])) {
		$cidfilter = $_GET['cidfilter'];
		$_SESSION['cidfilter'] = $cidfilter;
} else 
	if (isset($_SESSION['cidfilter'])) {
		$cidfilter = $_SESSION['cidfilter'];
	
} 
$cidfilter = strtolower($cidfilter);


$opts_config = array("server" => "$ini_array[manager_ip]", "port" => "$ini_array[manager_port]", "username" => "$ini_array[manager_username]","secret" => "$ini_array[manager_secret]","log_level" => "0") ;

$asm = new AGI_AsteriskManager(NULL,$opts_config);

if($asm->connect())
  {

	/* Get Numbers + Names */
 	$peer = $asm->command("database show cidname");
	$asm->disconnect();

	
	$numbers = array();
	$filtered = array();
	
	
	$names = array();
	/* $line = explode("\n", $peer['data']); */
	
	
	foreach(explode("\n", $peer['data']) as $line)
	{
		$a = strpos('z'.$line, ':') - 1;
		if($a >= 0) {
			$numbers[trim(substr($line, 9, $a-9))] = trim(substr($line, $a + 1));
		}	
	}
	unset($line); 
 
	if ($cidfilter) {
		$nf = str_replace("*","",$cidfilter);
		$nf = strtolower($nf);
		foreach($numbers as $key => $value) {    /* suchen in allem */
			$low = strtolower($key);
			$a = strpos('z'.$low,$nf) - 1;
			if ($a  >= 0) {
				$filtered[$key] = $value;
			}
		}
		unset($value);		
	} else {
	
		if (strpos('z'.$namefilter,"*") > 0) {       /* suchem vom Anfang */
			$nf = str_replace("*","",$namefilter);
			$nf = strtolower($nf);
                        $nf = htmlentities($nf);
			foreach($numbers as $key => $value) {
				$low = strtolower($value);
				if (str_starts_with($low,$nf)) {
					$filtered[$key] = $value;
				}
			}
		} else {
			foreach($numbers as $key => $value) {    /* suchen in allem */
				$low = strtolower($value);
				$nf = strtolower($namefilter);
                                $nf = htmlentities($nf);
				$a = strpos('z'.$low,$nf) - 1;
				if ($a  >= 0) {
					$filtered[$key] = $value;
				}
			}
		}	
		unset($value);
	}

	if ($cid) {
		$name = $numbers[$cid];
	}

	/* Sortiere */
	switch($sort) {
	case 'name_asc':
		asort($filtered);
		$sortnext="desc";
		break;
	case 'name_desc':
		arsort($filtered);
		$sortnext="asc";
		break;
	case 'cid_asc':
		ksort($filtered,SORT_STRING | SORT_FLAG_CASE);
		$sortnext="desc";
		break;
	case 'cid_desc':
		krsort($filtered,SORT_STRING | SORT_FLAG_CASE);
		$sortnext="asc";
		break;
	default:
		$sortnext="asc";
		break;
	}	
	
}
?>



<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html class=" cbmmujdg idc0_341">

<head>
	<meta content="text/html; charset=UTF-8" http-equiv="content-type">

	<link rel="stylesheet" type="text/css" href="mstyle.css">

	<script type="text/javascript">

		RefreshTimer = 0;
		SubmitPrompt = '';
		reloadFlag = '';


		// defines what command will be submitted upon Enter...
		// This is set according to cursor position
		function SetSubmit(cmd) {
			SubmitPrompt = '';
			document.getElementById('submitcmd').value = cmd;
		}


		// Programmed submit request from JavaScript...
		// Mimics the behaviour of clicking Enter
		function SubmitReq(cmd) {
			document.getElementById('submitcmd').value = cmd;
			if (Validate()) {
				document.getElementById('form').submit();
			}
		}


		// All programmed or explicit submit requests must first pass this validation function
		// submitcmd field contains command to be executed
		function Validate() {
			var ok = true;
			cmd = document.getElementById('submitcmd').value;
			// Process hight priority commands first...
			switch (cmd) {

				case 'logout_admin':
					ok = true;
					break;

				case 'login_admin':
					ok = PromptPW('Bitte das Admin-Passwort eingeben:');
					break;

				case 'set_myphone':
					ok = PromptUsrPW('Bitte das Voicemail-Passwort eingeben:');
					break;

				case 'vmok1':
					ok = PromptUsrPW('Bitte das Voicemail-Passwort eingeben:');
					break;

				case 'vmok2':
					ok = PromptUsrPW('Bitte das Voicemail-Passwort eingeben:');
					break;

				case 'vmok3':
					ok = PromptUsrPW('Bitte das Voicemail-Passwort eingeben:');
					break;

				default:
					// Low priority command handling follows...
					if (document.getElementById('myphone')) {
						// If page contains myphone field and it was manually changed , try to attach to new extension
						mpc = readCookie('myphone');
						mpf = document.getElementById('myphone').value;
						// Should Myphone be changed?
						if (((mpc == null) && (mpf != '')) || ((mpc != null) && (mpc != mpf))) {
							document.getElementById('submitcmd').value = 'set_myphone';		// overwrite any previous cmd
							ok = PromptUsrPW('Bitte das Voicemail-Passwort eingeben:');
						}
					}
			}
			return (ok);		// false will cancel submit!!
		}


		// Asks for password unconditionally
		function PromptPW(msg) {
			var ok = false;
			document.getElementById('submit_pw').value = '';
			var pw = prompt(msg, '');
			if (pw != null) {
				document.getElementById('submit_pw').value = pw;
				ok = true;
			}
			return (ok);
		}

		// Asks for password if authentication is requred
		function PromptUsrPW(msg) {
			var ok = false;
			if (readCookie('authreq') != 1) ok = true;
			else {
				document.getElementById('submit_pw').value = '';
				var pw = prompt(msg, '');
				if (pw != null) {
					document.getElementById('submit_pw').value = pw;
					ok = true;
				}
			}
			return (ok);
		}


		function SubmitConfirm(msg, cmd) {
			if (!confirm(msg)) return;
			else {
				document.getElementById('submitcmd').value = cmd;
				document.getElementById('form').submit();
			}
		}

		// OnChange event if CID has been manually edited / pasted into
		// and looks up name by realoading
		// This also ensures that upon pasting a number any previous name will be cleared
		function CidChange() {
			if (reloadFlag == '') {			// avoid reloading twice (from onChange and onClick)
				reloadFlag = 'reloading';
				document.getElementById('submitcmd').value = 'cidchange';
				document.getElementById('form').submit();
			}
		}

		function resetRefreshTimer() {
			var ri = 360;
			if (ri > 0) {
				clearTimeout(RefreshTimer);
				RefreshTimer = window.setTimeout("refresh()", ri * 1000);
			}
		}

		function stopRefreshTimer() {
			clearTimeout(RefreshTimer);
		}

		function refresh() {
			window.location.reload(false);
		}

		// stops page refresh during prompt
		function safe_prompt(pmsg) {
			stopRefreshTimer();
			ret = prompt(pmsg);
			resetRefreshTimer();
			return ret;
		}

		function showdialer(url) {
			newwindow = window.open(url, 'dialer', 'height=100,width=260,location=0,status=0,menubar=0,toolbar=0,titlebar=0');
			if (window.focus) { newwindow.focus() }
			return false;
		}

		function showphonebook() {
			var num = document.getElementById('cid').value;		// catch last second local changes
			newwindow = window.open('index.php?cid=' + num, 'phonebook');
			if (window.focus) { newwindow.focus() }
			return false;
		}

		function readCookie(name) {
			var nameEQ = name + "=";
			var ca = document.cookie.split(';');
			for (var i = 0; i < ca.length; i++) {
				var c = ca[i];
				while (c.charAt(0) == ' ') c = c.substring(1, c.length);
				if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
			}
			return null;
		}

	</script>

	<script type="text/javascript">
		window.name = 'phonebook';
	</script>
	<title>Jojo's Telefonbuch für Asterisk</title>
</head>

<body onload="resetRefreshTimer()">
	<table class="normaltext" width="100%" cellspacing="0" cellpadding="0">
		<tbody>
			<tr>
				<td>
					<table cellspacing="0" cellpadding="0" align="center">
						<tbody>
							<tr>
								<td>
									<form id="form" method="POST" action="submit.php" onsubmit="return(Validate())"
										accept-charset="UTF-8" enctype="multipart/form-data">
										<table width="100%" cellspacing="0" cellpadding="0">
											<tbody>
												<tr>
													<td valign="top">
														<table width="100%" cellspacing="0" cellpadding="0">
															<tbody>
																<tr>
																	<td rowspan="2" valign="middle">
																		<h1>Jojo's Telefonbuch</h1>
																	</td>
																	<td style="text-align:right;" valign="top"><input
																			type="image" name="enter"
																			src="images/pixel.gif"><input
																			type="hidden" name="submitcmd"
																			id="submitcmd" value=""><input type="hidden"
																			name="submit_pw" id="submit_pw"
																			value="">&nbsp;&nbsp;
																	</td>
																</tr>
																<tr>
																	<td>
																		<div id="err" class="msgline">&nbsp;</div>
																	</td>
																</tr>
															</tbody>
														</table>
														<script type="text/javascript">
															if (document.cookie == '')
																document.getElementById('err').innerHTML = '<span class="errtext">You must configure your browser to accept cookies, or phone book will stop working!</span>'
														</script>
													</td>
												</tr>
												<tr>
													<td colspan="2" valign="top">
														<table cellspacing="0" cellpadding="0">
															<tbody>
																<tr>
																	<td valign="top">
																		<table class="colbg" width="193px"
																			cellspacing="0" cellpadding="0">
																			<tbody>
																				<tr>
																					<td colspan="3" class="table2head"
																						align="left">Name suchen</td>
																				</tr>
																				<tr>
																					<td class="coltext"><input
																							type="text"
																							name="namefilter"
																							id="namefilter"
<?php
 print_r("value=\"$namefilter\"");
?>
																							size="16" maxlength="30"
																							class="active_bg"
																							onmouseover="resetRefreshTimer()"
																							onfocus="SetSubmit('searchname')">
																					</td>
																					<td class="coltext"><a href="#"
																							onclick="SubmitReq('searchname')"><img
																								src="images/lupe88a_22.gif"
																								class="vam"
																								title="Neme suchen"
																								alt="Neme suchen"></a>
																					</td>
																					<td class="coltext" width="100%"><a
																							href="#"
																							onclick="SubmitReq('cancelsearch')"><img
																								src="images/x_22.gif"
																								class="vam"
																								title="Filter Löschen"
																								alt="Filter Löschen"></a>
																					</td>
																				</tr>
																			</tbody>
																		</table>
																	</td>
																	<td style="padding-left:6px;" valign="top">
																		<table class="colbg" width="156px"
																			cellspacing="0" cellpadding="0">
																			<tbody>
																				<tr>
																					<td colspan="3" class="table2head"
																						align="left">Nummer suchen</td>
																				</tr>
																				<tr>
																					<td class="coltext"><input
																							type="text" name="cidfilter"
																							id="cidfilter" size="10"
<?php
 print_r("value=\"$cidfilter\"");
?>
																							maxlength="30"
																							onmouseover="resetRefreshTimer()"
																							onfocus="SetSubmit('searchcid')">
																					</td>
																					<td class="coltext"><a href="#"
																							onclick="SubmitReq('searchcid')"><img
																								src="images/lupe88a_22.gif"
																								class="vam"
																								title="Nummer suchen"
																								alt="Nummer suchen"></a>
																					</td>
																					<td class="coltext" width="100%">
																						<img class="vam"
																							src="images/pixel.gif"
																							alt="" width="22px"></td>
																				</tr>
																			</tbody>
																		</table>
																	</td>
																	<td style="padding-left:6px;" rowspan="2"
																		valign="top">
																		<table class="colbg" cellspacing="0"
																			cellpadding="0">
																			<tbody>
																				<tr>
																					<td class="table2head" colspan="2"
																						align="left"><span>Buch</span>
																					</td>
																				</tr>
																				<tr>
																					<td class="coltext"><a href="#"
																							onclick="SubmitReq('save')"><img
																								src="images/plus_22.gif"
																								class="vam"
																								title="Speichern / Ändern"
																								alt="Speichern / Ändern"
																								style="padding-left:2px;"></a>
																					</td>
																					<td class="coltext"><a href="#"
																							onclick="SubmitReq('delete')"><img
																								src="images/minus_22.gif"
																								class="vam"
																								alt="Adressbucheintrag Löschen"
																								title="Adressbucheintrag Löschen"
																								style="padding-right:2px;"></a>
																					</td>
																				</tr>
																				<tr>
																					<td class="coltext_cont">&nbsp;</td>
																				</tr>
																			</tbody>
																		</table>
																	</td>
																	<td style="padding-left:6px;" rowspan="2"
																		valign="top">
																		<table class="colbg" cellspacing="0"
																			cellpadding="0">
																			<tbody>
																				<tr>
																					<td class="table2head" align="left">
																						Name</td>
																				</tr>
																				<tr>
																					<td class="coltext"><input
																							type="text" name="name"
																							id="name" size="23"
	<?php
 print_r("value=\"$name\"");
?>
																							maxlength="50"
																							onmouseover="resetRefreshTimer()"
																							onfocus="SetSubmit('hangup')">
																					</td>
																				</tr>
																				<tr>
																					<td class="coltext_cont">&nbsp;</td>
																				</tr>
																			</tbody>
																		</table>
																	</td>
																	<td style="padding-left:6px;" rowspan="2"
																		valign="top">
																		<table class="colbg" cellspacing="0"
																			cellpadding="0">
																			<tbody>
																				<tr>
																					<td class="table2head" colspan="2"
																						align="left">Nummer</td>
																				</tr>
																				<tr>
																					<td class="coltext" colspan="2">
																						<input type="text" name="cid"
																							id="cid" size="17"
<?php
 print_r("value=\"$cid\"");
?>
																							maxlength="30"
																							onmouseover="resetRefreshTimer()"
																							onchange="CidChange()"
																							onfocus="SetSubmit('dial')">
																					</td>
																				</tr>
																				<tr>
																					<td class="coltext_cont">&nbsp;</td>
																					<td class="coltext_cont"
																						align="right"><a href="#"
																							onclick="CidChange()"
																							title="Bereitet Nummer zum Wählen vor und sucht ggf. Namen heraus">Check</a>
																					</td>
																				</tr>
																			</tbody>
																		</table>
																	</td>
																	<td style="padding-left:6px;" rowspan="2"
																		valign="top">
																		<table class="colbg" cellspacing="0"
																			cellpadding="0">
																			<tbody>
																				<tr>
																					<td class="table2head" align="left">
																						Ext</td>
																				</tr>
																				<tr>
																					<td class="coltext"><input
																							type="text" name="myphone"
																							id="myphone" size="1"
																							maxlength="4"
<?php
 print_r("value=\"$myphone\"");
?>
																							onmouseover="resetRefreshTimer()"
																							style="margin-right:3px; background-color:#E4FFDF;"
																							onchange="SetSubmit('set_myphone')">
																					</td>
																				</tr>
																				<tr>
																					<td class="coltext_cont">&nbsp;</td>
																				</tr>
																			</tbody>
																		</table>
																	</td>
																	<td style="padding-left:6px;" rowspan="2"
																		valign="top">
																		<table class="colbg" cellspacing="0"
																			cellpadding="0">
																			<tbody>
																				<tr>
																					<td class="table2head" colspan="2"
																						align="left">Wählen</td>
																				</tr>
																				<tr>
																					<td class="coltext"><a href="#"
																							onclick="SubmitReq('dial')"><img
																								src="images/telefon_25.gif"
																								style="margin-top:-3px"
																								title="Wählen"
																								alt="Wählen"></a></td>
																					<td class="coltext"><a href="#"
																							onclick="SubmitReq('hangup')"><img
																								src="images/x_22.gif"
																								style="margin-right:2px;"
																								title="Auflegen"
																								alt="Auflegen"></a></td>
																				</tr>
																				<tr>
																					<td class="coltext_cont">&nbsp;</td>
																				</tr>
																			</tbody>
																		</table>
																	</td>
																</tr>
																<tr>
																	<td colspan="2"
																		style="padding-top:3px; padding-bottom:4px;">
																		&nbsp;
<?php
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=a*\">A </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=b*\">B </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=c*\">C </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=d*\">D </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=e*\">E </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=f*\">F </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=g*\">G </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=h*\">H </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=i*\">I </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=j*\">J </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=k*\">K </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=l*\">L </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=m*\">M </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=n*\">N </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=o*\">O </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=p*\">P </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=q*\">Q </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=r*\">R </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=s*\">S </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=t*\">T </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=u*\">U </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=v*\">V </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=w*\">W </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=x*\">X </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=y*\">Y </a>");
	print_r("<a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?namefilter=z*\">Z </a>");

?>
																	</td>
																</tr>
																<tr>
																	<td class="table2head" align="left"><a
																			<?php print_r("href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?sort=name_".$sortnext."\"") ?>
																			title="Namen in der Asterisk-Datenbank"><span
																				class="menu_selected">Name</span></a>
																	</td>
																	<td class="table2head" style="padding-left:11px;"
																		align="left"><a
																		<?php print_r("href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?sort=cid_".$sortnext."\"") ?>
																			title="Nummern in der Asterisk-Datenbank">Nummer</a>
																	</td>
																	
																</tr>
																<tr>
																	<td colspan="2" valign="top">
																		<table class="colbg" width="100%"
																			cellspacing="0" cellpadding="0">
																			<tbody>
<?php
	foreach($filtered as $key => $value) {
		print_r("<tr><td class=\"pl\" width=\"192px\">{$value}</td>");
		print_r("<td class=\"pl\"><a href=\"http://" . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'] . "?cid=$key\">{$key}</a></td></tr>");
	}	
	unset($value);
?>
																				
																				
																			</tbody>
																		</table>
																	</td>
																</tr>
															</tbody>
														</table>
													</td>
												</tr>
											</tbody>
										</table>
									</form>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>
</body>

</html>
