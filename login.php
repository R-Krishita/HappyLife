<!-- This file handles both displaying the user login form and processing the login credentials. -->

<!-- This file is the main gateway for existing users. It has two jobs:
   1. When a user first navigates to it, it displays the HTML login form.
   2. When the user fills out the form and clicks "Login", it processes the submitted email and password, checks them against the database, and redirects the user to
      their specific dashboard (patient, doctor, or admin). -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/animations.css">  
    <link rel="stylesheet" href="css/main.css">  
    <link rel="stylesheet" href="css/login.css">
        
    <title>Login</title>
 
</head>
<body>
<?php

    session_start();

    $_SESSION["user"]="";
    $_SESSION["usertype"]="";
    
    // Set the new timezone
    date_default_timezone_set('Asia/Kolkata');
    $date = date('d-m-Y');

    $_SESSION["date"]=$date;
    

    //import database
    include("connection.php");

    



    if($_POST){

        $email=$_POST['useremail'];
        $password=$_POST['userpassword'];
        
        $error='<label for="promter" class="form-label"></label>';

        $stmt = $database->prepare("select * from webuser where email= ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows==1){
            $user = $result->fetch_assoc();
            $utype=$user['usertype'];

            $table_map = [
                'p' => ['table' => 'patient', 'email_col' => 'pemail', 'pass_col' => 'ppassword', 'redirect' => 'patient/index.php'],
                'a' => ['table' => 'admin', 'email_col' => 'aemail', 'pass_col' => 'apassword', 'redirect' => 'admin/index.php'],
                'd' => ['table' => 'doctor', 'email_col' => 'docemail', 'pass_col' => 'docpassword', 'redirect' => 'doctor/index.php']
            ];

            if (array_key_exists($utype, $table_map)) {
                $config = $table_map[$utype];
                
                $stmt_checker = $database->prepare("SELECT * FROM {$config['table']} WHERE {$config['email_col']} = ?");
                $stmt_checker->bind_param("s", $email);
                $stmt_checker->execute();
                $result_checker = $stmt_checker->get_result();

                if ($result_checker->num_rows == 1) {
                    $user_data = $result_checker->fetch_assoc();
                    $hashed_password = $user_data[$config['pass_col']];

                    if (password_verify($password, $hashed_password)) {
                        $_SESSION['user'] = $email;
                        $_SESSION['usertype'] = $utype;
                        header('location: ' . $config['redirect']);
                        exit();
                    }
                }
            }

            $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Wrong credentials: Invalid email or password</label>';
            
        }else{
            $error='<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">We cant found any acount for this email.</label>';
        }

    }else{
        $error='<label for="promter" class="form-label">&nbsp;</label>';
    }

    ?>





    <center>
    <div class="container">
        <table border="0" style="margin: 0;padding: 0;width: 60%;">
            <tr>
                <td>
                    <p class="header-text">Welcome Back!</p>
                </td>
            </tr>
        <div class="form-body">
            <tr>
                <td>
                    <p class="sub-text">Login with your details to continue</p>
                </td>
            </tr>
            <tr>
                <form action="" method="POST" >
                <td class="label-td">
                    <label for="useremail" class="form-label">Email: </label>
                </td>
            </tr>
            <tr>
                <td class="label-td">
                    <input type="email" name="useremail" class="input-text" placeholder="Email Address" required>
                </td>
            </tr>
            <tr>
                <td class="label-td">
                    <label for="userpassword" class="form-label">Password: </label>
                </td>
            </tr>

            <tr>
                <td class="label-td">
                    <input type="Password" name="userpassword" class="input-text" placeholder="Password" required>
                </td>
            </tr>


            <tr>
                <td><br>
                <?php echo $error ?>
                </td>
            </tr>

            <tr>
                <td>
                    <input type="submit" value="Login" class="login-btn btn-primary btn">
                </td>
            </tr>
        </div>
            <tr>
                <td>
                    <br>
                    <label for="" class="sub-text" style="font-weight: 280;">Don't have an account&#63; </label>
                    <a href="signup.php" class="hover-link1 non-style-link">Sign Up</a>
                    <br><br><br>
                </td>
            </tr>
                        
                        
    
                        
                    </form>
        </table>

    </div>
</center>
</body>
</html>