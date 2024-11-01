<?php
/*
Plugin Name: wp-gcal-rss
Plugin URI: http://code.google.com/p/wp-gcal-rss/
Description: A WordPress plug-in to bring Google Calendar events in via RSS without requiring the user to get a Google Data API key.
Author: Ken Martin
Version: 1.1
Author URI: http://www.kpmartin.com/
*/

function wp_gcal_rss($title, $c, $tz, $hmd, $mr) {
	$pluginRootURL = get_bloginfo('wpurl') . '/wp-content/plugins/' . dirname( plugin_basename( __FILE__ ) ); ?>

<div id="wp-gcal-rss_loading">
	<img src="<?php echo $pluginRootURL; ?>/wp-gcal-rss_loading.gif" />
</div>

<div id="wp-gcal-rss_dates" style="display:none;"></div>

<div id="wp-gcal-rss_error" style="display:none;">
	<p>It appears the the widget "wp-gcal-rss" is not working properly. This could be because:</p>
	<ul>
		<li>The widget not yet been configured in WP-Admin.</li>
		<li>The calendar may not have <a href="http://support.google.com/calendar/bin/answer.py?hl=en&answer=37083">"sharing" enabled</a>.</li>
		<li>The RSS feed may be unavailable.</li>
		<li>There could even be a <a href="http://code.google.com/p/wp-gcal-rss/issues/list">bug</a> somewhere in the plug-in.</li>
	</ul>
</div>

<script>
	jQuery(document).ready(function(){
	
		var cal = '<?php echo $pluginRootURL ?>/fetcher.php?c=<?php echo $c ?>&hmd=<?php echo $hmd ?>&mr=<?php echo $mr ?>';	
		var entries = null;
		var entry = null;
		var webcalLink = null;
		var dayDisplayFormat = 'dddd, MMM. dS';
		var reMilliseconds = /\.\d+/g;
		var timeDisplayFormat = 'h:mmtt';
		var currentDay = null;

		var handleSuccess = function(xml) {
			var Math_floor = Math.floor;
			var Date_parse = Date.parse;
			
			webcalLink = jQuery(xml).find('feed link[rel=alternate]').attr('href');
			entries = jQuery(xml).find('entry');
						
			var docFrag = document.createDocumentFragment();
			var theDL = document.createElement('dl');
			var aDTforDay = document.createElement('dt');
			var aDDforTitle = document.createElement('dd');
			var anEventLink = document.createElement('a');
			var aDDforTime = document.createElement('dd');
			
			for (var i=0,len=entries.length; i < len; i++) {
				entry = jQuery(entries[i]);
				title = entry.find('title').text();

				startTime = entry.find('[startTime]').attr('startTime');
				startTime = startTime.replace(reMilliseconds, '');

				endTime = entry.find('[endTime]').attr('endTime');
				endTime = endTime.replace(reMilliseconds, '');

				entry.startTime = new XDate(startTime);
				entry.endTime = new XDate(endTime);

				durationInTotalMinutes = entry.startTime.diffMinutes(entry.endTime);

				durationHours = Math_floor(durationInTotalMinutes/60);
				durationMinutes = ('0'+ (durationInTotalMinutes % 60)).substr(-2,2);
				entry.duration = { hours: durationHours, minutes: durationMinutes };

				st_day = entry.startTime.toString(dayDisplayFormat);
				st_time = entry.startTime.toString(timeDisplayFormat);
				et_day = entry.endTime.toString(dayDisplayFormat);
				et_time = entry.endTime.toString(timeDisplayFormat);
				
				link = entry.find('link[rel=alternate]').attr('href');
				
				if (currentDay !== st_day) {
					currentDay = st_day;
					var thisDTforDay = aDTforDay.cloneNode(false);
					thisDTforDay.innerHTML = st_day;
					theDL.appendChild(thisDTforDay);
				}
				var thisDDforTitle = aDDforTitle.cloneNode(false);
				anEventLink.href = link + '&ctz=<?php echo $tz ?>';
				anEventLink.innerHTML = title;					
				thisDDforTitle.appendChild(anEventLink.cloneNode(true));
				theDL.appendChild(thisDDforTitle);

				var thisDDforTime = aDDforTime.cloneNode(false);
				thisDDforTime.className = 'time';
				thisDDforTime.innerHTML = st_time.substr(0,st_time.length-1).toLowerCase() + '-' + et_time.substr(0,et_time.length-1).toLowerCase();					
				theDL.appendChild(thisDDforTime);

			}
			
			var theClosingParagraph = document.createElement('p');
			var theCalendarLink = document.createElement('a');
			theCalendarLink.href = webcalLink + '&ctz=<?php echo $tz ?>';
			theCalendarLink.innerHTML = 'Whole calendar';
			theClosingParagraph.appendChild(theCalendarLink);

			docFrag.appendChild(theDL);
			docFrag.appendChild(theClosingParagraph);

			var theParentDiv = jQuery('#wp-gcal-rss_dates');
			theParentDiv[0].appendChild(docFrag);
			
			jQuery('#wp-gcal-rss_loading').hide();
			theParentDiv.slideDown();
		};
		
		var handleError = function(x, t, e) {
			jQuery('#wp-gcal-rss_loading').hide();
			jQuery('#wp-gcal-rss_error').slideDown();
		};
		
		jQuery.ajax({
			url: cal,
			dataType: 'xml',
			success: handleSuccess,
			error: handleError
		});
	
	
	});
</script>





<?php
}


function widget_wp_gcal_rss_init() {

	if (!function_exists('wp_register_sidebar_widget')) {
		return;
	}

	function widget_wp_gcal_rss($args) {

		if (!$options = get_option('wp_gcal_rss')) {
			$options = array('wp_gcal_rss_t'=>'', 'wp_gcal_rss_c'=>'', 'wp_gcal_rss_tz'=>'', 'wp_gcal_rss_hmd'=>21, 'wp_gcal_rss_mr'=>25);
		}

		extract($args);
		echo $before_widget . $before_title . $options['wp_gcal_rss_t']. $after_title;
		wp_gcal_rss($options['wp_gcal_rss_t'], $options['wp_gcal_rss_c'], $options['wp_gcal_rss_tz'], $options['wp_gcal_rss_hmd'], $options['wp_gcal_rss_mr']);
		echo $after_widget;
		
	}
	function widget_wp_gcal_rss_options() {

		if (!$options = get_option('wp_gcal_rss')) {
			$options = array(
				'wp_gcal_rss_t' => '',
				'wp_gcal_rss_c' => '',
				'wp_gcal_rss_tz' => 'America/Chicago',
				'wp_gcal_rss_hmd' => 21,
				'wp_gcal_rss_mr' => 25
			);
		}

		if ($_POST['wp_gcal_rss_submit']) {
			$options = array(
				'wp_gcal_rss_t' => $_POST['wp_gcal_rss_t_v'],
				'wp_gcal_rss_c' => $_POST['wp_gcal_rss_c_v'],
				'wp_gcal_rss_tz' => $_POST['wp_gcal_rss_tz_v'],
				'wp_gcal_rss_hmd' => $_POST['wp_gcal_rss_hmd_v'],
				'wp_gcal_rss_mr' => $_POST['wp_gcal_rss_mr_v']
			);
			update_option('wp_gcal_rss', $options);
		} ?>

<p>Title:<input type="text" name="wp_gcal_rss_t_v" value="<?php echo $options['wp_gcal_rss_t'] ?>" id="wp_gcal_rss_t_v" /></p>
<p>Calendar identifier:<input type="text" name="wp_gcal_rss_c_v" value="<?php echo $options['wp_gcal_rss_c'] ?>" id="wp_gcal_rss_c_v" /></p>
<p>Time Zone:
<select id="wp_gcal_rss_tz_v" name="wp_gcal_rss_tz_v">
<?php
	$timezones = array('Africa/Abidjan','Africa/Accra','Africa/Addis_Ababa','Africa/Algiers','Africa/Asmera','Africa/Bamako','Africa/Bangui','Africa/Banjul','Africa/Bissau','Africa/Blantyre','Africa/Brazzaville','Africa/Bujumbura','Africa/Cairo','Africa/Casablanca','Africa/Ceuta','Africa/Conakry','Africa/Dakar','Africa/Dar_es_Salaam','Africa/Djibouti','Africa/Douala','Africa/El_Aaiun','Africa/Freetown','Africa/Gaborone','Africa/Harare','Africa/Johannesburg','Africa/Kampala','Africa/Khartoum','Africa/Kigali','Africa/Kinshasa','Africa/Lagos','Africa/Libreville','Africa/Lome','Africa/Luanda','Africa/Lubumbashi','Africa/Lusaka','Africa/Malabo','Africa/Maputo','Africa/Maseru','Africa/Mbabane','Africa/Mogadishu','Africa/Monrovia','Africa/Nairobi','Africa/Ndjamena','Africa/Niamey','Africa/Nouakchott','Africa/Ouagadougou','Africa/Porto','Africa/Sao_Tome','Africa/Timbuktu','Africa/Tripoli','Africa/Tunis','Africa/Windhoek','America/Adak','America/Anchorage','America/Anguilla','America/Antigua','America/Aruba','America/Asuncion','America/Barbados','America/Belize','America/Bogota','America/Boise','America/Buenos_Aires','America/Caracas','America/Catamarca','America/Cayenne','America/Cayman','America/Chicago','America/Cordoba','America/Costa_Rica','America/Cuiaba','America/Curacao','America/Dawson_Creek','America/Dawson','America/Denver','America/Detroit','America/Dominica','America/Edmonton','America/El_Salvador','America/Ensenada','America/Fortaleza','America/Glace_Bay','America/Godthab','America/Goose_Bay','America/Grand_Turk','America/Grenada','America/Guadeloupe','America/Guatemala','America/Guayaquil','America/Guyana','America/Halifax','America/Havana','America/Indianapolis','America/Inuvik','America/Iqaluit','America/Jamaica','America/Jujuy','America/Juneau','America/La_Paz','America/Lima','America/Los_Angeles','America/Louisville','America/Maceio','America/Managua','America/Manaus','America/Martinique','America/Mazatlan','America/Mendoza','America/Menominee','America/Mexico_City','America/Miquelon','America/Montevideo','America/Montreal','America/Montserrat','America/Nassau','America/New_York','America/Nipigon','America/Nome','America/Noronha','America/Panama','America/Pangnirtung','America/Paramaribo','America/Phoenix','America/Port_of_Spain','America/Port','America/Porto_Acre','America/Puerto_Rico','America/Rainy_River','America/Rankin_Inlet','America/Regina','America/Rosario','America/Santiago','America/Santo_Domingo','America/Sao_Paulo','America/Scoresbysund','America/Shiprock','America/St_Johns','America/St_Kitts','America/St_Lucia','America/St_Thomas','America/St_Vincent','America/Swift_Current','America/Tegucigalpa','America/Thule','America/Thunder_Bay','America/Tijuana','America/Tortola','America/Vancouver','America/Whitehorse','America/Winnipeg','America/Yakutat','America/Yellowknife','Antarctica/Casey','Antarctica/DumontDUrville','Antarctica/Mawson','Antarctica/McMurdo','Antarctica/Palmer','Antarctica/South_Pole','Arctic/Longyearbyen','Asia/Aden','Asia/Alma','Asia/Amman','Asia/Anadyr','Asia/Aqtau','Asia/Aqtobe','Asia/Ashkhabad','Asia/Baghdad','Asia/Bahrain','Asia/Baku','Asia/Bangkok','Asia/Beirut','Asia/Bishkek','Asia/Brunei','Asia/Calcutta','Asia/Chungking','Asia/Colombo','Asia/Dacca','Asia/Damascus','Asia/Dubai','Asia/Dushanbe','Asia/Gaza','Asia/Harbin','Asia/Hong_Kong','Asia/Irkutsk','Asia/Ishigaki','Asia/Jakarta','Asia/Jayapura','Asia/Jerusalem','Asia/Kabul','Asia/Kamchatka','Asia/Karachi','Asia/Kashgar','Asia/Katmandu','Asia/Krasnoyarsk','Asia/Kuala_Lumpur','Asia/Kuching','Asia/Kuwait','Asia/Macao','Asia/Magadan','Asia/Manila','Asia/Muscat','Asia/Nicosia','Asia/Novosibirsk','Asia/Omsk','Asia/Phnom_Penh','Asia/Pyongyang','Asia/Qatar','Asia/Rangoon','Asia/Riyadh','Asia/Saigon','Asia/Seoul','Asia/Shanghai','Asia/Singapore','Asia/Taipei','Asia/Tashkent','Asia/Tbilisi','Asia/Tehran','Asia/Thimbu','Asia/Tokyo','Asia/Ujung_Pandang','Asia/Ulan_Bator','Asia/Urumqi','Asia/Vientiane','Asia/Vladivostok','Asia/Yakutsk','Asia/Yekaterinburg','Asia/Yerevan','Atlantic/Azores','Atlantic/Bermuda','Atlantic/Canary','Atlantic/Cape_Verde','Atlantic/Faeroe','Atlantic/Jan_Mayen','Atlantic/Madeira','Atlantic/Reykjavik','Atlantic/South_Georgia','Atlantic/St_Helena','Atlantic/Stanley','Australia/Adelaide','Australia/Brisbane','Australia/Broken_Hill','Australia/Darwin','Australia/Hobart','Australia/Lindeman','Australia/Lord_Howe','Australia/Melbourne','Australia/Perth','Australia/Sydney','Europe/Amsterdam','Europe/Athens','Europe/Belfast','Europe/Belgrade','Europe/Berlin','Europe/Bratislava','Europe/Brussels','Europe/Bucharest','Europe/Budapest','Europe/Chisinau','Europe/Copenhagen','Europe/Dublin','Europe/Gibraltar','Europe/Helsinki','Europe/Istanbul','Europe/Kaliningrad','Europe/Kiev','Europe/Lisbon','Europe/Ljubljana','Europe/London','Europe/Luxembourg','Europe/Madrid','Europe/Malta','Europe/Minsk','Europe/Monaco','Europe/Moscow','Europe/Oslo','Europe/Paris','Europe/Prague','Europe/Riga','Europe/Rome','Europe/Samara','Europe/San_Marino','Europe/Sarajevo','Europe/Simferopol','Europe/Skopje','Europe/Sofia','Europe/Stockholm','Europe/Tallinn','Europe/Tirane','Europe/Vaduz','Europe/Vatican','Europe/Vienna','Europe/Vilnius','Europe/Warsaw','Europe/Zagreb','Europe/Zurich','Indian/Antananarivo','Indian/Chagos','Indian/Christmas','Indian/Cocos','Indian/Comoro','Indian/Kerguelen','Indian/Mahe','Indian/Maldives','Indian/Mauritius','Indian/Mayotte','Indian/Reunion','Pacific/Apia','Pacific/Auckland','Pacific/Chatham','Pacific/Easter','Pacific/Efate','Pacific/Enderbury','Pacific/Fakaofo','Pacific/Fiji','Pacific/Funafuti','Pacific/Galapagos','Pacific/Gambier','Pacific/Guadalcanal','Pacific/Guam','Pacific/Honolulu','Pacific/Johnston','Pacific/Kiritimati','Pacific/Kosrae','Pacific/Kwajalein','Pacific/Majuro','Pacific/Marquesas','Pacific/Midway','Pacific/Nauru','Pacific/Niue','Pacific/Norfolk','Pacific/Noumea','Pacific/Pago_Pago','Pacific/Palau','Pacific/Pitcairn','Pacific/Ponape','Pacific/Port_Moresby','Pacific/Rarotonga','Pacific/Saipan','Pacific/Tahiti','Pacific/Tarawa','Pacific/Tongatapu','Pacific/Truk','Pacific/Wake','Pacific/Wallis','Pacific/Yap');
	foreach ($timezones as $timezone) {
		if ($options['wp_gcal_rss_tz'] == $timezone) { ?>
			<option selected="selected"><?php echo $timezone ?></option>
<?php	} else { ?>
			<option><?php echo $timezone ?></option>
<?php	}
	}
?>
</select>
</p>
<p>How many days:<input type="text" name="wp_gcal_rss_hmd_v" value="<?php echo $options['wp_gcal_rss_hmd'] ?>" id="wp_gcal_rss_hmd_v" /></p>
<p>Maximum returns:<input type="text" name="wp_gcal_rss_mr_v" value="<?php echo $options['wp_gcal_rss_mr'] ?>" id="wp_gcal_rss_mr_v" /></p>
<input type="hidden" id="wp_gcal_rsssubmit" name="wp_gcal_rss_submit" value="1" />

<?php
	}
	wp_register_sidebar_widget('wp_gcal_rss','wp_gcal_rss','widget_wp_gcal_rss');
	wp_register_widget_control('wp_gcal_rss','wp_gcal_rss', 'widget_wp_gcal_rss_options', null);

}
function wp_gcal_rss_css() {
	$wp_gcal_rss_css_file = '<link type="text/css" rel="stylesheet" href="'.get_bloginfo('wpurl') . '/wp-content/plugins/' . dirname( plugin_basename( __FILE__ ) ).'/wp-gcal-rss.css" />';
	echo $wp_gcal_rss_css_file;
}

add_action('wp_head', 'wp_gcal_rss_css');
add_action('plugins_loaded', 'widget_wp_gcal_rss_init');
wp_enqueue_script('jquery');
wp_enqueue_script( '', get_bloginfo( 'wpurl' ) . '/wp-content/plugins/' . dirname( plugin_basename( __FILE__ ) ) . '/xdate.js');
?>