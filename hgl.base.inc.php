<?php
/*
 *---------------------------------------------------------------
 * DEFINE BASIC
 *---------------------------------------------------------------
 */
    setlocale(LC_ALL, '');    
    setlocale(LC_ALL, 'nl_NL.UTF-8');    

   // $config = parse_ini_file(PATH.'config.ini',true);    
    $config = parse_ini_file(PATH.'config-local.ini',true);    
    
    // Database stuff
    define('FDBH', $config['database']['db_host']);
    define('FDBU', $config['database']['db_user']);
    define('FDBP', $config['database']['db_pass']);
    define('FDBN', $config['database']['db_name']);
    // Email sender
    define('FROM', $config['email']['postmaster']);
    
/*
 *---------------------------------------------------------------
 * Process the only variables we accept and filter them.
 *---------------------------------------------------------------
 */
    define('L1', empty($_GET['h']) ? '' : filter_var($_GET['h'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
    define('L2', empty($_GET['g']) ? '' : filter_var($_GET['g'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
    define('L3', empty($_GET['l']) ? '' : filter_var($_GET['l'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));
    define('L4', empty($_GET['x']) ? '' : filter_var($_GET['x'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH));

/*
 *---------------------------------------------------------------
 * Initiate DB Connection
 *---------------------------------------------------------------
 */
    try {
        $hgl = new PDO('mysql:host='.FDBH.';dbname='.FDBN, FDBU, FDBP);
        $hgl->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        $_SESSION['error'] = 'SQL Fout: ' . $e->getMessage();
    }

/*
 *---------------------------------------------------------------
 * Functions.
 *---------------------------------------------------------------
 */

    function clean_input($var) { 
        $result = filter_var($var, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH); 
        return $result; 
    }

    function validate_email($email, $allowed_domain) {
        $split_email = explode('@', $email);
        $email_domain = array_pop($split_email);
        $check_email = in_array($email_domain, $allowed_domain) ? TRUE : FALSE;
        return $check_email;
    }

    function send_verification_email($email) { 
        // send email to register
        $to        = $email;
        $subject   = 'Toegangsverificatie (hgl.fresch.org)';
        $headers   = array();
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-type: text/plain; charset=iso-8859-1";
        $headers[] = "From: Fresch.org <noreply@fresch.org>";
        $headers[] = "Cc: Actieregio Haaglanden <actieregio.h@makeawishnederland.org>";
        $headers[] = "X-Mailer: PHP/".phpversion();
        $message = 'Volg de link om je authenticatiegegevens bij te werken: http://hgl.fresch.org/?h=verify&x='.bin2hex($email); 

        return mail($to, $subject, $message, implode("\r\n", $headers));    
    }

?>