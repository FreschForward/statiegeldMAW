<?php

    switch(L2) {
            
        case 'supermarkt':
            
            unset($_SESSION['error']);
            unset($_SESSION['info']);

            try {
                
                $sgactions = 'SELECT a.id, a.s_id, k.keten, s.naam, s.plaats, a.action_id, 
                                     a.start_date, a.end_date, a.earnings, v.status, a.volunteer, a.notes,
                                     datediff(a.end_date, a.start_date) as days,
                                     s.telefoon, c.name, c.phone, c.email
                              FROM hgl_sga AS a JOIN hgl_sga_status v on v.id = a.status
                                                JOIN hgl_smv AS s ON s.id = a.s_id
                                                LEFT JOIN hgl_smv_cf AS c ON c.s_id = s.id
                                                JOIN hgl_smk AS k on k.id = s.k_id
                              WHERE s.id = '. L3 .'
                              ORDER BY a.status ASC';

                $get_sg = $hgl->prepare($sgactions);
                $get_sg->execute();
                $get_sg->setFetchMode(PDO::FETCH_ASSOC); 
            
            } catch(PDOException $e) {
                
                $_SESSION['error'] = 'SQL Fout: ' . $e->getMessage();
            }
            
            break; 
            
        case 'wijzigen':
            
            unset($_SESSION['error']);
            unset($_SESSION['info']);
            
            $aID = !empty($_POST['AID']) ? clean_input($_POST['AID']) : L3;
            
            if (!empty($_POST['AID'])) {
            
                $aSupermarkt = clean_input($_POST['aSupermarkt']);
                $aStatus = clean_input($_POST['aStatus']);
                $aStartdate = clean_input($_POST['aStartdate']);
                $aEnddate = clean_input($_POST['aEnddate']);
                $aVolunteer = clean_input($_POST['aVolunteer']);
                $aNotes = clean_input($_POST['aNotes']);
                $aAction = clean_input($_POST['aAction']);
                $aEarnings = clean_input($_POST['aEarnings']);

                try {
                    $upd = $hgl->prepare('UPDATE hgl_sga 
                                             SET action_id = :aAction,
                                                 start_date = :aStartdate,
                                                 end_date = :aEnddate,
                                                 earnings = :aEarnings,
                                                 status = :aStatus,
                                                 volunteer = :aVolunteer,
                                                 notes = :aNotes
                                           WHERE id = :aID');
                
                    $upd->execute(array(':aAction' => $aAction,
                                        ':aStartdate' => $aStartdate,
                                        ':aEnddate' => $aEnddate, 
                                        ':aEarnings' => $aEarnings,
                                        ':aStatus' => $aStatus,
                                        ':aVolunteer' => $aVolunteer,
                                        ':aNotes' => $aNotes,
                                        ':aID' => $aID));
                    
                    $_SESSION['info'] = 'Statiegeldactie is bijgewerkt!';
                    
                } catch(PDOException $e) {
                    
                    $_SESSION['error'] = 'SQL Fout: ' . $e->getMessage();
                
                }
                                      
            } 
                                      
        break;
        
        case 'toevoegen':
            
            unset($_SESSION['error']);
            unset($_SESSION['info']);
                        
            if (!empty(L3)) {
            
                try {
                    $ins = $hgl->prepare('INSERT INTO hgl_sga (s_id, status, volunteer) VALUES (:sID, 1, :volunteer)');
                    $ins->execute(array(':sID' => L3,
                                        ':volunteer' => $_SESSION['name']));
                    
                    $aID = $hgl->lastInsertId();
                    header('Location: http://hgl.fresch.org/?k=statiegeldacties&l=wijzigen&l='. $aID);
                    exit();

                } catch(PDOException $e) {
                    
                    $_SESSION['error'] = 'SQL Fout: ' . $e->getMessage();
                }
                                      
            } 
                                      
        break;
            
        default:

            unset($_SESSION['error']);
            unset($_SESSION['info']);
            
            $search_filter = clean_input($_POST['search_filter']);
            $status_filter = clean_input($_POST['status_filter']);
            $date_filter = clean_input($_POST['date_filter']);
            $place_filter = clean_input($_POST['place_filter']);
            $volunteer_filter = clean_input($_POST['volunteer_filter']);
            
            try {
                
                $sgactions = 'SELECT a.id, a.s_id, k.keten, s.naam, s.plaats, a.action_id, 
                                     a.start_date, a.end_date, a.earnings, v.status, a.volunteer, a.notes,
                                     datediff(a.end_date, a.start_date) as days,
                                     s.telefoon, c.name, c.phone, c.email
                              FROM hgl_sga AS a JOIN hgl_sga_status v on v.id = a.status
                                                JOIN hgl_smv AS s ON s.id = a.s_id
                                                LEFT JOIN hgl_smv_cf AS c ON c.s_id = s.id
                                                JOIN hgl_smk AS k on k.id = s.k_id';
                
                if (!empty($search_filter)) {
                    
                    $sgactions .= ' WHERE lower(k.keten) LIKE lower("%'. $search_filter .'%") 
                                    OR lower(v.status) LIKE lower("%'. $search_filter .'%") 
                                    OR lower(s.plaats) LIKE lower("%'. $search_filter .'%")
                                    OR lower(s.naam) LIKE lower("%'. $search_filter .'%") 
                                    OR lower(a.volunteer) LIKE lower("%'. $search_filter .'%") 
                                    OR lower(a.notes) LIKE lower("%'. $search_filter .'%")';
                
                } elseif (!empty($status_filter)) {
                
                    $sgactions .= ' WHERE a.status =' . $status_filter;
                
                } elseif (!empty($date_filter)) {
                   
                    $sgactions .= ' WHERE "' . $date_filter . '" BETWEEN a.start_date AND a.end_date';
                
                } elseif (!empty($place_filter)) {
                    
                    $sgactions .= ' WHERE lower(s.plaats) LIKE lower("%'. $place_filter .'%")';
                
                } elseif (!empty($volunteer_filter)) {
                    
                    $sgactions .= ' WHERE lower(a.volunteer) LIKE lower("%'. $volunteer_filter .'%")';
                
                } else {
                    
                    $sgactions .= '  WHERE a.status < 6';
                    
                }
            
                $sgactions .= ' ORDER BY a.status ASC';
                $get_sg = $hgl->prepare($sgactions);
                $get_sg->execute();
                $get_sg->setFetchMode(PDO::FETCH_ASSOC); 
            
            } catch(PDOException $e) {
                
                $_SESSION['error'] = 'SQL Fout: ' . $e->getMessage();
            }
                        
            break;     
    }

?>

<div class="col-md-2">
    <div class="list-group">
        <a href="/?h=statiegeldacties" class="list-group-item active">
            <span class="glyphicon glyphicon-home" aria-hidden="true"></span>
            &nbsp; Statiegeldacties</a>
    </div>

<?  $pchk = array('wijzigen', 'toevoegen');
    if (!in_array(L2,$pchk)) { ?>
        
    <!-- Search Filters !-->
    <!-- Date Search !-->
    <div class="form-group">
        <h6>Datum <abbr title="Datum ligt op of tussen begin en einddatum.">zoeken</abbr></h6>
        <form action="/?h=statiegeldacties" method="post" id="dp">
        <div class="input-group date" id="datePicker">
            <input type="text" class="form-control" placeholder="Datum" name="date_filter" 
                   value="<? if(isset($date_filter)) { echo $date_filter; } ?>" />
            <span class="input-group-btn">
                <button class="btn btn-default" type="submit"><span class="glyphicon glyphicon-calendar"></span></button>
            </span>
        </div>
        </form>
    </div>
    
    <!-- Free Search !-->
    <div class="form-group">
        <h6>Vrij <abbr title="Er wordt gezocht op keten, vestiging, actienummer en notities.">zoeken</abbr></h6>
        <form action="/?h=statiegeldacties" method="post">
        <div class="input-group" id="searchForm">
            <input type="text" class="form-control" placeholder="Zoekterm" name="search_filter" 
                   value="<? if(isset($search_filter)) { echo $search_filter; } ?>" id="sf">
            <span class="input-group-btn">
                <button class="btn btn-default" type="submit"><span class="glyphicon glyphicon-search"></span></button>
            </span>
        </div>
        </form>
    </div>

    <!-- Status Filter !-->
    <div class="form-group">
        <h6>Filter op Status</h6>
        <form action="/?h=statiegeldacties" method="post">
        <div id="filterForm">
            <select name="status_filter" class="form-control" onchange="this.form.submit()">
                <option value="">Kies een Status...</option>
                <option disabled>──────────</option>
        <?  try {   $get_status = $hgl->prepare('SELECT id, status FROM hgl_sga_status ORDER BY id ASC');
                    $get_status->execute();
                    $get_status->setFetchMode(PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                $_SESSION['error'] = 'SQL Fout: ' . $e->getMessage();
            }

            while($status = $get_status->fetch()) {   
            
                if(isset($status_filter) && ($status_filter == $status['id'])) {
                    echo '<option value="'. $status['id'] .'" selected>' . $status['status'] .'</option>';
                } else {
                    echo '<option value="'. $status['id'] .'">' . $status['status'] .'</option>';
                }
            } 
        ?>
            </select>
        </div>
        </form>
    </div>
    
    <!-- Places Filter !-->
    <div class="form-group">
        <h6>Filter op Plaats</h6>
        <form action="/?h=statiegeldacties" method="post">
        <div id="filterForm">
            <select name="place_filter" class="form-control" onchange="this.form.submit()">
                <option value="">Kies een Plaats...</option>
                <option disabled>──────────</option>
        <?  try {   $get_places = $hgl->prepare('SELECT DISTINCT plaats FROM hgl_smv WHERE plaats <> "" ORDER BY plaats ASC');
                    $get_places->execute();
                    $get_places->setFetchMode(PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                $_SESSION['error'] = 'SQL Fout: ' . $e->getMessage();
            }

            while($places = $get_places->fetch()) {   
            
                if(isset($place_filter) && ($place_filter == $places['plaats'])) {
                    echo '<option selected>' . $places['plaats'] .'</option>';
                } else {
                    echo '<option>' . $places['plaats'] .'</option>';
                }
            } 
        ?>
            </select>
        </div>
        </form>
    </div>
    
    <!-- Volunteer Filter !-->
    <div class="form-group">
        <h6>Filter op Vrijwilliger</h6>
        <form action="/?h=statiegeldacties" method="post">
        <div id="filterForm">
            <select name="volunteer_filter" class="form-control" onchange="this.form.submit()">
                <option value="">Kies een Vrijwilliger...</option>
                <option disabled>──────────</option>
                <option value="?">Vrijwilligerloos</option>
        <?  try {   $get_volunteer = $hgl->prepare('SELECT DISTINCT user_name FROM hgl_users ORDER BY user_name ASC');
                    $get_volunteer->execute();
                    $get_volunteer->setFetchMode(PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                $_SESSION['error'] = 'SQL Fout: ' . $e->getMessage();
            }

            while($volunteer = $get_volunteer->fetch()) {   
            
                if(isset($volunteer_filter) && ($volunteer_filter == $volunteer['user_name'])) {
                    echo '<option selected>' . $volunteer['user_name'] .'</option>';
                } else {
                    echo '<option>' . $volunteer['user_name'] .'</option>';
                }
            } 
        ?>
            </select>
        </div>
        </form>
    </div>

<?  }   ?>
</div>

<div class="col-md-10">

<?  if (!in_array(L2,$pchk)) { ?>

        <div class="table-responsive">
        <table class="table table-hover table-condensed">
            <thead>
                <tr>
                    <th></th>
                    <th>Supermarkt</th>
                    <th></th>
                    <th>Status</th>
                    <th></th>
                    <th>Periode</th>
                    <th></th>
                    <th>Contactpersoon</th>
                </tr>
            </thead>
            <tbody>

<?  while($show = $get_sg->fetch()) { 

        echo '  <tr>
                    <td><a href="/?h=statiegeldacties&g=wijzigen&l='. $show['id'] .'">
                            <span class="glyphicon glyphicon-edit" aria-hidden="true"></span>
                        </a></td>
                    <td>' . $show['keten'] .' - ' . $show['naam'];
        echo '<br /><span class="label label-default">' . $show['volunteer'] . '</span>';
        echo ' </td><td><a href="#/" data-toggle="popover" tabindex="0" 
                           data-trigger="focus hover" title="Notities" data-content="' . $show['notes'] .'">
                            <span class="glyphicon glyphicon-comment" aria-hidden="true"></span></a>
               </td><td>' . $show['status'];
        
            if ($show['action_id'] > 0 ) { 
                echo '<br /><span class="label label-primary">'. $show['action_id'] .'</span>';
            }
    
        echo ' </td><td><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span>
               </td><td>';
                if (!empty($show['start_date']) and ($show['start_date'] <> '0000-00-00')) { 
                    $sdt = explode('-', $show['start_date']);
                    echo 'van <span class="text-success">'. strftime("%A %e %B %Y", mktime(0, 0, 0, $sdt[1], $sdt[2], $sdt[0])) .'</span>'; 
                } 
        echo '<br />';
                if (!empty($show['end_date']) and ($show['end_date'] <> '0000-00-00')) {  
                    $edt = explode('-', $show['end_date']);
                    echo 'tot <span class="text-danger">'. strftime("%A %e %B %Y", mktime(0, 0, 0, $edt[1], $edt[2], $edt[0])) .'</span>'; 
                } 
        echo ' </td><td>';
                    if ( $show['days'] > 0 ) { 
                        echo '<span class="label label-info">' . $show['days'] . ' dagen</span> ';
                    }
        echo '    <br />';
                    if ( $show['earnings'] > 0 ) { 
                            echo '<span class="label label-success">'. money_format('%(#1n', $show['earnings']) .'</span> ';
                    }
        echo ' </td><td>';
                    
                    if (!empty($show['email'])) {    
                        echo '<a href="mailto:'. $show['email'] .'">
                                <span class="glyphicon glyphicon-envelope" aria-hidden="true"></span></a>&nbsp;';
                    } 
                
                    echo $show['name'];
                    
                    if (!empty($show['phone']) or !empty($show['telefoon'])) { 
                        echo '<br /><span class="glyphicon glyphicon-phone" aria-hidden="true"></span>&nbsp;';
                        echo empty($show['phone']) ? $show['telefoon'] : $show['phone'];
                    }
            
        echo ' </td>
            </tr>';
} ?>
            </tbody>
            </table> 
        </div>
    
<?  } else {  
    
        if (!empty($_POST['AID']) or !empty(L3)) {

            $aID = !empty($_POST['AID']) ? clean_input($_POST['AID']) : L3;

            try {
                                
                $status = $hgl->prepare('SELECT id, status FROM hgl_sga_status ORDER BY id ASC');
                $status->execute();
                $status->setFetchMode(PDO::FETCH_ASSOC);
                
                $sm = $hgl->prepare('SELECT s.k_id, k.keten, a.s_id, s.naam, a.id, a.action_id, 
                                            a.start_date, a.end_date, a.earnings, a.status, a.volunteer, a.notes
                                     FROM hgl_sga AS a JOIN hgl_smv AS s ON s.id = a.s_id
                                                       JOIN hgl_smk AS k ON k.id = s.k_id
                                     WHERE a.id = :aID');
                $sm->execute(array(':aID' => $aID));
                $sm->setFetchMode(PDO::FETCH_OBJ);
                
                if ($sm->rowCount() > 0) {
                    $data = $sm->fetch();
                } else {
                    $_SESSION['info'] = 'Statiegeldactie niet gevonden';
                }
        
            } catch(PDOException $e) {
        
                $_SESSION['error'] = 'SQL Fout: ' . $e->getMessage();
            }

?>         
    
        <h4 class="text-right"><span class="glyphicon glyphicon-edit"></span> Bewerk Statiegeldactie</h4>
    
        <form class="form-horizontal" role="form" action="/?h=statiegeldacties&g=wijzigen" method="post">
        <input type="hidden" name="AID" value="<? echo $data->id; ?>">
        
        <!-- Supermarket !-->
        <div class="form-group">
        <label class="col-sm-2 control-label">Supermarkt</label>
        <div class="col-sm-10">
            <div class="input-group"> 
                <span class="input-group-addon"><span class="glyphicon glyphicon-flag" aria-hidden="true"></span></span>
                <input type="text" class="form-control" name="aSupermarkt" placeholder="Supermarkt..."
                       value="<? echo $data->keten . ' - ' .$data->naam; ?>" readonly>
            </div>        
        </div>
        </div>
        
        <!-- Status !-->
        <div class="form-group <? if(!empty($_POST) && empty($_POST['aStatus'])) { echo 'has-warning'; } ?>">
        <label class="col-sm-2 control-label">Status</label>
        <div class="col-sm-10">
            <div class="input-group"> 
                <span class="input-group-addon"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span></span>
                <select class="form-control" name="aStatus">
            <?      while($is = $status->fetch()) {
                        if ($is['id'] == $data->status ) {
                            echo '<option value="'. $is['id'].'" selected>'. $is['status'].'</option>';
                        } else {
                            echo '<option value="'. $is['id'].'">'. $is['status'].'</option>';
                        }
                    } ?>
                </select>
            </div>        
        </div>
        </div>

        <!-- Action -->
        <div class="form-group <? if(!empty($_POST) && empty($_POST['aAction'])) { echo 'has-warning'; } ?>">
        <label for="action" class="col-sm-3 control-label">Actienummer</label>
        <div class="col-sm-8">
            <div class="input-group">
                <span class="input-group-addon"><span class="glyphicon glyphicon-qrcode" aria-hidden="true"></span></span>
                <input type="text" class="form-control" placeholder="Actienummer..." name="aAction" 
                       value="<? echo $_POST['aAction'] ? $_POST['aAction'] : $data->action_id; ?>">
            </div>
        </div>
        </div>

        <!-- Earnings -->
        <div class="form-group <? if(!empty($_POST) && empty($_POST['aEarnings'])) { echo 'has-warning'; } ?>">
        <label for="action" class="col-sm-3 control-label">Opbrengsten</label>
        <div class="col-sm-8">
            <div class="input-group">
                <span class="input-group-addon"><span class="glyphicon glyphicon-euro" aria-hidden="true"></span></span>
                <input type="text" class="form-control" placeholder="Opbrengst..." name="aEarnings" 
                       value="<? echo $_POST['aEarnings'] ? $_POST['aEarnings'] : $data->earnings; ?>">
            </div>
        </div>
        </div>

        <!-- Action Period -->
        <div class="form-group <? if(!empty($_POST) && empty($_POST['aEnddate'])) { echo 'has-warning'; } ?>">
        <label for="action" class="col-sm-3 control-label">Actieperiode</label>
        <div class="col-sm-8">
            <div class="input-group input-daterange" id="datepicker">
                <span class="input-group-addon"><span class="glyphicon glyphicon-calendar" aria-hidden="true"></span></span>
                <input type="text" class="form-control" placeholder="Start Datum" name="aStartdate" 
                       value="<? echo $_POST['aStartdate'] ? $_POST['aStartdate'] : $data->start_date; ?>">
                <span class="input-group-addon">tot</span>
                <input type="text" class="form-control" placeholder="Eind Datum" name="aEnddate" 
                       value="<? echo $_POST['aEnddate'] ? $_POST['aEnddate'] : $data->end_date; ?>">
            </div>
        </div>
        </div>

        <!-- Volunteer -->
        <div class="form-group <? if(!empty($_POST) && empty($_POST['aVolunteer'])) { echo 'has-warning'; } ?>">
        <label for="action" class="col-sm-3 control-label">Vrijwilliger</label>
        <div class="col-sm-8">
            <div class="input-group">
                <span class="input-group-addon"><span class="glyphicon glyphicon-user" aria-hidden="true"></span></span>
                <input type="text" class="form-control" placeholder="Vrijwilliger..." name="aVolunteer" 
                       value="<? echo $_POST['aVolunteer'] ? $_POST['aVolunteer'] : $data->volunteer; ?>">
            </div>
        </div>
        </div>

        <!-- Notes -->
        <div class="form-group <? if(!empty($_POST) && empty($_POST['aNotes'])) { echo 'has-warning'; } ?>">
        <label for="notes" class="col-sm-2 control-label">Notities</label>
            <div class="col-sm-10">
                <textarea class="form-control" rows="8" id="notes" 
                          name="aNotes"><? echo $_POST['aNotes'] ? $_POST['aNotes'] : $data->notes; ?></textarea>
            </div>
        </div>
        
        <!-- Submit !-->            
        <div class="form-group">
            <label class="col-sm-2 control-label"></label>
            <div class="col-sm-10">
                <button type="submit" class="btn btn-warning">Bijwerken</button>
            </div>
        </div>
        </form>

<?  }  
}   ?>

</div>

<script type="text/javascript">

    $(document).ready(function() {

        $('[data-toggle="popover"]').popover({
            placement: 'auto bottom',
            container: 'body',
            html: true
        });
                    
        $('.input-group.date').datepicker({
            autoclose: true,
            beforeShowDay: $.noop,
            calendarWeeks: true,
            clearBtn: false,
            daysOfWeekDisabled: [],
            endDate: Infinity,
            forceParse: true,
            format: 'yyyy-mm-dd',
            keyboardNavigation: true,
            language: 'nl',
            minViewMode: 0,
            orientation: "top left",
            rtl: false,
            startDate: -Infinity,
            startView: 2,
            todayBtn: false,
            todayHighlight: true,
            weekStart: 0
        });
        
        $('.input-daterange').datepicker({
            autoclose: true,
            beforeShowDay: $.noop,
            calendarWeeks: true,
            clearBtn: false,
            daysOfWeekDisabled: [],
            endDate: Infinity,
            forceParse: true,
            format: 'yyyy-mm-dd',
            keyboardNavigation: true,
            language: 'nl',
            minViewMode: 0,
            orientation: "top left",
            rtl: false,
            startDate: -Infinity,
            startView: 2,
            todayBtn: "linked",
            todayHighlight: true,
            weekStart: 0
        });
    
    });

</script>

