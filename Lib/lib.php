<?php

require_once( "db.php" );

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

    $query = "SELECT `primaryCategory`, `secondaryCategory` FROM `$dataBaseName`.`page` 
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

    $query = "SELECT `pageTitle` FROM `$dataBaseName`.`page` 
              WHERE `primaryCategory`='$primaryCategory' 
              AND `secondaryCategory`='$secondaryCategory' 
              ORDER BY `pageTitle` ASC";

    $result = mysqli_query($GLOBALS['ligacao'], $query);
    $pages = array();

    if ($result != false) {
        while ($row = mysqli_fetch_assoc($result)) {
            $pages[] = $row['pageTitle'];
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
function processPageChange($username, $pageTitle, $newContent) {
    // 1. Check role authorization level using your existing function
    $isEditorOrHigher = authorizeUserByLevel($username, 'editor');

    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    // Sanitize values for queries
    $username   = mysqli_real_escape_string($GLOBALS['ligacao'], $username);
    $pageTitle  = mysqli_real_escape_string($GLOBALS['ligacao'], $pageTitle);
    $newContent = mysqli_real_escape_string($GLOBALS['ligacao'], $newContent);

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

    // 3. Routing decision logic
    if ($isEditorOrHigher || $hasHighContributions) {
        // Direct Apply: Update live production document text immediately
        $updateQuery = "UPDATE `$dataBaseName`.`page` 
                        SET `content` = '$newContent' 
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

    dbDisconnect();
    return $success;
}
?>