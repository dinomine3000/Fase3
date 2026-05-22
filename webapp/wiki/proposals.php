<?php
require_once( "../../Lib/lib.php" );
require_once( "../../Lib/db.php" );

if ( !isset( $_SESSION ) ) {
    session_start();
}
// Enforce authentication safety constraints
if (!authorizeUserByLevel($_SESSION['username'] ?? '', 'organizer')) {
    header("Location: ../index.php");
    exit();
}

// Handle moderation actions POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['changeId'])) {
    $changeId = (int)$_POST['changeId'];
    $action = $_POST['action'] === 'accept' ? 'accept' : 'deny';
    
    moderateProposal($changeId, $action);
    
    // Refresh page state
    header("Location: proposals.php");
    exit;
}

$proposals = getAllProposals();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Wiki Production Backlog Backlogs</title>
</head>
<body>

    <a href="../index.php">← Return to Home</a>
    <h2>Wiki Updates Content Review Queue</h2>

    <?php if (empty($proposals)): ?>
        <div>
            <h3>No pending review proposals found.</h3>
            <p>All user documentation edits have been processed.</p>
        </div>
    <?php else: ?>
        <?php foreach ($proposals as $p): ?>
            <div>
                <div>
                    <strong>Target Document:</strong> <code><?php echo htmlspecialchars($p['pageTitle']); ?></code>
                    <br>
                    <small>Submitted by user: <em><?php echo htmlspecialchars($p['editorName'] ?? 'Unknown User'); ?></em></small>
                </div>

                <div>
                    <!-- Live Content Column Without Markdown -->
                    <div class="diff-box">
                        <h4>Live Production Text</h4>
                        <?php 
                            $cleanCurrent = strip_tags($p['currentContent'] ?? '[Document is new - no existing text]');
                            echo htmlspecialchars($cleanCurrent); 
                        ?>
                    </div>

                    <!-- Proposed Content Column Without Markdown -->
                    <div class="diff-box">
                        <h4>Proposed Modification Update</h4>
                        <?php 
                            $cleanNew = strip_tags($p['newContent']);
                            echo htmlspecialchars($cleanNew); 
                        ?>
                    </div>
                </div>

                <!-- Control Buttons Form -->
                <form method="POST" onsubmit="return confirm('Are you sure you want to proceed with this moderation action?');">
                    <input type="hidden" name="changeId" value="<?php echo $p['changeId']; ?>">
                    <button type="submit" name="action" value="accept" class="btn btn-accept">Accept Revision</button>
                    <button type="submit" name="action" value="deny" class="btn btn-deny">Reject Proposal</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>