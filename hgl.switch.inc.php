<!-- Navigation -->    
<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="/?">
                <img alt="Brand" src="/images/logo.png" height="95%">
            </a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li <? if(L1 == 'dashboard') { echo 'class="active"'; } ?>><a href="/?h=dashboard">Dashboard</a></li>
                <li <? if(empty(L1) or (L1 == 'statiegeldacties')) { echo 'class="active"'; } ?>><a href="/?h=statiegeldacties">Statiegeldacties</a></li>
                <li <? if(L1 == 'supermarkten') { echo 'class="active"'; } ?>><a href="/?h=supermarkten">Supermarkten</a></li>
            </ul>
            <p class="navbar-text navbar-right">Hallo <b><? echo $_SESSION['name']; ?></b></p>
        </div>
        <!--/.navbar-collapse -->
    </div>
</nav>
    
<!-- Main Container -->    
<div class="container">
    <div class="row">
        
<? 
    switch(L1) {
        case 'statiegeldacties':
            include(PATH.'hgl.sg.start.inc.php');
            break;
        case 'supermarkten':
            include(PATH.'hgl.sm.start.inc.php');
            break;
        case 'dashboard':
            include(PATH.'hgl.start.inc.php');
            break;            
        default:
            include(PATH.'hgl.sg.start.inc.php');
            break;
    }
        
?>  
    </div>
</div>