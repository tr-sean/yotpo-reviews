<div id="postbox-container-1">
	<div id="side-sortables" class="meta-box-sortables ui-sortable">

		<!-- Clear Cache -->
		<form action="" method="post" id="clear_cache" class="postbox">
			<input type="hidden" name="ypr_action" value="clear_cache">
			<div class="postbox-header"><h2 class="hndle ui-sortable-handle">Clear Review Cache</h2></div>
			<div class="inside">
				<p>You can manually clear the cache from the Yotpo API.</p>
				<input type="submit" value="Clear Cache" class="button">
			</div>
		</form>

		<!-- Manually Run Import -->
		<div class="postbox">
			<div class="postbox-header"><h2 class="hndle ui-sortable-handle">Manually Run Import</h2></div>
			<div class="inside">
				<p>You can manually run the import to gather reviews for the past 24 hours.</p>
				<a href="#" class="manually_run button do-ajax" data-run-type="manually_run">Run Manual Import</a>
			</div>
		</div>

		<!-- First Time Run -->
		<div class="postbox">
			<div class="postbox-header"><h2 class="hndle ui-sortable-handle">First Time Import</h2></div>
			<div class="inside">
				<p>In order to gather any reviews that have been posted prior to this install, you should run a first time import. This will import all reviews that have existing products.</p>
				<a href="#" class="first_time button do-ajax" data-run-type="first_time">Run First Time Import</a>
			</div>
		</div>
	</div>
</div>
