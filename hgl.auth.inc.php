<?  

$authForm = '';

switch(L1) {
    
    /* Verify External Request */
    case 'verify':
        
        unset($_SESSION['error']);
        unset($_SESSION['info']);
        
        if (!empty(L4)) {
            
            $validate_email = hex2bin(clean_input(L4));
    
            if (!validate_email($validate_email, $config['hgl_auth']['domain'])) {
                $_SESSION['error'] = 'Fout! - E-mail is van verkeerde domein!';
            } else {
    
                try {
                    $ins = $hgl->prepare('INSERT IGNORE INTO hgl_users (user_email) VALUES (:user_email)');
                    $ins->execute(array(':user_email' => $validate_email)); 
                    
                    $get = $hgl->prepare('SELECT user_name, user_email FROM hgl_users WHERE user_email = :user_email;');
                    $get->execute(array(':user_email' => $validate_email));

                    if ($get->rowCount() > 0) {
                        
                        $data = $get->fetch(PDO::FETCH_OBJ);
                        
                        $_SESSION['email'] = $data->user_email;
                        $_SESSION['name'] = $data->user_name;
                        
                        $AuthForm = 'U';
                                                
                    }
                
                } catch(PDOException $e) {
                    
                    $_SESSION['error'] = 'SQL Fout: ' . $e->getMessage();
                }

            }
        
        } else {
            
            $_SESSION['error'] = 'Fout! - E-mail is van verkeerde domein!';
        
        }
        
        break;
        
    /* Validate Login */
    case 'login':
        
        unset($_SESSION['error']);
        unset($_SESSION['info']);
              
        if (!empty($_POST['AuthLogin'])) {
        
            $user_email = clean_input($_POST['AuthLogin']);
            $user_password = clean_input($_POST['AuthPass']);
            
            if (!validate_email($user_email, $config['hgl_auth']['domain'])) {
            
                $_SESSION['error'] = 'Fout! - E-mail is van verkeerde domein!';
            
            } else {
                
                if ($_POST['AuthReset'] == 'on') { 
                
                    $_SESSION['info'] = send_verification_email($user_email) ? 'Verificatielink verstuurd' : 'Fout!';
                    
                } else {

                    try {
                        
                        $val = $hgl->prepare('SELECT user_name, user_password_hash FROM hgl_users WHERE user_email = :user_email');
                        $val->execute(array(':user_email' => $user_email));

                        if ($val->rowCount() > 0) {
                            
                            $data = $val->fetch(PDO::FETCH_OBJ);
                            $_SESSION['email'] = $user_email;
                            $_SESSION['name']  = $data->user_name;

                            if (password_verify($user_password,$data->user_password_hash)) {
                                
                                $_SESSION['auth'] = TRUE;
                                header('Location: http://hgl.fresch.org/');
                                exit();        
                        
                            } else {
                                
                                $_SESSION['error'] = 'Fout! - Incorrect wachtwoord.';
                            }
                    
                        } else {
                       
                            $_SESSION['error'] = 'Fout! - Onbekend e-mailadres.';
                        
                        }
                    
                    } catch(PDOException $e) {
                        
                        $_SESSION['error'] = 'SQL Fout: ' . $e->getMessage();
                    
                    }
                
                }
                
            }
        
        } else { 
            
             $_SESSION['error'] = 'Fout! - Een e-mailadres is verplicht!';
        
        }
        
        break;
        
        /* Process Updated Information */
        case 'set':
        
            unset($_SESSION['error']);
            unset($_SESSION['info']);
                  
            if (!empty($_POST['AuthLogin'])
                && !empty($_POST['AuthPass'])
                && !empty($_POST['Volunteer'])) {
        
                    $user_email = clean_input($_POST['AuthLogin']);
                    $user_password = clean_input($_POST['AuthPass']);
                    $user_name = clean_input($_POST['Volunteer']);
                
                    if (!validate_email($user_email, $config['hgl_auth']['domain'])) {
            
                        $_SESSION['error'] = 'Fout! - E-mail is van verkeerde domein!';
            
                    } else {

                        $uph = password_hash($user_password, PASSWORD_DEFAULT);
            
                        try {
                            
                            $upd = $hgl->prepare('UPDATE hgl_users SET user_name = :user_name, 
                                                         user_password_hash = :user_password_hash 
                                                  WHERE user_email = :user_email');
                        
                            if ($upd->execute( array(   ':user_name' => $user_name,
                                                        ':user_password_hash' => $uph,
                                                        ':user_email' => $user_email ))) {
                            
                                $_SESSION['info'] = 'Je gegevens zijn bijgewerkt! - Graag inloggen';
                                header('Location: http://hgl.fresch.org/');
                                exit(); 
                        
                            }
                        
                        } catch(PDOException $e) {
                            
                            $_SESSION['error'] = 'SQL Fout: ' . $e->getMessage();
                        }
                    
                    }
            
            } else {
                
                 $AuthForm = 'U';
                 $_SESSION['error'] = 'Fout! - Alle velden zijn verplicht!';
            
            }
            
            break;
    }

?>

<div class="container">
    <div class="row">
        
<!-- Login Box -->
<div class="col-md-4 col-md-offset-4">
    <img src="images/logo.png" class="img-responsive" alt="Responsive image" style="padding:10px;">

    <!-- Authentication Form !-->
    <form id="AuthForm" action="?h=<? if ($AuthForm =='U') { echo 'set'; } else { echo 'login'; } ?>" method="post">
        
        <!-- Auth_Login !-->
        <div class="form-group <? if(!empty($_POST) && empty($_POST['AuthLogin'])) { echo 'has-error'; } ?>">
            <div class="input-group"> 
                <span class="input-group-addon"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span></span>
                <input type="email" class="form-control" name="AuthLogin" placeholder="E-mailadres..." 
                       value="<? echo $_SESSION['email'] ? $_SESSION['email'] : $_POST['AuthLogin']; ?>" required>
            </div>        
        </div>
        
        <!-- Auth_Pass !-->
        <div class="form-group <? if(!empty($_POST) && empty($_POST['AuthPass'])) { echo 'has-error'; } ?>">
            <div class="input-group"> 
                <span class="input-group-addon"><span class="glyphicon glyphicon-flash" aria-hidden="true"></span></span>
                <input type="password" class="form-control" name="AuthPass" placeholder="Wachtwoord...">
            </div>
        </div>
         
<?  if ($AuthForm == 'U') { ?>
        
        <!-- Volunteer !-->
        <div class="form-group <? if(!empty($_POST) && empty($_POST['Volunteer'])) { echo 'has-error'; } ?>">
            <div class="input-group"> 
                <span class="input-group-addon"><span class="glyphicon glyphicon-user" aria-hidden="true"></span></span>
                <input type="text" class="form-control" name="Volunteer" placeholder="Roepnaam..." 
                       value="<? echo $_SESSION['name'] ? $_SESSION['name'] : $_POST['Volunteer']; ?>" required>
            </div>        
        </div>

<?  } else {  ?>
        
        <div class="checkbox">
            <label>
                <input type="checkbox" name="AuthReset"> Reset Wachtwoord
            </label>
        </div>    

<?  }  ?>
        <button class="btn btn-primary btn-block" type="submit">Verwerk</button>
    </form>
</div>
        
    </div>
</div>