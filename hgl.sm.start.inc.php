<?php

    switch(L2) {

        case 'vestiging':
            
            unset($_SESSION['error']);
            unset($_SESSION['info']);

            try {
                
                $supermarkets = 'SELECT s.id, k.keten, v.status, s.naam, s.plaats, s.postcode, s.adres, s.telefoon, 
                                        count(a.id) as qty, sum(a.earnings) as eur, 
                                        c.Name as ctName, c.Phone as ctPhone, c.email as ctMail
                                 FROM hgl_smv AS s 
                                    JOIN hgl_smv_status AS v on s.status = v.id
                                    LEFT JOIN hgl_smv_cf AS c on c.s_id = s.id
                                    JOIN hgl_smk AS k ON k.id = s.k_id
                                    LEFT JOIN hgl_sga AS a ON s.id = a.s_id
                                 WHERE s.id = ' . L3 . ' 
                                 GROUP BY k.keten, v.status, s.naam, s.plaats, s.postcode, s.adres, s.telefoon
                                 ORDER BY qty DESC';
                $get_sm = $hgl->prepare($supermarkets);
                $get_sm->execute();
                $get_sm->setFetchMode(PDO::FETCH_ASSOC); 
            
            } catch(PDOException $e) {
                
                $_SESSION['error'] = 'SQL Fout: ' . $e->getMessage();
            }
            
            break; 
            
        case 'wijzigen':
            
            unset($_SESSION['error']);
            unset($_SESSION['info']);
            
            $sID = !empty($_POST['SID']) ? clean_input($_POST['SID']) : L3;
            
            if (!empty($_POST['SID'])) {
            
                $sChain = clean_input($_POST['sChain']);
                $sName = clean_input($_POST['sName']);
                $sStatus = clean_input($_POST['sStatus']);
                $sPlace = clean_input($_POST['sPlace']);
                $sZipcode = clean_input($_POST['sZipcode']);
                $sAddress = clean_input($_POST['sAddress']);
                $sPhone = clean_input($_POST['sPhone']);
                $ctName = clean_input($_POST['ctName']);
                $ctPhone = clean_input($_POST['ctPhone']);
                $ctMail = clean_input($_POST['ctMail']);

                try {
                    $upd = $hgl->prepare('UPDATE hgl_smv 
                                             SET k_id = :sChain,
                                                 naam = :sName,
                                               status = :sStatus,
                                               plaats = :sPlace,
                                             postcode = :sZipcode, 
                                                adres = :sAddress, 
                                             telefoon = :sPhone
                                          WHERE id = :sID');
                    $upd->execute(array(':sChain' => $sChain,
                                        ':sName' => $sName,
                                        ':sStatus' => $sStatus,
                                        ':sPlace' => $sPlace,
                                        ':sZipcode' => $sZipcode, 
                                        ':sAddress' => $sAddress,
                                        ':sPhone' => $sPhone,
                                        ':sID' => $sID));
                    
                    if (!empty($ctName)) {
                        $upd_cf = $hgl->prepare('INSERT INTO hgl_smv_cf (s_id,name,phone,email) 
                                                    VALUES (:sID,:ctName,:ctPhone,:ctMail)
                                                    ON DUPLICATE KEY UPDATE name = VALUES(name), 
                                                                            phone = VALUES(phone), 
                                                                            email = VALUES(email)');
                        $upd_cf->execute(array(':ctName' => $ctName,
                                               ':ctPhone' => $ctPhone,
                                               ':ctMail' => $ctMail,
                                               ':sID' => $sID));
                    }
                        
                    $_SESSION['info'] = 'Supermarkt is bijgewerkt!';
                    
                } catch(PDOException $e) {
                    
                    $_SESSION['error'] = 'SQL Fout: ' . $e->getMessage();
                }
                                      
            } 
                                      
        break;
        
        case 'toevoegen':
            
            unset($_SESSION['error']);
            unset($_SESSION['info']);
                        
            if (empty(L3)) {
            
                try {
                    $ins = $hgl->prepare('INSERT INTO hgl_smv (status) VALUES (1)');
                    $ins->execute();
                    
                    $sID = $hgl->lastInsertId();
                    header('Location: http://hgl.fresch.org/?h=supermarkten&g=wijzigen&l='. $sID);
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
            $place_filter = clean_input($_POST['place_filter']);
            $chain_filter = clean_input($_POST['chain_filter']);
            
            try {
                
                $supermarkets = 'SELECT s.id, k.keten, v.status, s.naam, s.plaats, s.postcode, s.adres, s.telefoon, 
                                        count(a.id) as qty, sum(a.earnings) as eur, 
                                        c.Name as ctName, c.Phone as ctPhone, c.email as ctMail
                                 FROM hgl_smv AS s 
                                    JOIN hgl_smv_status AS v on s.status = v.id
                                    LEFT JOIN hgl_smv_cf AS c on c.s_id = s.id
                                    JOIN hgl_smk AS k ON k.id = s.k_id
                                    LEFT JOIN hgl_sga AS a ON s.id = a.s_id';
                
                if (!empty($search_filter)) {
                    
                    $supermarkets .= ' WHERE lower(k.keten) LIKE lower("%'. $search_filter .'%") 
                                        OR lower(v.status) LIKE lower("%'. $search_filter .'%") 
                                        OR lower(s.naam) LIKE lower("%'. $search_filter .'%") 
                                        OR lower(s.plaats) LIKE lower("%'. $search_filter .'%") 
                                        OR lower(s.adres) LIKE lower("%'. $search_filter .'%")';
                }

                if (!empty($status_filter)) {
                    $supermarkets .= ' WHERE s.status =' . $status_filter;
                }
            
                if (!empty($place_filter)) {
                    $supermarkets .= ' WHERE lower(s.plaats) LIKE lower("%'. $place_filter .'%")';
                }

                if (!empty($chain_filter)) { 
                    $supermarkets .= ' WHERE k.id = '. $chain_filter;
                }
            
            
                $supermarkets .= ' GROUP BY k.keten, v.status, s.naam, s.plaats, s.postcode, s.adres, s.telefoon
                                    ORDER BY qty DESC';
                $get_sm = $hgl->prepare($supermarkets);
                $get_sm->execute();
                $get_sm->setFetchMode(PDO::FETCH_ASSOC); 
            
            } catch(PDOException $e) {
                
                $_SESSION['error'] = 'SQL Fout: ' . $e->getMessage();
            }
                        
            break;     
    }

?>

<div class="col-md-2">
    <div class="list-group">
        <a href="/?h=supermarkten" class="list-group-item active">
            <span class="glyphicon glyphicon-home" aria-hidden="true"></span>
            &nbsp; Supermarkten</a>
        <a href="/?h=supermarkten&g=toevoegen" class="list-group-item">
            <span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>
            &nbsp; Voeg toe</a>
    </div>

<?  $pchk = array('wijzigen', 'toevoegen');
    if (!in_array(L2,$pchk)) { ?>
        
    <!-- Search Filters !-->
    <!-- Free Search !-->
    <div class="form-group">
        <h6>Vrij <abbr title="Er wordt gezocht op keten, vestiging, plaats en adres.">zoeken</abbr></h6>
        <form action="/?h=supermarkten" method="post">
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
        <form action="/?h=supermarkten" method="post">
        <div id="filterForm">
            <select name="status_filter" class="form-control" onchange="this.form.submit()">
                <option value="">Kies een Status...</option>
                <option disabled>──────────</option>
        <?  try {   $get_status = $hgl->prepare('SELECT id, status FROM hgl_smv_status ORDER BY id ASC');
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
        <form action="/?h=supermarkten" method="post">
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

    <!-- Chains Filter !-->
    <div class="form-group">
        <h6>Filter op Keten</h6>
        <form action="/?h=supermarkten" method="post">
        <div id="filterForm">
            <select name="chain_filter" class="form-control" onchange="this.form.submit()">
                <option value="">Kies een Keten...</option>
                <option disabled>──────────</option>
        <?  try {   $get_chains = $hgl->prepare('SELECT id, keten FROM hgl_smk ORDER BY keten ASC');
                    $get_chains->execute();
                    $get_chains->setFetchMode(PDO::FETCH_ASSOC);
            } catch(PDOException $e) {
                $_SESSION['error'] = 'SQL Fout: ' . $e->getMessage();
            }

            while($chains = $get_chains->fetch()) {   
            
                if(isset($chain_filter) && ($chain_filter == $chains['id'])) {
                    echo '<option value="'. $chains['id'] .'" selected>' . $chains['keten'] .'</option>';
                } else {
                    echo '<option value="'. $chains['id'] .'">' . $chains['keten'] .'</option>';
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
                <th>Status</th>
                <th colspan="2"></th>
                <th colspan="2">&#35; Acties</th>
                <th></th>
                <th>Contactpersoon</th>
            </tr>
            </thead>
            <tbody>
<?  while($show = $get_sm->fetch()) { 
        echo '<tr>
                <td><a href="/?h=supermarkten&g=wijzigen&l='. $show['id'] .'"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></a></td>
                <td>' . $show['keten'] .' - ' . $show['naam'] .'
                    <br/>
                    <a href="https://maps.google.nl/?q=' . str_replace(' ','+',$show['adres']) .'+'. str_replace(' ','+',$show['postcode']) . '+' . str_replace(' ','+',$show['plaats']) . '" target="_blank"><span class="glyphicon glyphicon-map-marker" aria-hidden="true"></span></a>
                    &nbsp;' . $show['adres'] . ', '. $show['plaats'] . '</td>
                <td>' . $show['status'] . '</td>
                <td><a href="/?h=statiegeldacties&g=toevoegen&l='. $show['id'] .'"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span></a></td>
                <td><a href="/?h=statiegeldacties&g=supermarkt&l='. $show['id'] .'"><span class="glyphicon glyphicon-new-window" aria-hidden="true"></span></a></td>
                <td>' . $show['qty'] . '</td>
                <td>(&euro; ' . number_format($show['eur'],2,',','.') .')</td>
                <td>';
                
                if (!empty($show['ctMail'])) { 
                    echo '<a href="mailto:'. $show['ctMail'] .'">
                            <span class="glyphicon glyphicon-envelope" aria-hidden="true"></span></a>';
                } 

        echo '  <br/>';

                if (!empty($show['ctPhone'])) {
                    echo '<span class="glyphicon glyphicon-phone" aria-hidden="true"></span>';
                } elseif (!empty($show['telefoon'])) { 
                    echo '<span class="glyphicon glyphicon-phone-alt" aria-hidden="true"></span>';    
                } 
                    
        echo '  </td>
                <td>' . $show['ctName'] . '<br/>';
    
        echo empty($show['ctPhone']) ? $show['telefoon'] : $show['ctPhone'];
        echo '  </td>
            </tr>';
     } ?>
            </tbody>
            </table>
        </div>
    
<?  } else {  
    
        if (!empty($_POST['SID']) or !empty(L3)) {

            $sID = !empty($_POST['SID']) ? clean_input($_POST['SID']) : L3;

            try {
                
                $chains = $hgl->prepare('SELECT id, keten FROM hgl_smk ORDER BY keten ASC');
                $chains->execute();
                $chains->setFetchMode(PDO::FETCH_ASSOC);
                
                $status = $hgl->prepare('SELECT id, status FROM hgl_smv_status ORDER BY id ASC');
                $status->execute();
                $status->setFetchMode(PDO::FETCH_ASSOC);
                
                $sm = $hgl->prepare('SELECT s.id, s.k_id, s.status, s.naam, s.plaats, s.postcode, s.adres, s.telefoon,
                                            c.name as ctName, c.phone as ctPhone, c.email as ctMail
                                     FROM hgl_smv AS s LEFT JOIN hgl_smv_cf AS c ON c.s_id = s.id 
                                     WHERE s.id = :sID');
                $sm->execute(array(':sID' => $sID));
                $sm->setFetchMode(PDO::FETCH_OBJ);
                
                if ($sm->rowCount() > 0) {
                    $data = $sm->fetch();
                } else {
                    $_SESSION['info'] = 'Supermarkt niet gevonden';
                }
        
            } catch(PDOException $e) {
        
                $_SESSION['error'] = 'SQL Fout: ' . $e->getMessage();
            }

?>         
    
        <h4 class="text-right"><span class="glyphicon glyphicon-edit"></span> Bewerk Supermarkt</h4>
    
        <form class="form-horizontal" role="form" action="/?h=supermarkten&g=wijzigen" method="post">
        <input type="hidden" name="SID" value="<? echo $data->id; ?>">
        
        <!-- Chains !-->
        <div class="form-group <? if(!empty($_POST) && empty($_POST['sChain'])) { echo 'has-warning'; } ?>">
        <label class="col-sm-2 control-label">Keten</label>
        <div class="col-sm-10">
            <div class="input-group"> 
                <span class="input-group-addon"><span class="glyphicon glyphicon-link" aria-hidden="true"></span></span>
                <select class="form-control" name="sChain">
            <?      while($ic = $chains->fetch()) {
                        if ($ic['id'] == $data->k_id ) {
                            echo '<option value="'. $ic['id'].'" selected>'. $ic['keten'].'</option>';
                        } else {
                            echo '<option value="'. $ic['id'].'">'. $ic['keten'].'</option>';
                        }
                    } ?>
                </select>
            </div>
        </div>
        </div>

        <!-- Name !-->
        <div class="form-group <? if(!empty($_POST) && empty($_POST['sName'])) { echo 'has-warning'; } ?>">
        <label class="col-sm-2 control-label">Supermarkt</label>
        <div class="col-sm-10">
            <div class="input-group"> 
                <span class="input-group-addon"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></span>
                <input type="text" class="form-control" name="sName" placeholder="Supermarktnaam..." 
                       value="<? echo $_POST['sName'] ? $_POST['sName'] : $data->naam; ?>">
            </div>        
        </div>
        </div>
        
        <!-- Status !-->
        <div class="form-group <? if(!empty($_POST) && empty($_POST['sStatus'])) { echo 'has-warning'; } ?>">
        <label class="col-sm-2 control-label">Status</label>
        <div class="col-sm-10">
            <div class="input-group"> 
                <span class="input-group-addon"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span></span>
                <select class="form-control" name="sStatus">
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

        <!-- Address !-->
        <div class="form-group <? if(!empty($_POST) && empty($_POST['sAddress'])) { echo 'has-warning'; } ?>">
            <label class="col-sm-2 control-label">Adres</label>
            <div class="col-sm-10">
                <div class="input-group"> 
                    <span class="input-group-addon"><span class="glyphicon glyphicon-map-marker" aria-hidden="true"></span></span>
                    <input type="text" class="form-control" name="sAddress" placeholder="Adres..." 
                           value="<? echo $_POST['sAddress'] ? $_POST['sAddress'] : $data->adres; ?>">
                </div>
                <div class="input-group"> 
                    <span class="input-group-addon"><span class="glyphicon glyphicon-barcode" aria-hidden="true"></span></span>
                    <input type="text" class="form-control" name="sZipcode" placeholder="Postcode..." 
                           value="<? echo $_POST['sZipcode'] ? $_POST['sZipcode'] : $data->postcode; ?>">
                    <span></span>
                    <span class="input-group-addon"><span class="glyphicon glyphicon-home" aria-hidden="true"></span></span>
                    <input type="text" class="form-control" name="sPlace" placeholder="Plaatsnaam..." 
                           value="<? echo $_POST['sPlace'] ? $_POST['sPlace'] : $data->plaats; ?>">
                </div>           
            </div>
        </div>

        <!-- Phone !-->
        <div class="form-group <? if(!empty($_POST) && empty($_POST['sPhone'])) { echo 'has-warning'; } ?>">
        <label class="col-sm-2 control-label">Telefoon</label>
        <div class="col-sm-10">
            <div class="input-group"> 
                <span class="input-group-addon"><span class="glyphicon glyphicon-phone-alt" aria-hidden="true"></span></span>
                <input type="text" class="form-control" name="sPhone" placeholder="Telefoon..." 
                       value="<? echo $_POST['sPhone'] ? $_POST['sPhone'] : $data->telefoon; ?>">
            </div>        
        </div>
        </div>

        <!-- Contact !-->
        <div class="form-group <? if(!empty($_POST) && empty($_POST['ctName'])) { echo 'has-warning'; } ?>">
            <label class="col-sm-2 control-label">Contactpersoon</label>
            <div class="col-sm-10">
                <div class="input-group"> 
                    <span class="input-group-addon"><span class="glyphicon glyphicon-user" aria-hidden="true"></span></span>
                    <input type="text" class="form-control" name="ctName" placeholder="Contactpersoon..." 
                           value="<? echo $_POST['ctName'] ? $_POST['ctName'] : $data->ctName; ?>">
                </div>
                <div class="input-group"> 
                    <span class="input-group-addon"><span class="glyphicon glyphicon-phone" aria-hidden="true"></span></span>
                    <input type="text" class="form-control" name="ctPhone" placeholder="Telefoon..." 
                           value="<? echo $_POST['ctPhone'] ? $_POST['ctPhone'] : $data->ctPhone; ?>">
                    <span></span>
                    <span class="input-group-addon"><span class="glyphicon glyphicon-envelope" aria-hidden="true"></span></span>
                    <input type="email" class="form-control" name="ctMail" placeholder="E-mailadres..." 
                           value="<? echo $_POST['ctMail'] ? $_POST['ctMail'] : $data->ctMail; ?>">
                </div>           
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
