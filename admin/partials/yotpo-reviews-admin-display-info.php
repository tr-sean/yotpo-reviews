<h2 class="yp-style">Plugin Info</h2>

<h3>Initial Setup</h3>
<ol>
	<li>Input your Yotpo app ID and secret key. <a href="https://support.yotpo.com/en/article/finding-your-yotpo-app-key-and-secret-key" target="_blank">How?</a></li>
	<li>Select how you would like your products identified.</li>
	<li>Input your WooCommerce client key and secret key. <a href="https://woocommerce.com/document/woocommerce-rest-api/" target="_blank">How?</a></li>
	<li>Your keys are not stored in a database, but rather stored in the wp-config.php file for safe keeping.</li>
</ol>

<h3>Using The Plugin</h3>
<p>Upon saving your settings, a scheduled event will be created which will run every 24 hours. This will check for new reviews to import from the previous day.</p>
<p>In order to get all current reviews, prior to this plugin's installation/activation, you'll need to run the, "Run First Time Import" function by clicking the corresponding button.</p>
<p>If you like, you can go in and manually get reviews from the past 24 hours, by clicking the "Run Manual Import" button.</p>
<p>If you have reviews that are published then “rejected”, the next time an import is run, they will be removed from the WooCommerce reviews.</p>

<h3>Helper Functions</h3>
<p>Here are some helper functions you can use in your theme that come along with this plugin:</p>
<p>
	<strong>Review Stars</strong><br>
	To display a more accurate "bottomline" rating, you can use the function below. Place it where you want to display a product's rating.
</p>
<code>&lt;?php echo Yotpo_Reviews_Public::wc_ratings( $product->get_id() ); ?&gt;</code>

<h3>Template Override</h3>
<p>The styling for this plugin is basic, as it would be expected for you to update the styling to match your theme. You can override the template by copying the template file from the plugin folder to your theme's WooCommerce template override folder (example: <code>wp-content/themes/your-theme/woocommerce/</code>). These files are located at: <code>wp-content/plugins/yotpo-reviews/templates/public/templates/</code>.</p>

<h3>Plugin Deactivation</h3>
<p>When the plugin is deactivated, to avoid any unwanted review imports, the scheduled event is removed. If you decide to reactivate the plugin you'll need to re-save the settings in order to start the scheduled event again.</p>
