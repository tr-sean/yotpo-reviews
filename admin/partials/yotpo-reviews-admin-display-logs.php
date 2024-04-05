<h2 class="yp-style">Logs</h2>
<p>See when the import has been run. If you like, you can clear the data from the table by <a href="" class="do-ajax" data-table="clear">clicking here</a>.</p>

<?php
	// Get run logs
	$logs = Yotpo_Reviews_Admin::display_logs_table();
?>


<table class="widefat fixed" cellspacing="0">
	<thead>
		<tr>
			<th id="columnname" class="manage-column column-columnname" scope="col">Import Date</th>
			<th id="columnname" class="manage-column column-columnname" scope="col">Total Reviews</th>
			<th id="columnname" class="manage-column column-columnname" scope="col">Imported</th>
			<th id="columnname" class="manage-column column-columnname" scope="col">
				Skipped
				<span class="dashicons dashicons-info" title="Either review already exists on site or product does not exist" style="cursor:pointer;"></span>
			</th>
			<th id="columnname" class="manage-column column-columnname" scope="col">Deleted</th>
			<th id="columnname" class="manage-column column-columnname" scope="col">Import Method</th>
		</tr>
	</thead>
	<tbody>
		<?php $i = 0; foreach ($logs as $log) : ?>
		<tr <?php echo $i % 2 == 0 ? 'class="alternate"' : ''; ?>>
			<td class="column-columnname" title="<?php echo date('l F j, Y \a\t g:i a', strtotime($log['date'])); ?>"><?php echo $log['date']; ?></td>
			<td class="column-columnname"><?php echo $log['total']; ?></td>
			<td class="column-columnname"><?php echo $log['imported']; ?></td>
			<td class="column-columnname"><?php echo $log['skipped_none']; ?></td>
			<td class="column-columnname"><?php echo $log['deleted']; ?></td>
			<td class="column-columnname"><?php echo $log['method']; ?></td>
		</tr>
		<?php $i ++; endforeach; ?>
	</tbody>
</table>
