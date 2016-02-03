<?php
/*
Plugin Name: Namaste CSV reports
Plugin URI: 
Description: Export weekly or overall reports. Get statistic of webinar, archive and lessons visits duing the week.
Author: al@shtrak.eu
Version: 0.1
Author URI: http://shtrak.eu/it
License: GPLv2 or later
Text Domain: namaste-csv
*/


/**
* Add extra menu items for admins
*/
add_action('namaste_lms_admin_menu', 'add_admin_menu');
function add_admin_menu() {
	add_submenu_page('namaste_options',__('Namaste CSV Reports','namaste-csv'), __('Namaste CSV Reports','namaste-csv'),'manage_options','namaste-csv', 'download_report');
}

/**
* Add text domain
*/
add_action('plugins_loaded', 'namste_csv_reports_textdomain');
function namste_csv_reports_textdomain() {
	load_plugin_textdomain( 'namaste-csv', false, dirname( plugin_basename( __FILE__ ) ) );
}

/**
* Admin panel part
*/
function download_report() {
	echo '<div class="wrap"><h2>'.__('Download Report','namaste-csv').'</h2>';

	//check if extensions loaded
	echo "<p>";
	if (!extension_loaded('gd')) {
		echo "PHP Extension GD not loaded (php_gd2)!</br>";
		die();
	} else {
		echo "PHP Extension is GD OK!</br>";
	}
	
	if (!extension_loaded('xml')) {
		echo "PHP Extension XML not loaded (php_xml)!</br>";
		die();
	} else {
		echo "PHP Extension is XML OK!</br>";
	}

	if (!extension_loaded('zip')) {
		echo "PHP Extension ZIP not loaded (php_zip)!</br>";
		die();
	} else {
		echo "PHP Extension is ZIP OK!</br>";
	}
	echo "</p>";
	
	//set the options form;
	echo load_the_form();

	//check if settigns are set
	if (get_option('namaste_csv_lesson_id') && get_option('namaste_csv_start_date') && get_option('namaste_csv_end_date')) {
		echo '<p style="font-weight:bold">'. __('You can download the report from ','namaste-csv'). '<a href="'.plugin_dir_url( __FILE__ ) .'all-reports.php" class="download-report-namste-csvs" attr-value="all">'.__('here.','namaste-csv').'</a></p>';
	}
	
	echo "</div>";
}

//Form with functions
function load_the_form(){
	$html = '';

	//check if exitst
	if (isset($_POST['lessons-id'])) {
		//validate 
		if ( get_posts($_POST['lessons-id'])) {
			//update or add database
			if(get_option('namaste_csv_lesson_id')){
				update_option( 'namaste_csv_lesson_id', $_POST['lessons-id'] );
			} else {
				add_option('namaste_csv_lesson_id', $_POST['lessons-id']  );
			}
			
		} else {
			//error msg
			$html .= '<p style="color:red;font-weight:bold;">'.__('Either lesson id is not in the data base or it is not a number.','namaste-csv').'</p>';
		}
		
	}

	//check if exitst
	if (isset($_POST['namaste_csv_start_date'])) {
		//validate 
		if (validate_date($_POST['namaste_csv_start_date'])) {
			//update database
			
			if(get_option('namaste_csv_start_date' )){
				update_option( 'namaste_csv_start_date', $_POST['namaste_csv_start_date'] );
			} else {
				add_option('namaste_csv_start_date', $_POST['namaste_csv_start_date']  );
			}
		} else {
			//error msg
			$html .= '<p style="color:red;font-weight:bold;">'.__('Incorrect date or date format.','namaste-csv').'</p>';
		}

		
		
	}

	if (isset($_POST['namaste_csv_end_date'])) {
		if (validate_date($_POST['namaste_csv_end_date'])) {
			//update database
			
			if(get_option('namaste_csv_end_date' )){
				update_option( 'namaste_csv_end_date', $_POST['namaste_csv_end_date'] );
			} else {
				add_option('namaste_csv_end_date', $_POST['namaste_csv_end_date']  );
			}
		} else {
			//error msg
			$html .= '<p style="color:red;font-weight:bold;">'.__('Incorrect date or date format.','namaste-csv').'</p>';
		}
	}

	$html .= "<form method='post'>";
	$html .= "<label for='lessons-id'>".__('Lessons id','namaste-csv')."</br><input type='number' name='lessons-id' id='lessons-id' value='".get_option('namaste_csv_lesson_id')."'></label></br>";
	$html .= "<label for='namaste_csv_start_date'>".__('Lessons start date','namaste-csv')."</br><input type='text' name='namaste_csv_start_date' id='namaste_csv_start_date' value='".get_option('namaste_csv_start_date')."' placeholder='format: yyyy-mm-dd'></label></br>";
	$html .= "<label for='namaste_csv_end_date'>".__('Lessons end date','namaste-csv')."</br><input type='text' name='namaste_csv_end_date' id='namaste_csv_end_date' value='".get_option('namaste_csv_end_date')."' placeholder='format: yyyy-mm-dd'></label></br>";
	$html .= "<input type='submit' value='".__('Submit','namaste-csv')."'>";
	$html .= "</form>";
	return $html;	
}

function validate_date($date){
	$test_arr  = explode('-', $date);//yyyy-mm-dd;

	if (count($test_arr) == 3) {
	    if (checkdate($test_arr[1], $test_arr[2], $test_arr[0])) {
	       return true; // valid date
	    } else {
	       return false; // problem with dates
	    }
	} else {
    	return false; // problem with input ...
	}
}