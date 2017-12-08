<?  /* Start A Session */
    session_start();

    /* Load Basics */
    //define('PATH', '/sites/fresch.org/private/');
    define('PATH', '/Users/fresch/Sites/private/');
        include_once(PATH.'hgl.base.inc.php'); 

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="favicon.ico">

    <title><?   if(!$_SESSION['auth']) { 
                    echo 'Log in op Make-A-Wish Haaglanden'; 
                } else { 
                    echo $_SESSION['name'] . ' | Make-A-Wish Haaglanden'; 
                } ?> </title>

    <!-- Stylesheet !-->
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/css/bootstrap-datepicker.min.css">
    <style>
        .popover-content {
            width: 200px;
        }
    </style>
    
    <!-- Scripts !-->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
       
</head>

<body style="padding-top: 70px;">
    
        <!-- Contents !-->
<?      switch($_SESSION['auth']) {
            case TRUE:
                include_once(PATH.'hgl.switch.inc.php');
                break;
            case FALSE:
                include_once(PATH.'hgl.auth.inc.php');
            break;
        } ?>

<div class="container">
    <div class="row">
        <!-- Alert Control -->
        <div class="col-md-4 col-md-offset-4">
            <p>&nbsp;</p>
<?      if (!empty($_SESSION['error'])) { 
            echo '<div class="alert alert-danger alert-dismissible" role="alert">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    '. $_SESSION['error'] . '
                  </div>';
        } 
    
        if (!empty($_SESSION['info'])) {
            echo '<div class="alert alert-warning alert-dismissible" role="alert">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    '. $_SESSION['info'] . '
                  </div>';
        } ?>   
        
        </div>
    </div>
</div>

</body>
</html>



