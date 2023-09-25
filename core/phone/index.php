<?php
require_once "root.php";
require_once "resources/require.php";
require_once "resources/check_auth.php";
require_once "resources/paging.php";

//redirect admin to app instead
if (file_exists($_SERVER["PROJECT_ROOT"] . "/app/domains/app_config.php") && !permission_exists('domain_all')) {
    header("Location: " . PROJECT_PATH . "/app/domains/domains.php");
}

//check permission
if (permission_exists('voicemail_greeting_view') || permission_exists('xml_cdr_view')) {
    //access granted
} else {
    echo "access denied";
    exit;
}


//add multi-lingual support
$language = new text;
$text = $language->get();

//change the domain
if (is_uuid($_GET["domain_uuid"]) && $_GET["domain_change"] == "true") {
    if (permission_exists('domain_select')) {
        //get the domain_uuid
        $sql = "select * from v_domains ";
        // $sql .= "order by domain_name asc ";
        $database = new database;
        $result = $database->select($sql, null, 'all');
        if (is_array($result) && sizeof($result) != 0) {
            foreach ($result as $row) {
                if (count($result) == 0) {
                    $_SESSION["domain_uuid"] = $row["domain_uuid"];
                    $_SESSION["domain_name"] = $row['domain_name'];
                } else {
                    if ($row['domain_name'] == $domain_array[0] || $row['domain_name'] == 'www.' . $domain_array[0]) {
                        $_SESSION["domain_uuid"] = $row["domain_uuid"];
                        $_SESSION["domain_name"] = $row['domain_name'];
                    }
                }
            }
        }
        unset($sql, $result);

        //update the domain session variables
        $domain_uuid = $_GET["domain_uuid"];
        $_SESSION['domain_uuid'] = $domain_uuid;
        $_SESSION["domain_name"] = $_SESSION['domains'][$domain_uuid]['domain_name'];
        $_SESSION['domain']['template']['name'] = $_SESSION['domains'][$domain_uuid]['template_name'];

        //clear the extension array so that it is regenerated for the selected domain
        unset($_SESSION['extension_array']);

        //set the setting arrays
        $domain = new domains();
        $domain->db = $db;
        $domain->set();

        //redirect the user
        if ($_SESSION["login"]["destination"] != '') {
            // to default, or domain specific, login destination
            header("Location: " . PROJECT_PATH . $_SESSION["login"]["destination"]["url"]);
        } else {
            header("Location: " . PROJECT_PATH . "/core/user_settings/user_dashboard.php");
        }
        exit;
    }
}

//redirect the user
if (file_exists($_SERVER["DOCUMENT_ROOT"] . "/app/domains/domains.php")) {
    $href = '/app/domains/domains.php';
}

//includes
echo "<style> .card{height:77vh;} </style>";
require_once "resources/header.php";
$document['title'] = "Phone";
require_once "resources/paging.php";

//get the http values and set them as variables
$search = $_GET["search"];
$order_by = $_GET["order_by"] != '' ? $_GET["order_by"] : 'domain_name';
$order = $_GET["order"];

 $sql = "SELECT extension_uuid FROM v_extension_users WHERE domain_uuid = '" . $domain_uuid . "' AND user_uuid = '".$_SESSION['user_uuid']."'";

$database = new database;
$extension_uuid = $database->select($sql, null, 'column');
unset($sql);

 $sql = "SELECT extension, password FROM v_extensions WHERE extension_uuid = '".$extension_uuid."'";
$database = new database;
$row = $database->select($sql, null, 'all');
 $extension = $row[0]['extension'];
 $password = $row[0]['password'];
unset($sql);

$sql = "SELECT contact_name from view_users where domain_name = '$_SESSION[domain_name]' AND username = '$_SESSION[username]'";
$database = new database;
$contactName = $database->select($sql, null, 'column');
 if($contactName == ""){
    $contactName = $extension;
 }
unset($sql);



//get the domains

$c = 0;
$row_style["0"] = "row_style0";
$row_style["1"] = "row_style1";

//show the header and the search


echo "<iframe src='https://$_SESSION[domain_name]/Browser-Phone/Phone/index.php?server=$_SESSION[domain_name]&extension=$extension&password=$password&fullname=$contactName' width='100%' height='100%' frameborder='none'></iframe>";
echo "<br /><br />";

//include the footer
require_once "resources/footer.php";
?>
