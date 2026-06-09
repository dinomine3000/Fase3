<?php
require_once( "../../Lib/wikiLib.php" );
include_once("../../Lib/extendedParsedown.php");
include_once("../../Lib/db.php");
//Display dos erros
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); 

if (!isset($_SESSION)) {
    session_start();
}
$isLoggedIn = isset($_SESSION['id']);
$clientName = $isLoggedIn ? $_SESSION['username'] : null;

$flags[] = FILTER_NULL_ON_FAILURE;

//if no name given, return home
$pageUsername = filter_input( INPUT_GET, 'user', FILTER_UNSAFE_RAW, $flags);
if(!isset($pageUsername)){
    header("Location: ../index.php");
    exit();
}

// Handle form submission to change role. only moderators can do so for other people below them
$submitted_role = filter_input( INPUT_POST, 'new_role', FILTER_UNSAFE_RAW, $flags);
$isModerator = authorizeUserByLevel($clientName, 'moderator');
$canChangeRoles = $isLoggedIn && $isModerator && $clientName !== $pageUsername;
if(isset($submitted_role) && $canChangeRoles){
    $rolePage = getUserRoleInfo($pageUsername);
    $roleClient = $clientName == null ? 0 : getUserRoleInfo($clientName);
    if ($rolePage['roleLevel'] < $roleClient['roleLevel']) {
        changeUserInfo($pageUsername, "idRole", $submitted_role);
    }
}

//handle form submission to change bio. client and page must be the same User
$newBio = filter_input( INPUT_POST, 'new_bio', FILTER_UNSAFE_RAW, $flags);
if (isset($newBio) && $isLoggedIn && $pageUsername === $clientName) {
    //limit newbio size to 256 on server side.
    changeUserInfo($pageUsername, "bio", mb_substr($newBio, 0, 256, 'UTF-8'));
}

//handle form submission to change banned status. client and page can not be the same User. must be a moderator or higher. target cant be higher
$newBan = filter_input( INPUT_POST, 'banning', FILTER_UNSAFE_RAW, $flags);
if ($isModerator && isset($newBan) && $pageUsername !== $clientName) {
    
    $rolePage = getUserRoleInfo($pageUsername);
    $roleClient = $clientName == null ? 0 : getUserRoleInfo($clientName);
    if ($rolePage['roleLevel'] < $roleClient['roleLevel']) {
        changeUserInfo($pageUsername, "isBanned", $newBan);
    }
}

//if name given doesnt exist, return home
//if the user doesnt exist, the function above shouldnt crash. so now we have the updated data (for bio and role)
$user = getUserInfo($pageUsername);
if($user === null || !isset($user)){
    header("Location: ../index.php");
    exit();
} 
$user['role'] = getUserRoleInfo($pageUsername);

$canBan = $isModerator && $pageUsername !== $clientName && $user['role']['roleLevel'] < getUserRoleInfo($clientName)['roleLevel'];

// 2) Get roles up to but excluding the viewed user's current role
$availableRoles = [];
if ($canChangeRoles) {
    $rawRoles = getAvailableRolesUpToUser($pageUsername);
    foreach ($rawRoles as $r) {
        if ($r['friendlyName'] !== $user['role']['friendlyName']) {
            $availableRoles[] = $r;
        }
    }
}

// 3) Check if user is viewing their own profile
$isOwnProfile = $isLoggedIn && ($clientName === $pageUsername);
$status_label = $user['isBanned'] ? 'Banned' : ($user['active'] ? 'Active' : 'Unverified');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile — <?php echo htmlspecialchars($pageUsername); ?></title>
</head>
<body>

    <header>
    <a href="../home.php">← Go Back</a>
        <form id="userSearch" method="GET" action="">
            <input type="search" name="user" id="userSearchbar">
            <button type="submit">Search</button>
        </form>
        <div id="autocomplete-suggestions"></div>
        <script src="../scripts/ajaxHandler.js"></script>
        <script>
            document.getElementById('userSearchbar').addEventListener('input', function() {
                searchUsers(this.value);
            });
        </script>
        <h1>User Profile — <?php echo htmlspecialchars($pageUsername); ?></h1>
        <div>
            <div id="bio-view" style="display: block;">
                <?php echo (new ExtendedParsedown())->text($user['bio']); ?>
                <?php if ($isOwnProfile): ?>
                    <button type="button" id="edit-bio-btn">Edit Bio</button>
                <?php endif; ?>
            </div>

                
            <?php if ($isOwnProfile):?>
                <form id="bio-edit-form" method="POST" action="?user=<?php echo urlencode($pageUsername); ?>" style="display: none;">
                    <!-- limit newbio size to 256 on client side. -->
                    <textarea maxlength=256 name="new_bio" rows="4" cols="50"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                    <br>
                    <button type="submit">Submit</button>
                    <button type="button" id="cancel-bio-btn">Cancel</button>
                </form>
            <?php 
                echo '<script src="./js/profile.js"></script>'; 
            endif; ?>
        </div>
    </header>

    <main>

        <section>
            <h2>Account Overview</h2>
            <dl>
                <dt>Username</dt>
                <dd><?php echo htmlspecialchars($pageUsername); ?></dd>

                <dt>Role</dt>
                <dd><?php echo htmlspecialchars($user['role']['friendlyName']); ?></dd>

                <dt>Account Status</dt>
                <dd><?php echo $status_label; ?></dd>

                <dt>Wiki Contributions</dt>
                <dd><?php echo (int)$user['contributions']; ?></dd>
            </dl>

            <?php if ($canBan): ?>
                <hr>
                <form method="POST" action="?user=<?php echo urlencode($pageUsername); ?>">
                    <input type="hidden" name="banning" value="<?php echo $user['isBanned'] ? 0 : 1; ?>">
                    <button type="submit"><?php echo ($user['isBanned'] ? "Unban $pageUsername" : "Ban $pageUsername")?></button>
                </form>
            <?php endif; ?>
        </section>

        <?php if ($canChangeRoles): ?>
            <hr>

            <section>
                <h2>Change User Role</h2>
                <form method="POST" action="?user=<?php echo urlencode($pageUsername); ?>">
                    <fieldset>
                        <legend>Assign a new role to this user</legend>
                        <label for="new_role">Select Role:</label>
                        <select name="new_role" id="new_role">
                            <?php foreach ($availableRoles as $roleName): ?>
                                <option value="<?php echo htmlspecialchars($roleName['idRole']); ?>">
                                    <?php echo htmlspecialchars($roleName['friendlyName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit">Update Role</button>
                    </fieldset>
                </form>
            </section>
        <?php endif; ?>

    </main>

    <footer>
        <small>Profile page for <?php echo htmlspecialchars($pageUsername); ?></small>
    </footer>

</body>
</html>