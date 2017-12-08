<? include(PATH.'alert.control.inc.php'); ?>

<div class="row">
    <div class="col-md-4">
        <h2>Opbrengsten per Jaar</h2>
    <?  try {
            $conn = new PDO('mysql:host='.FDBH.';dbname='.FDBN, FDBU, FDBP);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $conn->prepare('SELECT 
                                        year(end_date) as jaar, 
                                        count(id) as aantal, 
                                        avg(earnings) as opbr_gem, 
                                        sum(earnings) as opbr_tot 
                                    FROM hgl_sga 
                                        WHERE earnings > 0 
                                    GROUP BY year(end_date)');
        
            if($stmt->execute()) {
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
            } 
        } catch(PDOException $e) {
            $_SESSION['error'] = 'SQL Fout: ' . $e->getMessage();
        }
    ?>
        <div class="table-responsive">
            <table class="table table-hover">
            <thead>
            <tr>
                <th>Jaar</th>
                <th>&#35; Acties</th>
                <th>&euro; / Actie</th>
                <th>&euro; / Totaal</th>
            </tr>
            </thead>
            <tbody>
    <?  while($row = $stmt->fetch()) { ?>
            <tr>
                <th scope="row"><?php echo $row['jaar']; ?></th>
                <td><?php echo $row['aantal']; ?></td>
                <td>&euro; <? echo number_format($row['opbr_gem'],2,',','.'); ?></td>
                <td>&euro; <? echo number_format($row['opbr_tot'],2,',','.'); ?></td>
            </tr>
    <?  } ?>
            </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-8">
        <h2>Top 10 Supermarkten <small>(o.b.v. hoogste opbrengst per dag)</small></h2>
    <?  try {
            $conn = new PDO('mysql:host='.FDBH.';dbname='.FDBN, FDBU, FDBP);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $conn->prepare('SELECT 
                                        k.keten as chain, 
                                        s.naam as supermarket, 
                                        s.plaats as city, 
                                        avg(datediff(a.end_date, a.start_date)) as average_days, 
                                        sum(a.earnings) as total_revenue, 
                                        sum(a.earnings) / sum(datediff(a.end_date, a.start_date)) as average_revenue_per_day
                                    FROM hgl_sga AS a 
                                        LEFT JOIN hgl_smv AS s ON s.id = a.s_id
                                        LEFT JOIN hgl_smk AS k ON k.id = s.k_id
                                    WHERE a.status > 0
                                        GROUP BY k.keten, s.naam, s.plaats
                                    ORDER BY `average_revenue_per_day` DESC LIMIT 10');
        
            if($stmt->execute()) {
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
            } 
        } catch(PDOException $e) {
            $_SESSION['error'] = 'SQL Fout: ' . $e->getMessage();
        }
    ?>
        <div class="table-responsive">
            <table class="table table-hover">
            <thead>
            <tr>
                <th>Keten</th>
                <th>Vestiging</th>
                <th>Plaats</th>
                <th>Dagen (gem.)</th>
                <th>Opbrengst</th>
                <th class="text-right">Opbrengst per dag</th>
            </tr>
            </thead>
            <tbody>
    <?  while($row = $stmt->fetch()) { ?>
            <tr>
                <td><?php echo $row['chain']; ?></span></td>
                <td><?php echo $row['supermarket']; ?></td>
                <td><?php echo $row['city']; ?></td>
                <td><?php echo number_format($row['average_days'],0); ?></td>
                <td>&euro; <? echo number_format($row['total_revenue'],2,',','.'); ?></td>
                <td class="text-right">&euro; <? echo number_format($row['average_revenue_per_day'],2,',','.'); ?></td>
            </tr>
    <?  } ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
