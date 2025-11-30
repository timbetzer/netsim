<?php
require_once 'login.inc.php';

$login_error = "";

// Handle form submission
if (!empty($_POST['reg_username']) && !empty($_POST['reg_password']) && !empty($_POST['confirm_password'])) {
    $username = trim($_POST['reg_username']);
    $password = $_POST['reg_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if username is at least 5 characters
    if (strlen($username) < 5) {
        $login_error = "Nutzername muss mindestens 5 Zeichen lang sein";
    }
    // Check if passwords match
    elseif ($password !== $confirm_password) {
        $login_error = "Passwörter sind nicht gleich";
    } else {
        // Check if username exists
        $userq = $db->prepare("SELECT id FROM user WHERE name = :name");
        $userq->bindValue(':name', $username, SQLITE3_TEXT);
        $res = $userq->execute();
        $existingUser = $res->fetchArray(SQLITE3_ASSOC);

        if ($existingUser) {
            $login_error = "Dieser Nutzername ist belegt. Bitte wähle einen Neuen.";
        } else {
            // Insert new user
            $q = $db->prepare("INSERT INTO user (name, password) VALUES (:name, :password)");
            $q->bindValue(':name', $username, SQLITE3_TEXT);
            $q->bindValue(':password', password_hash($password, PASSWORD_DEFAULT), SQLITE3_TEXT);

            if ($q->execute()) {
                // Retrieve newly inserted user ID
                $userq = $db->prepare("SELECT id FROM user WHERE name = :name");
                $userq->bindValue(':name', $username, SQLITE3_TEXT);
                $res = $userq->execute();
                $row = $res->fetchArray(SQLITE3_ASSOC);

                if ($row) {
                    $_SESSION['cs4g_user_id'] = $row['id'];
                    header('Location: ./');
                    exit();
                } else {
                    $login_error = "Registrierung erfolgreich, aber der Login ist fehlgeschlagen, bitte probiere erneut dich anzumelden.";
                }
            } else {
                $login_error = "Fehler beim Registrieren, bitte probiere es später wieder.";
            }
        }
    }
}

include 'header.inc.php';
?>

<h3>Registrierung</h3>

<p>Ein Benutzerkonto verfolgt nur deinen Fortschritt durch die Levels. Beachte, dass sich Netsim in der <strong>Beta</strong>-Phase befindet, sodass einige Daten zurückgesetzt werden können.</p>


<!-- Styled Error Message -->
<?php if (!empty($login_error)) : ?>
    <div class="ui-widget">
        <div class="ui-state-error ui-corner-all" style="padding: 0.7em;">
            <p>
                <span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
                <?= htmlspecialchars($login_error) ?>
            </p>
        </div>
    </div>
<?php endif; ?>

<!-- Styled Registration Form -->
<form method="post" action="register.php" onsubmit="return validateForm();" class="ui-widget-content ui-corner-all" style="padding: 15px; max-width: 300px;">
    <p><label for="reg_username">Nutzername:</label><br>
        <input type="text" name="reg_username" id="reg_username" required minlength="5" class="ui-widget-content ui-corner-all" style="width: 100%; padding: 5px;"></p>

    <p><label for="reg_password">Passwort:</label><br>
        <input type="password" name="reg_password" id="reg_password" required class="ui-widget-content ui-corner-all" style="width: 100%; padding: 5px;"></p>

    <p><label for="confirm_password">Passwort bestätigen:</label><br>
        <input type="password" name="confirm_password" id="confirm_password" required class="ui-widget-content ui-corner-all" style="width: 100%; padding: 5px;"></p>

    <p><input type="submit" value="Registrieren" class="ui-button ui-widget ui-corner-all"></p>
</form>

<div style="height:150px;"></div>

<!-- Validation Script -->
<script>
    function validateForm() {
        let password = document.getElementById('reg_password').value;
        let confirmPassword = document.getElementById('confirm_password').value;
        if (password !== confirmPassword) {
            alert("Passwords do not match.");
            return false;
        }
        return true;
    }

    document.getElementById('reg_username').focus();
</script>

<?php include 'footer.inc.php'; ?>
