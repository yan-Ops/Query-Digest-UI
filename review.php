<?php

require('config.php');

$review = new mysqli($reviewhost['db_host'], $reviewhost['db_user'], $reviewhost['db_password'], $reviewhost['db_database']);

if (@$_REQUEST['Review'] == 'Review' ) {
    $query = $review->prepare('UPDATE review SET reviewed_by = ?, reviewed_on = NOW(), comments = ? WHERE checksum = ?');
    $query->bind_param("sss", $_REQUEST['reviewed_by'], $_REQUEST['comments'], $_REQUEST['checksum']);
    $query->execute();
    header( "Location: review.php?checksum={$_REQUEST['checksum']}" ) ;
    exit;
}

$query = $review->prepare('SELECT review.*
                    FROM '.$reviewhost['review_table'].' AS review
                   WHERE review.checksum = ?
                GROUP BY review.checksum
                    ');
$query->bind_param("s", $_REQUEST['checksum']);
$query->execute();
$result = $query->get_result();
$reviewData = $result->fetch_assoc();

$query = $review->prepare('SELECT review.*
                    FROM '.$reviewhost['review_history_table'].' AS review
                   WHERE review.checksum = ?
                GROUP BY review.checksum
                    ');
$query->bind_param("s", $_REQUEST['checksum']);
$query->execute();
$result = $query->get_result();
$reviewHistoryData = $result->fetch_assoc();

foreach ($reviewData as $key=>&$val) {
    if (in_array($key, array('checksum')))
        continue;
    if (is_numeric($val)) {
        if (stripos($key, 'time') !== false) {
            $val *= 1000;
            $val = round($val, 0);
        }
        else
            $val = round($val, 2);
        $val = number_format($val);
    }
}
unset ($key, $val);

foreach ($reviewHistoryData as $key=>&$val) {
    if (in_array($key, array('checksum')))
        continue;
    if (is_numeric($val)) {
        if (stripos($key, 'time') !== false) {
            $val *= 1000;
            $val = round($val, 0);
        }
        else
            $val = round($val, 2);
        $val = number_format($val);
    }
}
unset ($key, $val);

?>

<?php include('templates/header.php'); ?>

<script type="text/javascript">
    $(function() {
        $( ".accordionOpen" ).accordion({
            collapsible: true
        });

        $( ".accordion" ).accordion({
            collapsible: true,
            active: false
        });

        $('.dataTable').dataTable({
            "bJQueryUI":        true,
            "bPaginate":        false,
            "bLengthChange":    false,
            "bFilter":          false,
            "bSort":            false,
            "bInfo":            false,
            "sDom":             "t"
        });

    });
</script>

<div class="accordionOpen">
    <h3><a href="#">Example Query</a></h3>
    <div>
        <?php echo str_replace(',', ', ', $reviewData['sample']); ?>
    </div>
</div>

<div class="accordion">
    <h3><a href="#">Query Fingerprint</a></h3>
    <div>
        <?php echo str_replace(',', ', ', $reviewData['fingerprint']); ?>
    </div>
</div>

<div class="accordionOpen">
    <h3><a href="#">Review Information</a></h3>
    <div>
        <form method="get">
            Reviewed By: <br>
            <input type="text" name="reviewed_by" value="<?php echo $reviewData['reviewed_by']; ?>"><br>
            Comments: <br>
            <textarea name="comments"><?php echo $reviewData['comments']; ?></textarea><br>
            <input type="hidden" name="checksum" value="<?php echo $_REQUEST['checksum']; ?>">
            <input type="submit" name="Review" value="Review">
        </form>
    </div>
</div>

<div class="accordionOpen">
    <h3><a href="#">Detailed Stats</a></h3>
    <div>
        <p>Seen between <?php echo $reviewHistoryData['ts_min']; ?> and <?php echo $reviewHistoryData['ts_max']; ?>.</p>
        <table class="dataTable">
            <thead>
                <tr>
                    <th></th>
                    <th>Count</th>
                    <th>Sum</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Query Count</td>
                    <td class="number"><?php echo $reviewHistoryData['ts_cnt']; ?></td>
                    <td class="number">-</td></tr>
                <tr><td>Query Cache</td>
                    <td class="number"><?php echo $reviewHistoryData['QC_Hit_cnt']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['QC_Hit_sum']; ?></td></tr>
                <tr><td>Full Scan</td>
                    <td class="number"><?php echo $reviewHistoryData['Full_scan_cnt']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Full_scan_sum']; ?></td></tr>
                <tr><td>Full Join</td>
                    <td class="number"><?php echo $reviewHistoryData['Full_join_cnt']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Full_join_sum']; ?></td></tr>
                <tr><td>Temporary Tables</td>
                    <td class="number"><?php echo $reviewHistoryData['Tmp_table_cnt']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Tmp_table_sum']; ?></td></tr>
                <tr><td>On Disk Temporary Tables</td>
                    <td class="number"><?php echo $reviewHistoryData['Disk_tmp_table_cnt']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Disk_tmp_table_sum']; ?></td></tr>
                <tr><td>File Sorts</td>
                    <td class="number"><?php echo $reviewHistoryData['Filesort_cnt']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Filesort_sum']; ?></td></tr>
                <tr><td>On Disk File Sorts</td>
                    <td class="number"><?php echo $reviewHistoryData['Disk_filesort_cnt']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Disk_filesort_sum']; ?></td></tr>
            </tbody>
        </table>

        <table class="dataTable">
            <thead>
                <tr>
                    <th></th>
                    <th>Sum</th>
                    <th>Min</th>
                    <th>Max</th>
                    <th>95%</th>
                    <th>StdDev</th>
                    <th>Median</th>
                </tr>
            </thead>
            <tbody>
                <tr><td>Query Time (ms)</td>
                    <td class="number"><?php echo $reviewHistoryData['Query_time_sum']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Query_time_min']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Query_time_max']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Query_time_pct_95']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Query_time_stddev']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Query_time_median']; ?></td></tr>
                <tr><td>Lock Time (ms)</td>
                    <td class="number"><?php echo $reviewHistoryData['Lock_time_sum']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Lock_time_min']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Lock_time_max']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Lock_time_pct_95']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Lock_time_stddev']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Lock_time_median']; ?></td></tr>
                <tr><td>Rows Sent</td>
                    <td class="number"><?php echo $reviewHistoryData['Rows_sent_sum']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Rows_sent_min']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Rows_sent_max']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Rows_sent_pct_95']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Rows_sent_stddev']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Rows_sent_median']; ?></td></tr>
                <tr><td>Rows Examined</td>
                    <td class="number"><?php echo $reviewHistoryData['Rows_examined_sum']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Rows_examined_min']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Rows_examined_max']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Rows_examined_pct_95']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Rows_examined_stddev']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Rows_examined_median']; ?></td></tr>
                <tr><td>Rows Affected</td>
                    <td class="number"><?php echo $reviewHistoryData['Rows_affected_sum']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Rows_affected_min']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Rows_affected_max']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Rows_affected_pct_95']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Rows_affected_stddev']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Rows_affected_median']; ?></td></tr>
                <tr><td>Rows Read</td>
                    <td class="number"><?php echo $reviewHistoryData['Rows_read_sum']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Rows_read_min']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Rows_read_max']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Rows_read_pct_95']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Rows_read_stddev']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Rows_read_median']; ?></td></tr>
                <tr><td>Merge_passes</td>
                    <td class="number"><?php echo $reviewHistoryData['Merge_passes_sum']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Merge_passes_min']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Merge_passes_max']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Merge_passes_pct_95']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Merge_passes_stddev']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['Merge_passes_median']; ?></td></tr>
                <tr><td>InnoDB IO Read Ops</td>
                    <td class="number"></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_IO_r_ops_min']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_IO_r_ops_max']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_IO_r_ops_pct_95']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_IO_r_ops_stddev']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_IO_r_ops_median']; ?></td></tr>
                <tr><td>InnoDB IO Read Bytes</td>
                    <td class="number"></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_IO_r_bytes_min']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_IO_r_bytes_max']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_IO_r_bytes_pct_95']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_IO_r_bytes_stddev']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_IO_r_bytes_median']; ?></td></tr>
                <tr><td>InnoDB IO Read Wait</td>
                    <td class="number"></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_IO_r_wait_min']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_IO_r_wait_max']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_IO_r_wait_pct_95']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_IO_r_wait_stddev']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_IO_r_wait_median']; ?></td></tr>
                <tr><td>InnoDB Record Lock Wait</td>
                    <td class="number"></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_rec_lock_wait_min']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_rec_lock_wait_max']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_rec_lock_wait_pct_95']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_rec_lock_wait_stddev']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_rec_lock_wait_median']; ?></td></tr>
                <tr><td>InnoDB Queue Wait</td>
                    <td class="number"></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_queue_wait_min']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_queue_wait_max']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_queue_wait_pct_95']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_queue_wait_stddev']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_queue_wait_median']; ?></td></tr>
                <tr><td>InnoDB Distinct Pages</td>
                    <td class="number"></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_pages_distinct_min']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_pages_distinct_max']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_pages_distinct_pct_95']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_pages_distinct_stddev']; ?></td>
                    <td class="number"><?php echo $reviewHistoryData['InnoDB_pages_distinct_median']; ?></td></tr>

            </tbody>
        </table>
    </div>
</div>

<div class="accordion">
    <h3><a href="#">Raw Data</a></h3>
    <div>
        <pre>
            <?php var_dump($reviewData); ?>
            <?php var_dump($reviewHistoryData); ?>
        </pre>
    </div>
</div>

<?php include('templates/footer.php'); ?>
