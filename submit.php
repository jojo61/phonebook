<?php

session_start();
require_once('phpagi-asmanager.php');

$ini_array = parse_ini_file("config.php");
$opts_config = array("server" => "$ini_array[manager_ip]", "port" => "$ini_array[manager_port]", "username" => "$ini_array[manager_username]","secret" => "$ini_array[manager_secret]","log_level" => 0) ;
$url = 'http://' . $_SERVER['HTTP_HOST'];            // Get the server
$url .= rtrim(dirname($_SERVER['PHP_SELF']), '/\\'); // Get the current directory
$url .= '/index.php';  

switch($_POST['submitcmd']){
case 'searchname':
    $_SESSION['namefilter']=$_POST['namefilter'];
    if ($_SESSION['cidfilter'])
        unset($_SESSION['cidfilter']);
    header("Location:" . $url ,true, 302);
    exit();
    break;

case 'searchcid':
    $_SESSION['cidfilter']=$_POST['cidfilter'];
    if ($_SESSION['namefilter'])
        unset($_SESSION['namefilter']);
    header("Location:" . $url ,true, 302);
    exit();
    break;

case 'cidchange':
    $_SESSION['cid']=$_POST['cid'];
    header("Location:" . $url ,true, 302);
    exit();
    break;

case 'cancelsearch':
    if ($_SESSION['cidfilter'])
        unset($_SESSION['cidfilter']);
    if ($_SESSION['namefilter'])
        unset($_SESSION['namefilter']);
    header("Location:" . $url ,true, 302);
    exit();
    break;

case 'set_myphone':
    setcookie("myphone", $_POST['myphone']);
    header("Location:" . $url ,true, 302);
    exit();
    break;

case 'dial':
    $channel = "Local/".$_POST['myphone']."@".$ini_array['dial_context'];
    $context = $ini_array['dial_context'];
    $timeout = $ini_array['dial_timeout'];
    $variable  = 'SIPADDHEADER=Call-Info: <sip:nosip>\;answer-after=0';
    
    if (isset($ini_array['dial_variables']))
        $variable = 'SIPADDHEADER='.$ini_array['dial_variables'];
    
    $asm = new AGI_AsteriskManager(NULL,$opts_config);

    if($asm->connect()) {
        $args = array("Channel" => $channel, "Context" => $context, "Priority" => 1, "Timeout" => $timeout, "Variable" => $variable, "CallerID" => $_POST['myphone'], "Exten" => $_POST['cid'],"Async" => true,"ActionID" => "testvar" );
        $call = $asm->Originate($channel,$_POST['cid'],$context,1,NULL,NULL,$timeout,$_POST['myphone'],$variable,NULL,true,"testvar");
        /*$call = $asm->Originate($args);*/
        $asm->disconnect();
        
    }
    unset($asm);
    header("Location:" . $url ,true, 302);
    exit();
    break;

case 'hangup':
    $asm1 = new AGI_AsteriskManager(NULL,$opts_config);
    if($asm1->connect()) {
        $hangup = $asm1->command("core show channels concise");
        $local = "Local/".$_POST['myphone'];
        $sipcall = $ini_array['dial_channel_prefix'].$_POST['myphone'];

        foreach(explode("\n", $hangup['data']) as $line)
        {
            $a = strpos('z'.$line, '!') - 1;
            if($a >= 0) {
                $channel = substr($line, 0, $a);
                if (!strncmp($channel,$local,strlen($local)) || !strncmp($channel,$sipcall,strlen($sipcall))) {
                    $asm1->Hangup($channel);
                }

            }	
        }
        unset($line); 

        $asm1->disconnect();
    }
    unset($asm1);
    header("Location:" . $url ,true, 302);
    exit();
    break;

case 'save':
    if ($_POST['name'] != "") {
        $asm1 = new AGI_AsteriskManager(NULL,$opts_config);
        if($asm1->connect()) {
            $save1 = $asm1->command("database put cidname ".$_POST['cid']." ".$_POST['name']);
            $asm1->disconnect();
        }
        unset($asm1);
    }
    header("Location:" . $url ,true, 302);
    exit();
    break;

case 'delete':
    if ($_POST['cid'] != "") {
        $asm1 = new AGI_AsteriskManager(NULL,$opts_config);
        if($asm1->connect()) {
            $save = $asm1->command("database del cidname ".$_POST['cid']);
            $asm1->disconnect();
        }
        unset($asm1);
    }
    header("Location:" . $url ,true, 302);
    exit();
    break;

}


?>

<html>
<body>
<?php

/* Notausstieg zum testen */


foreach($_POST as $key => $value) {
    print_r("$key => $value <br>");
    
}
unset($value);

?>

</body>
</html> 