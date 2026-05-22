<?php

require_once( "db.php" );
require_once( "lib-mail-v2.php" );

function getBrowser() {
    $userBrowser = '';
    $userAgent = $_SERVER['HTTP_USER_AGENT'];

    if (preg_match('/Trident/i', $userAgent)) {
        $userBrowser = "Internet Explorer";
    } elseif (preg_match('/MSIE/i', $userAgent)) {
        $userBrowser = "Internet Explorer";
    } elseif (preg_match('/Firefox/i', $userAgent)) {
        $userBrowser = "Mozilla Firefox";
    } elseif (preg_match('/Safari/i', $userAgent)) {
        $userBrowser = "Apple Safari";
    } elseif (preg_match('/Chrome/i', $userAgent)) {
        $userBrowser = "Google Chrome";
    } elseif (preg_match('/Flock/i', $userAgent)) {
        $userBrowser = "Flock";
    } elseif (preg_match('/Opera/i', $userAgent)) {
        $userBrowser = "Opera";
    } elseif (preg_match('/Netscape/i', $userAgent)) {
        $userBrowser = "Netscape";
    }

    if (preg_match('/Mobile/i', $userAgent)) {
        $userBrowser = "Mobile Device";
    }
    return $userBrowser;
}

function getBaseUrl(){
    $name = webAppName();
    $serverName = filter_input( INPUT_SERVER, 'SERVER_NAME', FILTER_UNSAFE_RAW, $flags);
    $serverPort = 80;
    $baseUrl = "http://" . $serverName . ":" . $serverPort;
    $baseUrl = $baseUrl . $name;
    return $baseUrl;
}

/**
 * Checks if a specific user has a given role.
 * 
 * @param mysqli $conn     The active database connection variable.
 * @param int    $userId   The ID of the user to check.
 * @param string $roleName The friendly name of the role (e.g., 'manager', 'user').
 * @return bool True if the user has the role, false otherwise.
 */
function userHasRole($conn, $userId, $roleName): Bool {
    // Backticks ` are required because your table names contain hyphens (-)
    $sql = "SELECT COUNT(*) 
            FROM `auth-permissions` p
            JOIN `auth-roles` r ON p.idRole = r.idRole
            WHERE p.idUser = ? AND r.friendlyName = ?";

    // Prepare the SQL statement
    $stmt = mysqli_prepare($conn, $sql);
    
    if ($stmt) {
        // "is" means: 1st parameter is an Integer ($userId), 2nd is a String ($roleName)
        mysqli_stmt_bind_param($stmt, "is", $userId, $roleName);
        
        // Run the query
        mysqli_stmt_execute($stmt);
        
        // Bind the result count to a variable
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        
        // Close the statement
        mysqli_stmt_close($stmt);
        
        // If the count is greater than 0, they have the role!
        return $count > 0;
    }
    
    return false;
}

function redirectToPage($url, $title, $message, $refreshTime = 5) {
    echo "<html>\n";
    echo "  <head>\n";
    echo "    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>\n";
    echo "    <meta http-equiv=\"REFRESH\" content=\"$refreshTime;url=$url\">\n";
    echo "    <title>$title</title>\n";
    echo "  </head>\n";
    echo "  <body>\n";
    echo "    <p>$message</p>";
    echo "    <p>You will be redirect in $refreshTime seconds.</p>";
    echo "  </body>\n";
    echo "</html>";
    die();
}

$DefaultRedirectMessage = <<<EOD
    <p>Invalid data!</p>
    <p>Please fill all the requiered fields (marked with *).</p>
EOD;

function redirectToLastPage($title, $message = NULL, $refreshTime = 5) {
    $referer = filter_input( INPUT_SERVER, 'HTTP_REFERER', FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);

    echo "<html>\n";
    echo "  <head>\n";
    echo "    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>\n";
    echo "    <meta http-equiv=\"REFRESH\" content=\"$refreshTime;url=$referer\">\n";
    echo "    <title>$title</title>\n";
    echo "    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n";
    echo "    <link REL=\"stylesheet\" TYPE=\"text/css\" href=\"../Styles/GlobalStyle.css\">\n";
    echo "  </head>\n";
    echo "  <body>\n";
    if ( $message != NULL ) {
        echo $message;
    }
    else {
        echo $GLOBALS['DefaultRedirectMessage'];
    }
    echo "    <p>You will be redirect to the last page in $refreshTime seconds.\n";
    echo "  </body>\n";
    echo "</html>";
    die();
}

$find;
$replace;

function convertToEntities($str) {
    global $find;
    global $replace;

    if (($find == NULL) || ($replace == NULL)) {
        $find = array();
        $replace = array();

        foreach (get_html_translation_table(HTML_ENTITIES, ENT_QUOTES) as $key => $value) {
            $find[] = $key;
            $replace[] = $value;
        }
    }

    return str_replace($find, $replace, $str);
}

function webAppName() {
    $uri = explode("/", $_SERVER['REQUEST_URI']);
    $n = count($uri);
    $webApp = "";
    for ($idx = 0; $idx < $n - 1; $idx++) {
        $webApp .= ($uri[$idx] . "/" );
    }

    return $webApp;
}

function prepareHeaders() {
    list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
}

function ensureAuth($redirectPage) {
    prepareHeaders();

    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
        header("Location: $redirectPage");
        exit;
    }
}

function showAuth($authType, $realm, $message) {
    header("WWW-Authenticate: $authType realm=\"$realm\"");
    header("HTTP/1.0 401 Unauthorized");

    echo $message;
}

function isValid($userName, $password, $authType) {
    $userOk = -1;

    dbConnect(ConfigFile);
    
    $dataBaseName = $GLOBALS['configDataBase']->db;

    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName );

    $query = 
            "SELECT * FROM `$dataBaseName`.`auth-$authType` " .
            "WHERE `name`='$userName' AND `password`='$password' AND `active`='1'";
    $result = mysqli_query($GLOBALS['ligacao'], $query);

    if ( $result!=false ) {
        $userData = mysqli_fetch_array($result);
        $userOk = $userData['idUser'];
    }
    mysqli_free_result($result);

    dbDisconnect();

    return $userOk;
}

function existUserField($field, $value, $authType = "basic") {
    $exists = true;

    dbConnect(ConfigFile);
    
    $dataBaseName = $GLOBALS['configDataBase']->db;

    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName );

    $query = "SELECT * FROM `$dataBaseName`.`auth-$authType` " .
            "WHERE `$field`='$value'";
    $result = mysqli_query($GLOBALS['ligacao'], $query);

    if ( $result==false || mysqli_num_rows($result)==0 ) {
        $exists = false;
    }

    mysqli_free_result($result);

    dbDisconnect();

    return $exists;
}

function getRole($userId) {
    $userRoles = "";

    dbConnect(ConfigFile);
    
    $dataBaseName = $GLOBALS['configDataBase']->db;

    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName );

    $query = "SELECT `friendlyName` " .
            "FROM `$dataBaseName`.`auth-basic` u " .
            "JOIN `$dataBaseName`.`auth-permissions` p ON u.`idUser`=p.`idUser` " .
            "JOIN `$dataBaseName`.`auth-roles` r on p.`idRole`=r.`idRole` WHERE u.`active`=1 AND u.`idUser`='$userId'";

    $result = mysqli_query( $GLOBALS['ligacao'], $query );

    $isFirst = true;
    $userRoles .= "[";

    while ($userData = mysqli_fetch_array($result)) {
        if ($isFirst == true) {
            $isFirst = false;
        } else {
            $userRoles .= ", ";
        }

        $userRoles .= $userData['friendlyName'];
    }
    $userRoles .= "]";

    mysqli_free_result($result);

    dbDisconnect();

    return $userRoles;
}

function getEmail($idUser, $authType) {
    $userEmail = -1;

    dbConnect(ConfigFile);
    
    $dataBaseName = $GLOBALS['configDataBase']->db;

    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName );

    $query = "SELECT `email` FROM `$dataBaseName`.`auth-$authType` WHERE `idUser`='$idUser'";

    $result = mysqli_query($GLOBALS['ligacao'], $query);

    if ( $result!=false ) {
        $userData = mysqli_fetch_array($result);
        $userEmail = $userData['email'];
    }
    mysqli_free_result($result);

    dbDisconnect();

    return $userEmail;
}

function logout($authType, $realm, $location) {
    unset($_SERVER['PHP_AUTH_USER']);
    unset($_SERVER['PHP_AUTH_PW']);
    unset($_SERVER['HTTP_AUTHORIZATION']);

    header("WWW-Authenticate: $authType realm=\"$realm\"");
    header("HTTP/1.0 401 Unauthorized");

    header("Location: $location");
}

function getFileDetails($ids) {
    $isFirst = true;
    $whereClause = "";

    if (is_array($ids)) {
        foreach ($ids as $id) {
            if ($isFirst == false) {
                $whereClause .= " OR `id`='$id'";
            } else {
                $whereClause .= "`id`='$id'";
                $isFirst = false;
            }
        }
    } else {
        $whereClause = "`id`='$ids'";
    }

    dbConnect(ConfigFile);
    
    $dataBaseName = $GLOBALS['configDataBase']->db;

    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName );

    $query = "SELECT * FROM `$dataBaseName`.`images-details` WHERE " . $whereClause;

    $result = mysqli_query($GLOBALS['ligacao'], $query);

    $fileData = array();
    while (($fileDataRecord = mysqli_fetch_array($result)) != false) {
        $fileData[] = $fileDataRecord;
    }

    mysqli_free_result($result);
    dbDisconnect();

    if ( !is_array($ids)) {
        return $fileData[0];
    } else {
        return $fileData;
    }
}

function getConfiguration() {
    dbConnect(ConfigFile);

    $dataBaseName = $GLOBALS['configDataBase']->db;

    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName );

    $query = "SELECT * FROM `$dataBaseName`.`images-config`";

    $result = mysqli_query($GLOBALS['ligacao'], $query);

    $configuration = mysqli_fetch_array($result);

    mysqli_free_result($result);

    dbDisconnect();

    return $configuration;
}

function getStats() {
    dbConnect(ConfigFile);

    $dataBaseName = $GLOBALS['configDataBase']->db;

    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName );

    "SELECT COUNT(DISTINCT `mimeFileName`) FROM `$dataBaseName`.`images-details`;";
    "SELECT DISTINCT `mimeFileName` FROM `$dataBaseName`.`images-details`;";

    $queryTotal = "SELECT count(*) AS totalFiles FROM `$dataBaseName`.`images-details`";
    $queryImages = "SELECT count(*) AS totalImages FROM `$dataBaseName`.`images-details` WHERE `mimeFileName`='image'";
    $queryVideos = "SELECT count(*) AS totalVideos FROM `$dataBaseName`.`images-details` WHERE `mimeFileName`='video'";
    $queryAudios = "SELECT count(*) AS totalAudios FROM `$dataBaseName`.`images-details` WHERE `mimeFileName`='audio'";

    // Total files
    $resultTotal = mysqli_query($GLOBALS['ligacao'], $queryTotal);
    $totalData = mysqli_fetch_array($resultTotal);
    $stats['numFiles'] = $totalData['totalFiles'];
    mysqli_free_result($resultTotal);
  
    if ( $stats['numFiles']==0 ) {
        $stats['numImages'] = 0;
        $stats['numVideos'] = 0;
        $stats['numAudios'] = 0;

        dbDisconnect();

        return $stats;
    }

    // Image files
    $resultImages = mysqli_query($GLOBALS['ligacao'], $queryImages);
    $totalImages = mysqli_fetch_array($resultImages);
    $stats['numImages'] = $totalImages['totalImages'];
    mysqli_free_result($resultImages);

    // Video files
    $resultVideos = mysqli_query($GLOBALS['ligacao'], $queryVideos);
    $totalVideos = mysqli_fetch_array($resultVideos);
    $stats['numVideos'] = $totalVideos['totalVideos'];
    mysqli_free_result($resultVideos);

    // Audio files
    $resultAudios = mysqli_query($GLOBALS['ligacao'], $queryAudios);
    $totaltAudios = mysqli_fetch_array($resultAudios);
    $stats['numAudios'] = $totaltAudios['totalAudios'];
    mysqli_free_result($resultAudios);

    dbDisconnect();

    return $stats;
}

function showUploadFileError($errorCode) {
    switch ($errorCode) {
        case UPLOAD_ERR_OK:
            $errorMessage = "($errorCode) There is no error, the file uploaded with success.";
            break;

        case UPLOAD_ERR_INI_SIZE:
            $errorMessage = "($errorCode) The uploaded file exceeds the upload_max_filesize directive in php.ini file.";
            break;

        case UPLOAD_ERR_FORM_SIZE:
            $errorMessage = "($errorCode) The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.";
            break;

        case UPLOAD_ERR_PARTIAL:
            $errorMessage = "($errorCode) The uploaded file was only partially uploaded.";
            break;

        case UPLOAD_ERR_NO_FILE:
            $errorMessage = "($errorCode) No file was uploaded.";
            break;

        case UPLOAD_ERR_NO_TMP_DIR:
            $errorMessage = "($errorCode) Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.";
            break;

        case UPLOAD_ERR_CANT_WRITE:
            $errorMessage = "($errorCode) Failed to write file to disk. Introduced in PHP 5.1.0.";
            break;

        case UPLOAD_ERR_EXTENSION:
            $errorMessage = "($errorCode) A PHP extension stopped the file upload.";
            break;

        default:
            $errorMessage = "($errorCode) No description available.";
            break;
    }

    return $errorMessage;
}

function getXdebugArg() {
  $method = $_SERVER['REQUEST_METHOD'];
  
  if ($method == 'POST') {
    $args = $_POST;
  } elseif ($method == 'GET') {
    $args = $_GET;
  }

 foreach ($args as $key => $value) {
    if ( $key==="XDEBUG_SESSION_START" ) {
      return "XDEBUG_SESSION_START=$value";
    }
  }
  
  return null;
}

function getXdebugArgAsArray() {
  $method = $_SERVER['REQUEST_METHOD'];
  
  if ($method == 'POST') {
    $args = $_POST;
  } elseif ($method == 'GET') {
    $args = $_GET;
  }

 foreach ($args as $key => $value) {
    if ( $key==="XDEBUG_SESSION_START" ) {
      return array( "key" => $key, "value" => $value);
    }
  }
  
  return null;
}

function getCategoryList($type, $filterPrimary = "") {
    $categories = array();
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    if ($type === 'primary') {
        $query = "SELECT `primaryCategory` FROM `$dataBaseName`.`category-primary` ORDER BY `primaryCategory` ASC";
    } else {
        // If a primary filter is provided, grab only subcategories belonging to it
        if (!empty($filterPrimary)) {
            $filterPrimary = mysqli_real_escape_string($GLOBALS['ligacao'], $filterPrimary);
            $query = "SELECT `secondaryCategory` FROM `$dataBaseName`.`category-secondary` WHERE `primaryCategory`='$filterPrimary' ORDER BY `secondaryCategory` ASC";
        } else {
            $query = "SELECT * FROM `$dataBaseName`.`category-secondary` ORDER BY `primaryCategory`, `secondaryCategory` ASC";
        }
    }

    $result = mysqli_query($GLOBALS['ligacao'], $query);

    if ($result != false) {
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row;
        }
        mysqli_free_result($result);
    }

    dbDisconnect();
    return $categories;
}
function createPrimaryCategory($categoryName) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $categoryName = mysqli_real_escape_string($GLOBALS['ligacao'], trim($categoryName));
    if (empty($categoryName)) {
        dbDisconnect();
        return false;
    }

    $query = "INSERT INTO `$dataBaseName`.`category-primary` (`primaryCategory`) VALUES ('$categoryName')";
    $success = mysqli_query($GLOBALS['ligacao'], $query);
    
    dbDisconnect();
    return $success;
}

function createSecondaryCategory($primaryCategory, $secondaryCategory) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $primaryCategory = mysqli_real_escape_string($GLOBALS['ligacao'], $primaryCategory);
    $secondaryCategory = mysqli_real_escape_string($GLOBALS['ligacao'], trim($secondaryCategory));

    if (empty($primaryCategory) || empty($secondaryCategory)) {
        dbDisconnect();
        return false;
    }

    $query = "INSERT INTO `$dataBaseName`.`category-secondary` (`primaryCategory`, `secondaryCategory`) 
              VALUES ('$primaryCategory', '$secondaryCategory')";
    $success = mysqli_query($GLOBALS['ligacao'], $query);
    
    dbDisconnect();
    return $success;
}

function checkUserRole($idUser, $roleName) {
    $hasRole = false;
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    // Sanitize values to safely match your legacy query design patterns
    $idUser = (int)$idUser;
    $roleName = mysqli_real_escape_string($GLOBALS['ligacao'], $roleName);

    $query = "SELECT COUNT(*) as `total` 
              FROM `$dataBaseName`.`auth-permissions` p
              JOIN `$dataBaseName`.`auth-roles` r ON p.`idRole` = r.`idRole`
              WHERE p.`idUser`='$idUser' AND r.`friendlyName`='$roleName'";

    $result = mysqli_query($GLOBALS['ligacao'], $query);

    if ($result != false) {
        $data = mysqli_fetch_assoc($result);
        if ($data['total'] > 0) {
            $hasRole = true;
        }
        mysqli_free_result($result);
    }

    dbDisconnect();
    return $hasRole;
}
function writeWikiPage($primaryCategory, $secondaryCategory, $pageTitle, $content) {
    $success = false;
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $primaryCategory = mysqli_real_escape_string($GLOBALS['ligacao'], $primaryCategory);
    $secondaryCategory = mysqli_real_escape_string($GLOBALS['ligacao'], $secondaryCategory);
    $pageTitle = mysqli_real_escape_string($GLOBALS['ligacao'], $pageTitle);
    $content = mysqli_real_escape_string($GLOBALS['ligacao'], $content);

    // Tries an insert first; updates content if the composite key already exists
    $query = "INSERT INTO `$dataBaseName`.`page` (`primaryCategory`, `secondaryCategory`, `pageTitle`, `content`) 
              VALUES ('$primaryCategory', '$secondaryCategory', '$pageTitle', '$content')
              ON DUPLICATE KEY UPDATE `content`='$content'";

    if (mysqli_query($GLOBALS['ligacao'], $query)) {
        $success = true;
    }

    // Send notifications if the database write succeeded
    if ($success) {
        $notifyQuery = "SELECT u.`name`, u.`email` 
                        FROM `$dataBaseName`.`category-notifications` n
                        JOIN `$dataBaseName`.`auth-basic` u ON n.`userId` = u.`idUser`
                        WHERE n.`secondaryCategory` = '$secondaryCategory' AND u.`email` IS NOT NULL AND u.`email` != ''";
        
        $notifyResult = mysqli_query($GLOBALS['ligacao'], $notifyQuery);
        $subscribers = [];
        if ($notifyResult) {
            while ($row = mysqli_fetch_assoc($notifyResult)) {
                $subscribers[] = $row;
            }
            mysqli_free_result($notifyResult);
        }

        if (!empty($subscribers)) {
            $flags[] = FILTER_NULL_ON_FAILURE;
            $serverName = filter_input(INPUT_SERVER, 'SERVER_NAME', FILTER_UNSAFE_RAW, $flags);
            $serverPort = 80;
            $name = webAppName();
            $baseUrl = "http://" . $serverName . ":" . $serverPort;
            $link = $baseUrl . $name . "viewPage.php?title=" . urlencode($pageTitle);

            $Subject = "New/Updated Page in Category: " . $secondaryCategory;
            $Message = "A page titled '" . $pageTitle . "' has been added or updated in the '" . $secondaryCategory . "' category.\n\nYou can view the page here:\n" . $link;

            sendNotificationEmails($subscribers, $Subject, $Message);
        }
    }

    dbDisconnect();
    return $success;
}
function readWikiPage($pageTitle) {
    $pageData = null;
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    // Escape only the single key parameter
    $pageTitle = mysqli_real_escape_string($GLOBALS['ligacao'], $pageTitle);

    $query = "SELECT `content` FROM `$dataBaseName`.`page` 
              WHERE `pageTitle`='$pageTitle'";

    $result = mysqli_query($GLOBALS['ligacao'], $query);

    if ($result != false) {
        $pageData = mysqli_fetch_assoc($result); 
        mysqli_free_result($result);
    }

    dbDisconnect();
    
    // Guard against null if the page doesn't exist
    return isset($pageData['content']) ? $pageData['content'] : null;
}

/**
 * Summary of addCategory
 * @param mixed $type string to indicate if youre adding a primary or secondary cat
 * @param mixed $primaryName name of the primary category to create OR parent category to create second category under
 * @param mixed $secondaryName name of secondary category if creating one, or null if youre just getting a primary one
 * @return bool
 */
function addCategory($type, $primaryName, $secondaryName = "") {
    $success = false;
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $primaryName = mysqli_real_escape_string($GLOBALS['ligacao'], $primaryName);

    if ($type === 'primary') {
        $query = "INSERT INTO `$dataBaseName`.`category-primary` (`primaryCategory`) VALUES ('$primaryName')";
    } else {
        $secondaryName = mysqli_real_escape_string($GLOBALS['ligacao'], $secondaryName);
        $query = "INSERT INTO `$dataBaseName`.`category-secondary` (`primaryCategory`, `secondaryCategory`) VALUES ('$primaryName', '$secondaryName')";
    }

    if (mysqli_query($GLOBALS['ligacao'], $query)) {
        $success = true;
    }

    dbDisconnect();
    return $success;
}

function removeWikiPage($primaryCategory, $secondaryCategory, $pageTitle) {
    $success = false;
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $primaryCategory = mysqli_real_escape_string($GLOBALS['ligacao'], $primaryCategory);
    $secondaryCategory = mysqli_real_escape_string($GLOBALS['ligacao'], $secondaryCategory);
    $pageTitle = mysqli_real_escape_string($GLOBALS['ligacao'], $pageTitle);

    $query = "DELETE FROM `$dataBaseName`.`page` 
              WHERE `primaryCategory`='$primaryCategory' 
              AND `secondaryCategory`='$secondaryCategory' 
              AND `pageTitle`='$pageTitle'";

    if (mysqli_query($GLOBALS['ligacao'], $query)) {
        $success = true;
    }

    dbDisconnect();
    return $success;
}


// Helper function to get parent categories for a specific page title
function getPageMetaData($pageTitle) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $pageTitle = mysqli_real_escape_string($GLOBALS['ligacao'], $pageTitle);

    $query = "SELECT `primaryCategory`, `secondaryCategory`, `visibility` FROM `$dataBaseName`.`page` 
              WHERE `pageTitle`='$pageTitle'";

    $result = mysqli_query($GLOBALS['ligacao'], $query);
    $meta = null;

    if ($result != false) {
        $meta = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
    }

    dbDisconnect();
    return $meta;
}

// Helper function to query page titles under a specific secondary category composite key
function getPagesList($primaryCategory, $secondaryCategory) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $primaryCategory = mysqli_real_escape_string($GLOBALS['ligacao'], $primaryCategory);
    $secondaryCategory = mysqli_real_escape_string($GLOBALS['ligacao'], $secondaryCategory);

    // Added roleLevelVisibility to the SELECT statement
    $query = "SELECT `pageTitle`, `visibility` FROM `$dataBaseName`.`page` 
              WHERE `primaryCategory`='$primaryCategory' 
              AND `secondaryCategory`='$secondaryCategory' 
              ORDER BY `pageTitle` ASC";

    $result = mysqli_query($GLOBALS['ligacao'], $query);
    $pages = array();

    if ($result != false) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Stores both the title and the visibility level as an associative array element
            $pages[] = [
                'pageTitle'           => $row['pageTitle'],
                'visibility' => (int)$row['visibility']
            ];
        }
        mysqli_free_result($result);
    }

    dbDisconnect();
    return $pages;
}
function authorizeUserByLevel($username, $requiredRoleName) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $username = mysqli_real_escape_string($GLOBALS['ligacao'], $username);
    $requiredRoleName = mysqli_real_escape_string($GLOBALS['ligacao'], $requiredRoleName);

    // 1. Get the level of the required role from the DB
    $reqQuery = "SELECT `roleLevel` FROM `$dataBaseName`.`auth-roles` WHERE `friendlyName` = '$requiredRoleName' LIMIT 1";
    $reqResult = mysqli_query($GLOBALS['ligacao'], $reqQuery);
    
    if (!$reqResult || mysqli_num_rows($reqResult) === 0) {
        if ($reqResult) mysqli_free_result($reqResult);
        dbDisconnect();
        return false; 
    }
    $reqRow = mysqli_fetch_assoc($reqResult);
    $requiredLevel = (int)$reqRow['roleLevel'];
    mysqli_free_result($reqResult);

    // 2 & 3. Get the user's role and JOIN to resolve their actual level from the DB
    $userQuery = "SELECT r.`roleLevel` 
                  FROM `$dataBaseName`.`auth-basic` u
                  JOIN `$dataBaseName`.`auth-roles` r ON u.`idRole` = r.`idRole`
                  WHERE u.`name` = '$username'
                  LIMIT 1";
                  
    $userResult = mysqli_query($GLOBALS['ligacao'], $userQuery);
    
    $userLevel = 0; // Baseline fallback level (unauthenticated/lowest possible)
    if ($userResult && mysqli_num_rows($userResult) > 0) {
        $userRow = mysqli_fetch_assoc($userResult);
        $userLevel = (int)$userRow['roleLevel'];
        mysqli_free_result($userResult);
    }

    dbDisconnect();

    // Check if the user's level meets or exceeds the required level
    return ($userLevel >= $requiredLevel);
}
function getUserRoleFriendlyName($username) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $username = mysqli_real_escape_string($GLOBALS['ligacao'], $username);

    $query = "SELECT r.`friendlyName` 
              FROM `$dataBaseName`.`auth-basic` u
              JOIN `$dataBaseName`.`auth-roles` r ON u.`idRole` = r.`idRole`
              WHERE u.`name` = '$username' 
              LIMIT 1";
    $result = mysqli_query($GLOBALS['ligacao'], $query);
    $friendlyName = null;

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $friendlyName = $row['friendlyName'];
        mysqli_free_result($result);
    }

    dbDisconnect();
    return $friendlyName;
}

function processPageChange($username, $pageTitle, $newContent, $visibility) {
    // 1. Check role authorization level using your existing function
    $isEditorOrHigher = authorizeUserByLevel($username, 'editor');

    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    // Sanitize values for queries
    $username   = mysqli_real_escape_string($GLOBALS['ligacao'], $username);
    $pageTitle  = mysqli_real_escape_string($GLOBALS['ligacao'], $pageTitle);
    $newContent = mysqli_real_escape_string($GLOBALS['ligacao'], $newContent);
    $newVisibility = mysqli_real_escape_string($GLOBALS['ligacao'], $visibility);

    // 2. Fetch user metadata needed for contributions score and fallback tracking
    $userQuery = "SELECT `idUser`, `contributions` 
                  FROM `$dataBaseName`.`auth-basic` 
                  WHERE `name` = '$username' 
                  LIMIT 1";

    $userResult = mysqli_query($GLOBALS['ligacao'], $userQuery);
    
    if (!$userResult || mysqli_num_rows($userResult) === 0) {
        if ($userResult) mysqli_free_result($userResult);
        dbDisconnect();
        return false; // User not found
    }

    $userData      = mysqli_fetch_assoc($userResult);
    $idUser        = (int)$userData['idUser'];
    $contributions = (int)$userData['contributions'];
    mysqli_free_result($userResult);

    $hasHighContributions = ($contributions > 3);
    $isDirectApply = ($isEditorOrHigher || $hasHighContributions);

    // 3. Routing decision logic
    if ($isDirectApply) {
        // Direct Apply: Update live production document text immediately
        $updateQuery = "UPDATE `$dataBaseName`.`page` 
                        SET `content` = '$newContent', `visibility` = '$newVisibility' 
                        WHERE `pageTitle` = '$pageTitle'";
        $success = mysqli_query($GLOBALS['ligacao'], $updateQuery);
        
        if ($success) {
            mysqli_query($GLOBALS['ligacao'], "UPDATE `$dataBaseName`.`auth-basic` SET `contributions` = `contributions` + 1 WHERE `idUser` = $idUser");
        }
    } else {
        // Sandbox Review: Write proposal safely into queue moderation backlog log files
        $insertQuery = "INSERT INTO `$dataBaseName`.`page-changes` (`pageTitle`, `editorId`, `newContent`) 
                        VALUES ('$pageTitle', $idUser, '$newContent')";
        $success = mysqli_query($GLOBALS['ligacao'], $insertQuery);
    }

    // 4. Send Notifications if the direct update was successful
    if ($success && $isDirectApply) {
        $notifyQuery = "SELECT u.`name`, u.`email` 
                        FROM `$dataBaseName`.`page-notifications` n
                        JOIN `$dataBaseName`.`auth-basic` u ON n.`userId` = u.`idUser`
                        WHERE n.`pageTitle` = '$pageTitle' AND u.`email` IS NOT NULL AND u.`email` != ''";
        
        $notifyResult = mysqli_query($GLOBALS['ligacao'], $notifyQuery);
        $subscribers = [];
        if ($notifyResult) {
            while ($row = mysqli_fetch_assoc($notifyResult)) {
                $subscribers[] = $row;
            }
            mysqli_free_result($notifyResult);
        }

        if (!empty($subscribers)) {
            $flags[] = FILTER_NULL_ON_FAILURE;
            $serverName = filter_input(INPUT_SERVER, 'SERVER_NAME', FILTER_UNSAFE_RAW, $flags);
            $serverPort = 80;
            $name = webAppName();
            $baseUrl = "http://" . $serverName . ":" . $serverPort;
            $link = $baseUrl . $name . "viewPage.php?title=" . urlencode($pageTitle);

            $Subject = "Wiki Page Updated: " . $pageTitle;
            $Message = "The page '" . $pageTitle . "' has been updated by " . $username . ".\n\nYou can view the changes here:\n" . $link;

            sendNotificationEmails($subscribers, $Subject, $Message);
        }
    }

    dbDisconnect();
    return $success;
}

function sendNotificationEmails($subscribers, $subject, $message) {
    if (empty($subscribers)) {
        return;
    }

    $dataBaseName = $GLOBALS['configDataBase']->db;
    $emailId = 4;
    $queryString = "SELECT * FROM `$dataBaseName`.`email-accounts` WHERE `id`=$emailId";
    $queryResult = mysqli_query($GLOBALS['ligacao'], $queryString);
    
    if ($queryResult && $record = mysqli_fetch_array($queryResult)) {
        $smtpServer = $record['smtpServer'];
        $port = intval($record['port']);
        $useSSL = boolval($record['useSSL']);
        $timeout = intval($record['timeout']);
        $loginName = $record['loginName'];
        $password = $record['password'];
        $fromEmail = $record['email'];
        $fromName = $record['displayName'];
        
        mysqli_free_result($queryResult);

        foreach ($subscribers as $subscriber) {
            sendAuthEmail(
                $smtpServer,
                $useSSL,
                $port,
                $timeout,
                $loginName,
                $password,
                $fromEmail,
                $fromName,
                $subscriber['name'] . " <" . $subscriber['email'] . ">",
                NULL,
                NULL,
                $subject,
                $message,
                false,
                NULL
            );
        }
    } else if ($queryResult) {
        mysqli_free_result($queryResult);
    }
}

function getNotificationEmailsByPage($pageTitle) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $pageTitle = mysqli_real_escape_string($GLOBALS['ligacao'], $pageTitle);
    $emails = [];

    $query = "SELECT u.`email` 
              FROM `$dataBaseName`.`page-notifications` n
              JOIN `$dataBaseName`.`auth-basic` u ON n.`userId` = u.`idUser`
              WHERE n.`pageTitle` = '$pageTitle' AND u.`email` IS NOT NULL AND u.`email` != ''";

    $result = mysqli_query($GLOBALS['ligacao'], $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $emails[] = $row['email'];
        }
        mysqli_free_result($result);
    }

    dbDisconnect();
    return array_unique($emails);
}

function getNotificationEmailsByCategory($secondaryCategory) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $secondaryCategory = mysqli_real_escape_string($GLOBALS['ligacao'], $secondaryCategory);
    $emails = [];

    // Fetches subscribers based on the exact secondaryCategory matching your configuration
    $query = "SELECT u.`email` 
              FROM `$dataBaseName`.`category-notifications` n
              JOIN `$dataBaseName`.`auth-basic` u ON n.`userId` = u.`idUser`
              WHERE n.`secondaryCategory` = '$secondaryCategory' AND u.`email` IS NOT NULL AND u.`email` != ''";

    $result = mysqli_query($GLOBALS['ligacao'], $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $emails[] = $row['email'];
        }
        mysqli_free_result($result);
    }

    dbDisconnect();
    return array_unique($emails);
}
function togglePageSubscription($userId, $pageTitle) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $userId = (int)$userId;
    $pageTitle = mysqli_real_escape_string($GLOBALS['ligacao'], $pageTitle);

    $check = "SELECT 1 FROM `$dataBaseName`.`page-notifications` WHERE `userId` = $userId AND `pageTitle` = '$pageTitle' LIMIT 1";
    $result = mysqli_query($GLOBALS['ligacao'], $check);

    if ($result && mysqli_num_rows($result) > 0) {
        $query = "DELETE FROM `$dataBaseName`.`page-notifications` WHERE `userId` = $userId AND `pageTitle` = '$pageTitle'";
    } else {
        $query = "INSERT INTO `$dataBaseName`.`page-notifications` (`userId`, `pageTitle`) VALUES ($userId, '$pageTitle')";
    }
    
    if ($result) mysqli_free_result($result);
    $success = mysqli_query($GLOBALS['ligacao'], $query);
    dbDisconnect();
    return $success;
}

function toggleCategorySubscription($userId, $primaryCategory, $secondaryCategory) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $userId = (int)$userId;
    $primaryCategory = mysqli_real_escape_string($GLOBALS['ligacao'], $primaryCategory);
    $secondaryCategory = mysqli_real_escape_string($GLOBALS['ligacao'], $secondaryCategory);

    $check = "SELECT 1 FROM `$dataBaseName`.`category-notifications` 
              WHERE `userId` = $userId AND `primaryCategory` = '$primaryCategory' AND `secondaryCategory` = '$secondaryCategory' LIMIT 1";
    $result = mysqli_query($GLOBALS['ligacao'], $check);

    if ($result && mysqli_num_rows($result) > 0) {
        $query = "DELETE FROM `$dataBaseName`.`category-notifications` 
                  WHERE `userId` = $userId AND `primaryCategory` = '$primaryCategory' AND `secondaryCategory` = '$secondaryCategory'";
    } else {
        $query = "INSERT INTO `$dataBaseName`.`category-notifications` (`userId`, `primaryCategory`, `secondaryCategory`) 
                  VALUES ($userId, '$primaryCategory', '$secondaryCategory')";
    }

    if ($result) mysqli_free_result($result);
    $success = mysqli_query($GLOBALS['ligacao'], $query);
    dbDisconnect();
    return $success;
}

function isSubscribedToPage($userId, $pageTitle) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $userId = (int)$userId;
    $pageTitle = mysqli_real_escape_string($GLOBALS['ligacao'], $pageTitle);

    $query = "SELECT 1 FROM `$dataBaseName`.`page-notifications` WHERE `userId` = $userId AND `pageTitle` = '$pageTitle' LIMIT 1";
    $result = mysqli_query($GLOBALS['ligacao'], $query);
    $status = ($result && mysqli_num_rows($result) > 0);

    if ($result) mysqli_free_result($result);
    dbDisconnect();
    return $status;
}

function isSubscribedToCategory($userId, $primaryCategory, $secondaryCategory) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $userId = (int)$userId;
    $primaryCategory = mysqli_real_escape_string($GLOBALS['ligacao'], $primaryCategory);
    $secondaryCategory = mysqli_real_escape_string($GLOBALS['ligacao'], $secondaryCategory);

    $query = "SELECT 1 FROM `$dataBaseName`.`category-notifications` 
              WHERE `userId` = $userId AND `primaryCategory` = '$primaryCategory' AND `secondaryCategory` = '$secondaryCategory' LIMIT 1";
    $result = mysqli_query($GLOBALS['ligacao'], $query);
    $status = ($result && mysqli_num_rows($result) > 0);

    if ($result) mysqli_free_result($result);
    dbDisconnect();
    return $status;
}
function getAvailableRolesUpToUser($username) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $username = mysqli_real_escape_string($GLOBALS['ligacao'], $username);

    $userQuery = "SELECT r.`roleLevel` 
                  FROM `$dataBaseName`.`auth-basic` u
                  JOIN `$dataBaseName`.`auth-roles` r ON u.`idRole` = r.`idRole`
                  WHERE u.`name` = '$username' 
                  LIMIT 1";
                  
    $userResult = mysqli_query($GLOBALS['ligacao'], $userQuery);
    
    if (!$userResult || mysqli_num_rows($userResult) === 0) {
        if ($userResult) mysqli_free_result($userResult);
        dbDisconnect();
        return []; 
    }

    $userRow = mysqli_fetch_assoc($userResult);
    $userMaxLevel = (int)$userRow['roleLevel'];
    mysqli_free_result($userResult);

    // 3. Fetch roleLevel and friendlyName columns from auth-roles up to the user's level (inclusive)
    $rolesQuery = "SELECT `roleLevel`, `friendlyName` 
                   FROM `$dataBaseName`.`auth-roles` 
                   WHERE `roleLevel` <= $userMaxLevel 
                   ORDER BY `roleLevel` ASC";

    $rolesResult = mysqli_query($GLOBALS['ligacao'], $rolesQuery);
    $rolesList = [];

    if ($rolesResult) {
        while ($row = mysqli_fetch_assoc($rolesResult)) {
            $rolesList[] = [
                'roleLevel'    => (int)$row['roleLevel'],
                'friendlyName' => $row['friendlyName']
            ];
        }
        mysqli_free_result($rolesResult);
    }

    dbDisconnect();
    return $rolesList;
}
function authorizeUserByNumericLevel($username, $requiredLevel) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    if(!isset($username)) return $requiredLevel == 0;

    $username = mysqli_real_escape_string($GLOBALS['ligacao'], $username);
    $requiredLevel = (int)$requiredLevel;

    $userQuery = "SELECT r.`roleLevel` 
                  FROM `$dataBaseName`.`auth-basic` u
                  JOIN `$dataBaseName`.`auth-roles` r ON u.`idRole` = r.`idRole`
                  WHERE u.`name` = '$username' 
                  LIMIT 1";
                  
    $userResult = mysqli_query($GLOBALS['ligacao'], $userQuery);
    
    $userLevel = 0; 
    if ($userResult && mysqli_num_rows($userResult) > 0) {
        $userRow = mysqli_fetch_assoc($userResult);
        $userLevel = (int)$userRow['roleLevel'];
        mysqli_free_result($userResult);
    }

    dbDisconnect();

    return ($userLevel >= $requiredLevel);
}
function getPendingProposalsCount() {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $query = "SELECT COUNT(*) as total FROM `$dataBaseName`.`page-changes`";
    $result = mysqli_query($GLOBALS['ligacao'], $query);
    
    $count = 0;
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        $count = (int)$row['total'];
        mysqli_free_result($result);
    }
    dbDisconnect();
    return $count;
}

function getAllProposals() {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $query = "SELECT pc.`changeId`, pc.`pageTitle`, pc.`newContent`, u.`name` as editorName, p.`content` as currentContent 
              FROM `$dataBaseName`.`page-changes` pc
              LEFT JOIN `$dataBaseName`.`auth-basic` u ON pc.`editorId` = u.`idUser`
              LEFT JOIN `$dataBaseName`.`page` p ON pc.`pageTitle` = p.`pageTitle`
              ORDER BY pc.`changeId` ASC";

    $result = mysqli_query($GLOBALS['ligacao'], $query);
    $proposals = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $proposals[] = $row;
        }
        mysqli_free_result($result);
    }
    dbDisconnect();
    return $proposals;
}

function moderateProposal($changeId, $action) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $changeId = (int)$changeId;
    $success = false;

    // 1. Fetch proposal details first
    $query = "SELECT `pageTitle`, `editorId`, `newContent` FROM `$dataBaseName`.`page-changes` WHERE `changeId` = $changeId LIMIT 1";
    $result = mysqli_query($GLOBALS['ligacao'], $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $proposal = mysqli_fetch_assoc($result);
        mysqli_free_result($result);

        $pageTitle  = mysqli_real_escape_string($GLOBALS['ligacao'], $proposal['pageTitle']);
        $editorId   = (int)$proposal['editorId'];
        $newContent = mysqli_real_escape_string($GLOBALS['ligacao'], $proposal['newContent']);

        if ($action === 'accept') {
            // Update the live production page content
            $updatePage = "UPDATE `$dataBaseName`.`page` SET `content` = '$newContent' WHERE `pageTitle` = '$pageTitle'";
            if (mysqli_query($GLOBALS['ligacao'], $updatePage)) {
                // Reward the contributor with a point
                mysqli_query($GLOBALS['ligacao'], "UPDATE `$dataBaseName`.`auth-basic` SET `contributions` = `contributions` + 1 WHERE `idUser` = $editorId");
                $success = true;
            }
        } else {
            // Rejection requires no production updates
            $success = true;
        }

        // 2. Remove from moderation log regardless of action outcome
        if ($success) {
            mysqli_query($GLOBALS['ligacao'], "DELETE FROM `$dataBaseName`.`page-changes` WHERE `changeId` = $changeId");
        }
    } else if ($result) {
        mysqli_free_result($result);
    }

    dbDisconnect();
    return $success;
}


/**
 * Lê todas as discussões ativas, globalmente ou filtradas por categorias. digo
 * @param string|null $primaryCategory Categoria primária a filtrar (opcional)
 * @param string|null $secondaryCategory Categoria secundária a filtrar (opcional)
 * @return array Lista associativa de discussões
 */
function getForumDiscussions($primaryCategory = null, $secondaryCategory = null) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $query = "SELECT d.*, u.name as author, 
              (SELECT COUNT(*) FROM `$dataBaseName`.`forum_posts` p WHERE p.idDiscussion = d.idDiscussion) as total_replies
              FROM `$dataBaseName`.`forum_discussions` d
              JOIN `$dataBaseName`.`auth-basic` u ON d.idUser = u.idUser";

    $whereClauses = [];
    if (!empty($primaryCategory)) {
        $pCatSafe = mysqli_real_escape_string($GLOBALS['ligacao'], $primaryCategory);
        $whereClauses[] = "d.primaryCategory = '$pCatSafe'";
        if (!empty($secondaryCategory)) {
            $sCatSafe = mysqli_real_escape_string($GLOBALS['ligacao'], $secondaryCategory);
            $whereClauses[] = "d.secondaryCategory = '$sCatSafe'";
        }
    }

    if (!empty($whereClauses)) {
        $query .= " WHERE " . implode(" AND ", $whereClauses);
    }

    $query .= " ORDER BY d.isSticky DESC, d.last_posted_at DESC";

    $result = mysqli_query($GLOBALS['ligacao'], $query);
    $discussions = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $discussions[] = $row;
        }
        mysqli_free_result($result);
    }

    dbDisconnect();
    return $discussions;
}

/**
 * Lê todos os posts cronológicos de uma discussão e verifica se o utilizador atual já fez Like.
 * @param int $idDiscussion ID do tópico a abrir
 * @param int $currentUserId ID do utilizador ativo (para avaliar os gostos)
 * @return array Lista de posts do tópico
 */
function getForumPosts($idDiscussion, $currentUserId = 0) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $idDiscussionSafe = (int)$idDiscussion;
    $currentUserSafe = (int)$currentUserId;

    $query = "SELECT p.*, u.name as author,
              (SELECT COUNT(*) FROM `$dataBaseName`.`forum_likes` l WHERE l.idPost = p.idPost) as likes_count,
              EXISTS(SELECT 1 FROM `$dataBaseName`.`forum_likes` l WHERE l.idPost = p.idPost AND l.idUser = $currentUserSafe) as has_liked
              FROM `$dataBaseName`.`forum_posts` p
              JOIN `$dataBaseName`.`auth-basic` u ON p.idUser = u.idUser
              WHERE p.idDiscussion = $idDiscussionSafe
              ORDER BY p.created_at ASC";

    $result = mysqli_query($GLOBALS['ligacao'], $query);
    $posts = [];

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            // Conversão boolean para o JSON do frontend
            $row['has_liked'] = (bool)$row['has_liked']; 
            $posts[] = $row;
        }
        mysqli_free_result($result);
    }

    dbDisconnect();
    return $posts;
}

/**
 * Cria uma nova discussão (Tópico). Abre também o primeiro Post correspondente.
 * @return int|bool Retorna o ID da nova discussão criada ou falso em caso de erro.
 */
function createForumDiscussion($idUser, $title, $content, $primaryCategory, $secondaryCategory = null) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $idUserSafe = (int)$idUser;
    $titleSafe = mysqli_real_escape_string($GLOBALS['ligacao'], $title);
    $contentSafe = mysqli_real_escape_string($GLOBALS['ligacao'], $content);
    $primarySafe = mysqli_real_escape_string($GLOBALS['ligacao'], $primaryCategory);
    $secondarySafe = !empty($secondaryCategory) ? "'" . mysqli_real_escape_string($GLOBALS['ligacao'], $secondaryCategory) . "'" : "NULL";
    
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $slugSafe = mysqli_real_escape_string($GLOBALS['ligacao'], $slug);

    mysqli_begin_transaction($GLOBALS['ligacao']);

    // 1. Inserir Discussão
    $queryDisc = "INSERT INTO `$dataBaseName`.`forum_discussions` (`title`, `slug`, `idUser`, `primaryCategory`, `secondaryCategory`) 
                  VALUES ('$titleSafe', '$slugSafe', $idUserSafe, '$primarySafe', $secondarySafe)";
    
    if (mysqli_query($GLOBALS['ligacao'], $queryDisc)) {
        $idDiscussion = mysqli_insert_id($GLOBALS['ligacao']);

        // 2. Inserir o primeiro Post associado a essa discussão
        $queryPost = "INSERT INTO `$dataBaseName`.`forum_posts` (`idDiscussion`, `idUser`, `content`) 
                      VALUES ($idDiscussion, $idUserSafe, '$contentSafe')";
        
        if (mysqli_query($GLOBALS['ligacao'], $queryPost)) {
            mysqli_commit($GLOBALS['ligacao']);
            dbDisconnect();
            return $idDiscussion;
        }
    }

    mysqli_rollback($GLOBALS['ligacao']);
    dbDisconnect();
    return false;
}

/**
 * Adiciona uma resposta a uma discussão existente e dá o "bump" de tempo ao tópico pai.
 */
function createForumPost($idUser, $idDiscussion, $content) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $idUserSafe = (int)$idUser;
    $idDiscSafe = (int)$idDiscussion;
    $contentSafe = mysqli_real_escape_string($GLOBALS['ligacao'], $content);

    mysqli_begin_transaction($GLOBALS['ligacao']);

    $queryPost = "INSERT INTO `$dataBaseName`.`forum_posts` (`idDiscussion`, `idUser`, `content`) 
                  VALUES ($idDiscSafe, $idUserSafe, '$contentSafe')";

    if (mysqli_query($GLOBALS['ligacao'], $queryPost)) {
        // Atualizar o last_posted_at do tópico para o empurrar para cima na listagem
        $queryBump = "UPDATE `$dataBaseName`.`forum_discussions` 
                      SET `last_posted_at` = NOW() 
                      WHERE `idDiscussion` = $idDiscSafe";
        mysqli_query($GLOBALS['ligacao'], $queryBump);
        
        mysqli_commit($GLOBALS['ligacao']);
        dbDisconnect();
        return true;
    }

    mysqli_rollback($GLOBALS['ligacao']);
    dbDisconnect();
    return false;
}

/**
 * Liga ou desliga o "Gosto" de um utilizador num post específico.
 * @return string 'liked' ou 'unliked' ou false em caso de falha.
 */
function toggleForumLike($idUser, $idPost) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $idUserSafe = (int)$idUser;
    $idPostSafe = (int)$idPost;
    $action = false;

    // Verificar se já tem Gosto
    $queryCheck = "SELECT 1 FROM `$dataBaseName`.`forum_likes` WHERE `idPost` = $idPostSafe AND `idUser` = $idUserSafe";
    $resultCheck = mysqli_query($GLOBALS['ligacao'], $queryCheck);

    if ($resultCheck && mysqli_num_rows($resultCheck) > 0) {
        // Remover Gosto
        $queryDel = "DELETE FROM `$dataBaseName`.`forum_likes` WHERE `idPost` = $idPostSafe AND `idUser` = $idUserSafe";
        if (mysqli_query($GLOBALS['ligacao'], $queryDel)) {
            $action = 'unliked';
        }
    } else {
        // Inserir Gosto
        $queryIns = "INSERT INTO `$dataBaseName`.`forum_likes` (`idPost`, `idUser`) VALUES ($idPostSafe, $idUserSafe)";
        if (mysqli_query($GLOBALS['ligacao'], $queryIns)) {
            $action = 'liked';
        }
    }

    if ($resultCheck) mysqli_free_result($resultCheck);
    dbDisconnect();
    return $action;
}

/**
 * Utilitário partilhado para resolver o ID de um utilizador ativo com base no HTTP Auth
 */
function getActiveUserIdFromAuth() {
    $username = $_SERVER['PHP_AUTH_USER'] ?? null;
    if (!$username) return 0;

    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $usernameSafe = mysqli_real_escape_string($GLOBALS['ligacao'], $username);
    $query = "SELECT `idUser` FROM `$dataBaseName`.`auth-basic` WHERE `name` = '$usernameSafe' AND `active` = 1 LIMIT 1";
    $result = mysqli_query($GLOBALS['ligacao'], $query);
    
    $id = 0;
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $id = (int)$row['idUser'];
        mysqli_free_result($result);
    }

    dbDisconnect();
    return $id;
}
?>