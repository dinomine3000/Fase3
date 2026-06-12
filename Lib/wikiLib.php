<?php 

function getEmailConfig() {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    $conn = $GLOBALS['ligacao'];
    mysqli_select_db($conn, $dataBaseName);
    
    $query = "SELECT `email`, `password`, `displayName` FROM `$dataBaseName`.`email-accounts` WHERE `id` = 4 LIMIT 1";
    $result = mysqli_query($conn, $query);

    $config = null;
    if ($result != false) {
        if ($row = mysqli_fetch_assoc($result)) {
            $config = $row;
        }
        mysqli_free_result($result);
    }
    dbDisconnect();
    return $config;
}
function getImageConfig() {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    $conn = $GLOBALS['ligacao'];
    mysqli_select_db($conn, $dataBaseName);
    
    $query = "SELECT `destination`, `thumbType`, `maxFileSize`, `thumbWidth`, `thumbHeight` FROM `$dataBaseName`.`images-config` WHERE `id` = 1 LIMIT 1";
    $result = mysqli_query($conn, $query);

    $config = null;
    if ($result != false) {
        if ($row = mysqli_fetch_assoc($result)) {
            $config = $row;
        }
        mysqli_free_result($result);
    }
    dbDisconnect();
    return $config;
}

function setImageConfig($destinationFolder, $thumbType, $thumbWidth, $thumbHeight, $fileSize) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    $conn = $GLOBALS['ligacao'];
    mysqli_select_db($conn, $dataBaseName);
    
    $query = "UPDATE `$dataBaseName`.`images-config` SET `destination` = ?, `thumbType` = ?, `maxFileSize` = ?, `thumbWidth` = ?, `thumbHeight` = ? WHERE `id` = 1";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        dbDisconnect();
        return false;
    }

    $thumbWidth = (int)$thumbWidth;
    $thumbHeight = (int)$thumbHeight;

    mysqli_stmt_bind_param($stmt, 'ssiii', $destinationFolder, $thumbType, $fileSize, $thumbWidth, $thumbHeight);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    dbDisconnect();
    return $success;
}

function setEmailConfig($email, $password, $displayName) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    $conn = $GLOBALS['ligacao'];
    mysqli_select_db($conn, $dataBaseName);
    
    $query = "UPDATE `$dataBaseName`.`email-accounts` SET `email` = ?, `password` = ?, `displayName` = ? WHERE `id` = 4";
    $stmt = mysqli_prepare($conn, $query);
    
    if (!$stmt) {
        dbDisconnect();
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'sss', $email, $password, $displayName);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    
    dbDisconnect();
    return $success;
}

function getResultsMatching($testString, $desiredColumn = '', $tableName = 'auth-basic', $columnName = 'name', $maxResults = 5) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);
    
    $escapedString = mysqli_real_escape_string($GLOBALS['ligacao'], $testString);
    $escapedTable = mysqli_real_escape_string($GLOBALS['ligacao'], $tableName);
    $escapedColumn = mysqli_real_escape_string($GLOBALS['ligacao'], $columnName);
    $maxResults = (int)$maxResults;

    $query = "SELECT " . ($desiredColumn === '' ? '*' : $desiredColumn) . " FROM `$dataBaseName`.`$escapedTable` WHERE `$escapedColumn` LIKE '%$escapedString%' LIMIT $maxResults";
    $result = mysqli_query($GLOBALS['ligacao'], $query);

    if ($result != false) {
        while ($row = mysqli_fetch_assoc($result)) {
            $matchingResults[] = $row;
        }
        mysqli_free_result($result);
    }
    dbDisconnect();

    return empty($matchingResults) ? null : $matchingResults;
}
function changeUserInfo($username, $columnName, $newValue){
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    $conn = $GLOBALS['ligacao'];
    mysqli_select_db($conn, $dataBaseName);
    
    $updateQuery = "UPDATE `auth-basic` SET `$columnName` = ? WHERE `name` = ?";

    $stmt = mysqli_prepare($conn, $updateQuery);
    if (!$stmt) {
        dbDisconnect();
        return false;
    }

    mysqli_stmt_bind_param($stmt, 'ss', $newValue, $username);
    $success = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    dbDisconnect();
    return $success;
}
function getUserInfo($username) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);
    
    $query = "SELECT `active`, `isBanned`, `contributions`, `bio` FROM `$dataBaseName`.`auth-basic` WHERE `name`='$username' LIMIT 1";
    $result = mysqli_query($GLOBALS['ligacao'], $query);

    if ($result != false && $result != null) {
        if ($row = mysqli_fetch_assoc($result)) {
            $userResult = $row;
        }
        mysqli_free_result($result);
    }
    dbDisconnect();
    
    if(!isset($userResult)) return null;
    return $userResult;
}
function getCategoryList($type, $filterPrimary = "") {
    $categories = array();
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    if ($type === 'primary') {
        $query = "SELECT `primaryCategory` FROM `$dataBaseName`.`category-primary` ORDER BY `primaryCategory` ASC";
    } else {
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

    $query = "INSERT INTO `$dataBaseName`.`page` (`primaryCategory`, `secondaryCategory`, `pageTitle`, `content`) 
              VALUES ('$primaryCategory', '$secondaryCategory', '$pageTitle', '$content')
              ON DUPLICATE KEY UPDATE `content`='$content'";

    if (mysqli_query($GLOBALS['ligacao'], $query)) {
        $success = true;
    }

    dbDisconnect();

    if ($success) {
        $subscribers = getNotificationEmailsByCategory($secondaryCategory);
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
    return $success;
}
function readWikiPage($pageTitle) {
    $pageData = null;
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $pageTitle = mysqli_real_escape_string($GLOBALS['ligacao'], $pageTitle);

    $query = "SELECT `content` FROM `$dataBaseName`.`page` 
              WHERE `pageTitle`='$pageTitle'";

    $result = mysqli_query($GLOBALS['ligacao'], $query);

    if ($result != false) {
        $pageData = mysqli_fetch_assoc($result); 
        mysqli_free_result($result);
    }

    dbDisconnect();
    
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

function getPagesList($primaryCategory, $secondaryCategory) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $primaryCategory = mysqli_real_escape_string($GLOBALS['ligacao'], $primaryCategory);
    $secondaryCategory = mysqli_real_escape_string($GLOBALS['ligacao'], $secondaryCategory);

    $query = "SELECT `pageTitle`, `visibility` FROM `$dataBaseName`.`page` 
              WHERE `primaryCategory`='$primaryCategory' 
              AND `secondaryCategory`='$secondaryCategory' 
              ORDER BY `pageTitle` ASC";

    $result = mysqli_query($GLOBALS['ligacao'], $query);
    $pages = array();

    if ($result != false) {
        while ($row = mysqli_fetch_assoc($result)) {
            $pages[] = [
                'pageTitle' => $row['pageTitle'],
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

    if($username === null || !isset($username)) return false;

    $username = mysqli_real_escape_string($GLOBALS['ligacao'], $username);
    $requiredRoleName = mysqli_real_escape_string($GLOBALS['ligacao'], $requiredRoleName);

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
function getUserRoleInfo($username) {
    if(!isset($username)) return [
        'friendlyName' => 'guest',
        'roleLevel' => 0,
        'idRole' => '0',
    ];
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $username = mysqli_real_escape_string($GLOBALS['ligacao'], $username);

    $query = "SELECT r.`friendlyName`, r.`roleLevel`, r.`idRole` 
              FROM `$dataBaseName`.`auth-basic` u
              JOIN `$dataBaseName`.`auth-roles` r ON u.`idRole` = r.`idRole`
              WHERE u.`name` = '$username' 
              LIMIT 1";
    $result = mysqli_query($GLOBALS['ligacao'], $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
    }

    dbDisconnect();
    return isset($row) ? $row : null;
}

function processPageChange($username, $pageTitle, $newContent, $visibility) {
    $isEditorOrHigher = authorizeUserByLevel($username, 'editor');

    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $username   = mysqli_real_escape_string($GLOBALS['ligacao'], $username);
    $pageTitle  = mysqli_real_escape_string($GLOBALS['ligacao'], $pageTitle);
    $newContent = mysqli_real_escape_string($GLOBALS['ligacao'], $newContent);
    $newVisibility = mysqli_real_escape_string($GLOBALS['ligacao'], $visibility);

    $userQuery = "SELECT `idUser`, `contributions` 
                  FROM `$dataBaseName`.`auth-basic` 
                  WHERE `name` = '$username' 
                  LIMIT 1";

    $userResult = mysqli_query($GLOBALS['ligacao'], $userQuery);
    
    if (!$userResult || mysqli_num_rows($userResult) === 0) {
        if ($userResult) mysqli_free_result($userResult);
        dbDisconnect();
        return false;
    }

    $userData      = mysqli_fetch_assoc($userResult);
    $idUser        = (int)$userData['idUser'];
    $contributions = (int)$userData['contributions'];
    mysqli_free_result($userResult);

    $hasHighContributions = ($contributions > 3);
    $isDirectApply = ($isEditorOrHigher || $hasHighContributions);

    if ($isDirectApply) {
        // update live document text immediately
        $updateQuery = "UPDATE `$dataBaseName`.`page` 
                        SET `content` = '$newContent', `visibility` = '$newVisibility' 
                        WHERE `pageTitle` = '$pageTitle'";
        $success = mysqli_query($GLOBALS['ligacao'], $updateQuery);
        
        if ($success) {
            mysqli_query($GLOBALS['ligacao'], "UPDATE `$dataBaseName`.`auth-basic` SET `contributions` = `contributions` + 1 WHERE `idUser` = $idUser");
        }
    } else {
        // write proposal safely into queue moderation backlog log files
        $insertQuery = "INSERT INTO `$dataBaseName`.`page-changes` (`pageTitle`, `editorId`, `newContent`) 
                        VALUES ('$pageTitle', $idUser, '$newContent')";
        $success = mysqli_query($GLOBALS['ligacao'], $insertQuery);
    }

    //notifications
    if ($success && $isDirectApply) {

        $pageSubscribers = getNotificationEmailsByPage($pageTitle);
        $metaData = getPageMetaData($pageTitle);
        $categorySubscribers = getNotificationEmailsByCategory($metaData['secondaryCategory']);
        $allSubscribers = array_unique(array_merge($pageSubscribers, $categorySubscribers));

        if (!empty($allSubscribers)) {
            $flags[] = FILTER_NULL_ON_FAILURE;
            $serverName = filter_input(INPUT_SERVER, 'SERVER_NAME', FILTER_UNSAFE_RAW, $flags);
            $serverPort = 80;
            $name = webAppName();
            $baseUrl = "http://" . $serverName . ":" . $serverPort;
            $link = $baseUrl . $name . "viewPage.php?pageTitle=" . urlencode($pageTitle);

            $Subject = "Wiki Page Updated: " . $pageTitle;
            $Message = "The page '" . $pageTitle . "' has been updated by " . $username . ".\n\nYou can view the changes here:\n" . $link;

            sendNotificationEmails($allSubscribers, $Subject, $Message);
        }
    }

    dbDisconnect();
    return $success;
}

function sendNotificationEmails($subscribers, $subject, $message) {
    if (empty($subscribers)) {
        return;
    }

    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);
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
                $subscriber,
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

    $rolesQuery = "SELECT `roleLevel`, `friendlyName`, `idRole` 
                   FROM `$dataBaseName`.`auth-roles` 
                   WHERE `roleLevel` <= $userMaxLevel 
                   ORDER BY `roleLevel` ASC";

    $rolesResult = mysqli_query($GLOBALS['ligacao'], $rolesQuery);
    $rolesList = [];

    if ($rolesResult) {
        while ($row = mysqli_fetch_assoc($rolesResult)) {
            $rolesList[] = [
                'roleLevel'    => (int)$row['roleLevel'],
                'friendlyName' => $row['friendlyName'],
                'idRole' => $row['idRole']
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

    $query = "SELECT c.`pageTitle`, c.`editorId`, u.`name`, c.`newContent` 
          FROM `$dataBaseName`.`page-changes` c
          JOIN `$dataBaseName`.`auth-basic` u ON c.`editorId` = u.`idUser`
          WHERE c.`changeId` = $changeId 
          LIMIT 1";
    $result = mysqli_query($GLOBALS['ligacao'], $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $proposal = mysqli_fetch_assoc($result);
        mysqli_free_result($result);

        $pageTitle  = mysqli_real_escape_string($GLOBALS['ligacao'], $proposal['pageTitle']);
        $editorId   = (int)$proposal['editorId'];
        $editorName   = $proposal['name'];
        $newContent = mysqli_real_escape_string($GLOBALS['ligacao'], $proposal['newContent']);

        if ($action === 'accept') {
            $updatePage = "UPDATE `$dataBaseName`.`page` SET `content` = '$newContent' WHERE `pageTitle` = '$pageTitle'";
            if (mysqli_query($GLOBALS['ligacao'], $updatePage)) {
                notifyPageChange($pageTitle, $editorName);
                mysqli_query($GLOBALS['ligacao'], "UPDATE `$dataBaseName`.`auth-basic` SET `contributions` = `contributions` + 1 WHERE `idUser` = $editorId");
                $success = true;
            }
        } else {
            $success = true;
        }
        if ($success) {
            mysqli_query($GLOBALS['ligacao'], "DELETE FROM `$dataBaseName`.`page-changes` WHERE `changeId` = $changeId");
        }
    } else if ($result) {
        mysqli_free_result($result);
    }

    dbDisconnect();
    return $success;
}

function notifyPageChange($pageTitle, $usernameResponsible){
    $pageSubscribers = getNotificationEmailsByPage($pageTitle);
    $metaData = getPageMetaData($pageTitle);
    $categorySubscribers = getNotificationEmailsByCategory($metaData['secondaryCategory']);
    $allSubscribers = array_unique(array_merge($pageSubscribers, $categorySubscribers));

    if (!empty($allSubscribers)) {
        $flags[] = FILTER_NULL_ON_FAILURE;
        $serverName = filter_input(INPUT_SERVER, 'SERVER_NAME', FILTER_UNSAFE_RAW, $flags);
        $serverPort = 80;
        $name = webAppName();
        $baseUrl = "http://" . $serverName . ":" . $serverPort;
        $link = $baseUrl . $name . "viewPage.php?pageTitle=" . urlencode($pageTitle);

        $Subject = "Wiki Page Updated: " . $pageTitle;
        $Message = "The page '" . $pageTitle . "' has been updated by " . $usernameResponsible . ".\n\nYou can view the changes here:\n" . $link;

        sendNotificationEmails($allSubscribers, $Subject, $Message);
    }
}


function getForumDiscussions($primaryCategory = null, $secondaryCategory = null) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $query = "SELECT d.*, u.name as author,
              (SELECT COUNT(*) FROM `$dataBaseName`.`forum_posts` p WHERE p.idDiscussion = d.idDiscussion) - 1 as total_replies
              FROM `$dataBaseName`.`forum_discussions` d
              JOIN `$dataBaseName`.`auth-basic` u ON d.idUser = u.idUser";

    $whereClauses = [];

    if ($primaryCategory !== null && $primaryCategory !== '') {
        $pCatSafe = mysqli_real_escape_string($GLOBALS['ligacao'], $primaryCategory);
        $whereClauses[] = "d.primaryCategory = '$pCatSafe'";
    }

    if ($secondaryCategory !== null && $secondaryCategory !== '') {
        $sCatSafe = mysqli_real_escape_string($GLOBALS['ligacao'], $secondaryCategory);
        $whereClauses[] = "d.secondaryCategory = '$sCatSafe'";
    }

    if (count($whereClauses) > 0) {
        $query .= " WHERE " . implode(" AND ", $whereClauses);
    }

    $query .= " ORDER BY (
        SELECT COALESCE(MAX(p.created_at), d.created_at) 
        FROM `$dataBaseName`.`forum_posts` p 
        WHERE p.idDiscussion = d.idDiscussion
    ) DESC";

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

    $queryDisc = "INSERT INTO `$dataBaseName`.`forum_discussions` (`title`, `slug`, `idUser`, `primaryCategory`, `secondaryCategory`) 
                  VALUES ('$titleSafe', '$slugSafe', $idUserSafe, '$primarySafe', $secondarySafe)";
    
    if (mysqli_query($GLOBALS['ligacao'], $queryDisc)) {
        $idDiscussion = mysqli_insert_id($GLOBALS['ligacao']);

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


function toggleForumLike($idUser, $idPost) {
    dbConnect(ConfigFile);
    $dataBaseName = $GLOBALS['configDataBase']->db;
    mysqli_select_db($GLOBALS['ligacao'], $dataBaseName);

    $idUserSafe = (int)$idUser;
    $idPostSafe = (int)$idPost;
    $action = false;

    $queryCheck = "SELECT 1 FROM `$dataBaseName`.`forum_likes` WHERE `idPost` = $idPostSafe AND `idUser` = $idUserSafe";
    $resultCheck = mysqli_query($GLOBALS['ligacao'], $queryCheck);

    if ($resultCheck && mysqli_num_rows($resultCheck) > 0) {
        $queryDel = "DELETE FROM `$dataBaseName`.`forum_likes` WHERE `idPost` = $idPostSafe AND `idUser` = $idUserSafe";
        if (mysqli_query($GLOBALS['ligacao'], $queryDel)) {
            $action = 'unliked';
        }
    } else {
        $queryIns = "INSERT INTO `$dataBaseName`.`forum_likes` (`idPost`, `idUser`) VALUES ($idPostSafe, $idUserSafe)";
        if (mysqli_query($GLOBALS['ligacao'], $queryIns)) {
            $action = 'liked';
        }
    }

    if ($resultCheck) mysqli_free_result($resultCheck);
    dbDisconnect();
    return $action;
}


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