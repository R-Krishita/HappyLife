<!--  This file handles the second step of patient registration, creating the user account with a hashed password. -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/animations.css">  
    <link rel="stylesheet" href="css/main.css">  
    <link rel="stylesheet" href="css/signup.css">
        
    <title>Create Account</title>
    <style>
        .container{
            animation: transitionIn-X 0.5s;
        }
    </style>
</head>
<body>
<?php


session_start();

$_SESSION["user"] = "";
$_SESSION["usertype"] = "";

// Set the new timezone
date_default_timezone_set('Asia/Kolkata');
$date = date('Y-m-d'); // Use standard Y-m-d format for database compatibility
$_SESSION["date"] = $date;

// Import database connection
include("connection.php");

$error = '';

if ($_POST) {
    // Retrieve data from session and form post
    $fname = $_SESSION['personal']['fname'];
    $lname = $_SESSION['personal']['lname'];
    $name = $fname . " " . $lname;
    $address = $_SESSION['personal']['address'];
    $nic = $_SESSION['personal']['nic'];
    $dob = $_SESSION['personal']['dob'];
    $email = $_POST['newemail'];
    $tele = $_POST['tele'];
    $newpassword = $_POST['newpassword'];
    $cpassword = $_POST['cpassword'];

    if ($newpassword == $cpassword) {
        // Check if email already exists using a prepared statement
        $sqlmain = "SELECT * FROM webuser WHERE email=?;";
        $stmt = $database->prepare($sqlmain);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Already have an account for this Email address.</label>';
        } else {
            // Securely hash the new password
            $hashed_password = password_hash($newpassword, PASSWORD_DEFAULT);

            // Use a transaction to ensure both inserts succeed or fail together
            $database->begin_transaction();
            try {
                // Insert into the 'patient' table using a prepared statement
                $stmt1 = $database->prepare("INSERT INTO patient(pemail, pname, ppassword, paddress, pnic, pdob, ptel) VALUES (?, ?, ?, ?, ?, ?, ?);");
                $stmt1->bind_param("sssssss", $email, $name, $hashed_password, $address, $nic, $dob, $tele);
                $stmt1->execute();

                // Insert into the 'webuser' table using a prepared statement
                $stmt2 = $database->prepare("INSERT INTO webuser(email, usertype) VALUES (?, 'p');");
                $stmt2->bind_param("s", $email);
                $stmt2->execute();

                // If both queries were successful, commit the transaction
                $database->commit();

                // Set session variables for the new user
                $_SESSION["user"] = $email;
                $_SESSION["usertype"] = "p";
                $_SESSION["username"] = $fname;

                // Redirect to the patient dashboard
                header('Location: patient/index.php');
                exit(); // Always exit after a header redirect

            } catch (Exception $e) {
                // If any query fails, roll back the entire transaction
                $database->rollback();
                // It's good practice to log the actual error for debugging, not show it to the user
                // error_log($e->getMessage());
                $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">An error occurred during registration. Please try again.</label>';
            }
        }
    } else {
        $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Password Confirmation Error! Re-confirm Password.</label>';
    }
} else {
    // If the page is accessed without posting data, set a default empty error
    $error = '<label for="promter" class="form-label"></label>';
}
?>


    <center>
    <div class="container">
        <table border="0" style="width: 69%;">
            <tr>
                <td colspan="2">
                    <p class="header-text">Let's Get Started</p>
                    <p class="sub-text">It's Okey, Now Create User Account.</p>
                </td>
            </tr>
            <tr>
                <form action="" method="POST" >
                <td class="label-td" colspan="2">
                    <label for="newemail" class="form-label">Email: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="email" name="newemail" class="input-text" placeholder="Email Address" required>
                </td>
                
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="tele" class="form-label">Mobile Number: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="tel" name="tele" class="input-text"  placeholder="ex: 0712345678" pattern="[0]{1}[0-9]{9}" >
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="newpassword" class="form-label">Create New Password: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="password" name="newpassword" class="input-text" placeholder="New Password" required>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <label for="cpassword" class="form-label">Conform Password: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td" colspan="2">
                    <input type="password" name="cpassword" class="input-text" placeholder="Conform Password" required>
                </td>
            </tr>
     
            <tr>
                
                <td colspan="2">
                    <?php echo $error ?>

                </td>
            </tr>
            
            <tr>
                <td>
                    <input type="reset" value="Reset" class="login-btn btn-primary-soft btn" >
                </td>
                <td>
                    <input type="submit" value="Sign Up" class="login-btn btn-primary btn">
                </td>

            </tr>
            <tr>
                <td colspan="2">
                    <br>
                    <label for="" class="sub-text" style="font-weight: 280;">Already have an account&#63; </label>
                    <a href="login.php" class="hover-link1 non-style-link">Login</a>
                    <br><br><br>
                </td>
            </tr>

                    </form>
            </tr>
        </table>

    </div>
</center>
</body>
</html>