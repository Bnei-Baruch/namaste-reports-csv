<?php 
	//get the WordPress goodies
	require("../../../wp-blog-header.php");
	
	//check if logged in and admin
	if(!is_user_logged_in() && !current_user_can('manage_options' )){
		die();
	}
	
	//register vars
	global $wpdb;
	$overall['archive']		= array();
	$overall['forum']		= array();
	$overall['webinarTT']	= array();
	$overall['webinarSF']	= array();
	$overall['webinarPH']	= array();
	$overall['webinarMS']	= array();
	$overall['webinarVS']	= array();

	//options vars
	$lesson_id 		= get_option( 'namaste_csv_lesson_id' );
	$start_date		= get_option('namaste_csv_start_date');
	$end_date		= get_option('namaste_csv_end_date');

	//check if all options are set
	if (!$lesson_id || !$start_date || !$end_date) {
		die();
	}
	
	//start and end weeks
	$date_start_day = new DateTime($start_date);
	$date_end_day = new DateTime($end_date);
	$difference = $date_start_day->diff($date_end_day);
	$total_number_of_days = $difference->days;


	//get total results with one query (one to rule them all)
	$one_query = $wpdb->get_row("SELECT
	 SUM(IF(for_item_type ='forum'AND for_item_id =".$lesson_id." ,1,0)) AS FORUM,
	 SUM(IF(for_item_type ='archive'AND for_item_id =".$lesson_id." ,1,0)) AS ARCHIVE,
	 SUM(IF(for_item_type ='webinarTT'AND for_item_id =".$lesson_id." ,1,0)) AS WEBINARTT,
	 SUM(IF(for_item_type ='webinarSF'AND for_item_id =".$lesson_id." ,1,0)) AS WEBINARSF,
	 SUM(IF(for_item_type ='webinarPH'AND for_item_id =".$lesson_id." ,1,0)) AS WEBINARPH,
	 SUM(IF(for_item_type ='webinarMS'AND for_item_id =".$lesson_id." ,1,0)) AS WEBINARMS,
	 SUM(IF(for_item_type ='webinarVS'AND for_item_id =".$lesson_id." ,1,0)) AS WEBINARVS
	FROM " . $wpdb->prefix. "namaste_history ",ARRAY_A );
	
	$total_forum 		= $one_query['FORUM']; 		//forum
	$total_archive 		= $one_query['ARCHIVE']; 	//Archive
	$total_webinarTT 	= $one_query['WEBINARTT']; 	//Toronto
	$total_webinarSF 	= $one_query['WEBINARSF']; 	//San Francisko
	$total_webinarPH 	= $one_query['WEBINARPH']; 	//Petah
	$total_webinarMS 	= $one_query['WEBINARMS']; 	//Moskva
	$total_webinarVS 	= $one_query['WEBINARVS']; 	//voskresenya
	
	
	/* BB Dev queries to get all users ids from each item type */
	$users['FORUM'] = $wpdb->get_row('SELECT user_id FROM wp_namaste_history WHERE (for_item_id ='.$lesson_id.'AND for_item_type = forum)', ARRAY_A);
	$users['ARCHIVE'] = $wpdb->get_row('SELECT user_id FROM wp_namaste_history WHERE (for_item_id ='.$lesson_id.'AND for_item_type = array)', ARRAY_A);
	$users['WEBINARTT'] = $wpdb->get_row('SELECT user_id FROM wp_namaste_history WHERE (for_item_id ='.$lesson_id.'AND for_item_type = webinarTT)', ARRAY_A);
	$users['WEBINARSF'] = $wpdb->get_row('SELECT user_id FROM wp_namaste_history WHERE (for_item_id ='.$lesson_id.'AND for_item_type = webinarSF)', ARRAY_A);
	$users['WEBINARPH'] = $wpdb->get_row('SELECT user_id FROM wp_namaste_history WHERE (for_item_id ='.$lesson_id.'AND for_item_type = webinarPH)', ARRAY_A);
	$users['WEBINARMS'] = $wpdb->get_row('SELECT user_id FROM wp_namaste_history WHERE (for_item_id ='.$lesson_id.'AND for_item_type = webinarMS)', ARRAY_A);
	$users['WEBINARVS'] = $wpdb->get_row('SELECT user_id FROM wp_namaste_history WHERE (for_item_id ='.$lesson_id.'AND for_item_type = webinarVS)', ARRAY_A);
	
	/*
	$users['FORUM'] = $wpdb->get_row('SELECT history.id, users.user_login, signups.meta FROM wp_signups AS signups 
     INNER JOIN wp_users AS users ON signups.user_login = users.user_login 
	 INNER JOIN wp_namaste_history AS history ON users.user_id = history.user_id WHERE (history.for_item_id ='.$lesson_id.' 
	 AND history.for_item_type = forum)', ARRAY_A);
	
	$users['ARCHIVE'] = $wpdb->get_row('SELECT history.id, users.user_login, signups.meta FROM wp_signups AS signups 
     INNER JOIN wp_users AS users ON signups.user_login = users.user_login 
	 INNER JOIN wp_namaste_history AS history ON users.user_id = history.user_id WHERE (history.for_item_id ='.$lesson_id.' 
	 AND history.for_item_type = archive)', ARRAY_A);
	
	$users['WEBINARTT'] = $wpdb->get_row('SELECT history.id, users.user_login, signups.meta FROM wp_signups AS signups 
     INNER JOIN wp_users AS users ON signups.user_login = users.user_login 
	 INNER JOIN wp_namaste_history AS history ON users.user_id = history.user_id WHERE (history.for_item_id ='.$lesson_id.' 
	 AND history.for_item_type = webinarTT)', ARRAY_A);

	$users['WEBINARSF'] = $wpdb->get_row('SELECT history.id, users.user_login, signups.meta FROM wp_signups AS signups 
     INNER JOIN wp_users AS users ON signups.user_login = users.user_login 
	 INNER JOIN wp_namaste_history AS history ON users.user_id = history.user_id WHERE (history.for_item_id ='.$lesson_id.' 
	 AND history.for_item_type = webinarSF)', ARRAY_A);

	$users['WEBINARPH'] = $wpdb->get_row('SELECT history.id, users.user_login, signups.meta FROM wp_signups AS signups 
     INNER JOIN wp_users AS users ON signups.user_login = users.user_login 
	 INNER JOIN wp_namaste_history AS history ON users.user_id = history.user_id WHERE (history.for_item_id ='.$lesson_id.' 
	 AND history.for_item_type = webinarPH)', ARRAY_A);

	$users['WEBINARMS'] = $wpdb->get_row('SELECT history.id, users.user_login, signups.meta FROM wp_signups AS signups 
     INNER JOIN wp_users AS users ON signups.user_login = users.user_login 
	 INNER JOIN wp_namaste_history AS history ON users.user_id = history.user_id WHERE (history.for_item_id ='.$lesson_id.' 
	 AND history.for_item_type = webinarMS)', ARRAY_A);

	$users['WEBINARVS'] = $wpdb->get_row('SELECT history.id, users.user_login, signups.meta FROM wp_signups AS signups 
     INNER JOIN wp_users AS users ON signups.user_login = users.user_login 
	 INNER JOIN wp_namaste_history AS history ON users.user_id = history.user_id WHERE (history.for_item_id ='.$lesson_id.' 
	 AND history.for_item_type = webinarVS)', ARRAY_A);
	*/
	
	/*
	* Loop from the start of the courses till the last record on dayly base 
	*/
	$interval = DateInterval::createFromDateString('1 day');
	$period = new DatePeriod($date_start_day, $interval, $date_end_day);
	
	foreach ( $period as $date) {
		
		// init data fields
		$days[$date->format('Y-m-d')]['header'] = $date->format('Y-m-d');
		$days[$date->format('Y-m-d')]['male']		= 0;
		$days[$date->format('Y-m-d')]['female']		= 0;
		$days[$date->format('Y-m-d')]['18-24']		= 0;
		$days[$date->format('Y-m-d')]['25-34']		= 0;
		$days[$date->format('Y-m-d')]['35-44']		= 0;
		$days[$date->format('Y-m-d')]['45-54']		= 0;
		$days[$date->format('Y-m-d')]['55plus']		= 0;
		
		$request = get_statistics($date,$lesson_id);
		if (count($request)==0) {
			$days[$date->format('Y-m-d')]['archive']  	= 0;
			$days[$date->format('Y-m-d')]['forum']		= 0;
			$days[$date->format('Y-m-d')]['webinarTT']	= 0;
			$days[$date->format('Y-m-d')]['webinarSF']	= 0;
			$days[$date->format('Y-m-d')]['webinarPH']	= 0;
			$days[$date->format('Y-m-d')]['webinarMS']	= 0;
			$days[$date->format('Y-m-d')]['webinarVS']	= 0;
			$days[$date->format('Y-m-d')]['male']		= 0;
			$days[$date->format('Y-m-d')]['female']		= 0;
			$days[$date->format('Y-m-d')]['18-24']		= 0;
			$days[$date->format('Y-m-d')]['25-34']		= 0;
			$days[$date->format('Y-m-d')]['35-44']		= 0;
			$days[$date->format('Y-m-d')]['45-54']		= 0;
			$days[$date->format('Y-m-d')]['55plus']		= 0;
			
		} else{ 

			foreach ($request as $element) {
				switch ($element["for_item_type"]) {
					case 'archive':
						$days[$date->format('Y-m-d')]['archive'] = $element["COUNT(*)"];
						break;
					case 'forum':
						$days[$date->format('Y-m-d')]['forum'] = $element["COUNT(*)"];
						break;
					case 'webinarTT':
						$days[$date->format('Y-m-d')]['webinarTT'] = $element["COUNT(*)"];
						break;
					case 'webinarSF':
						$days[$date->format('Y-m-d')]['webinarSF'] = $element["COUNT(*)"];
						break;
					case 'webinarPH':
						$days[$date->format('Y-m-d')]['webinarPH'] = $element["COUNT(*)"];
						break;
					case 'webinarMS':
						$days[$date->format('Y-m-d')]['webinarMS'] = $element["COUNT(*)"];
						break;
					case 'webinarVS':
						$days[$date->format('Y-m-d')]['webinarVS'] = $element["COUNT(*)"];
						break;
				}
			}
		// Processing users count by gender and age groups
		$users = get_users_by_date($date, $lesson_id, 'archive');
		foreach ($users as $user) {
			$gender = xprofile_get_field_data('gender', $user['user_id']);
			if ($gender == "male")
				$days[$date->format('Y-m-d')]['male']++;
			else if ($gender == "female")
				$days[$date->format('Y-m-d')]['female']++;
			
			$age = xprofile_get_field_data('age', $user['user_id']);
			
			if ($age >= 18 && $age <= 24)
				$days[$date->format('Y-m-d')]['18-24']++;
			elseif ($age >= 25 && $age <= 34)
				$days[$date->format('Y-m-d')]['25-34']++;
			elseif ($age >= 35 && $age <= 44)
				$days[$date->format('Y-m-d')]['35-44']++;
			elseif ($age >= 45 && $age <= 54)
				$days[$date->format('Y-m-d')]['45-54']++;
			elseif ($age >= 55)
				$days[$date->format('Y-m-d')]['55plus']++;
			
		}
		
		}

	}

	/** Error reporting */
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	date_default_timezone_set('Europe/London');

	if (PHP_SAPI == 'cli')
		die('This example should only be run from a Web Browser');

	/** Include PHPExcel */
	require_once dirname(__FILE__) . '/excel/Classes/PHPExcel.php';

	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();

	// Set document properties
	$objPHPExcel->getProperties()->setCreator("Kabala")
								 ->setLastModifiedBy("Som (c)")
								 ->setTitle("Office 2007 XLSX Test Document")
								 ->setSubject("Office 2007 XLSX Test Document")
								 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
								 ->setKeywords("office 2007 openxml php")
								 ->setCategory("Test result file");

	// Add some data
	$objPHPExcel->setActiveSheetIndex(0)
	            ->setCellValue('A1', 'Стат показатель')
	            ->setCellValue('A3', 'Посещение занятия в среду с начала курса')
	            ->setCellValue('A6', 'Посещение занятия в восресенье 16:00 мск с начала курса')
	            ->setCellValue('A7', 'Просмотр записи урока в архиве')
	            ->setCellValue('A8', 'Aктивность участия на форуме')
	            ->setCellValue('A9', 'Male total')
	            ->setCellValue('A10', 'Female total')
	            ->setCellValue('A11', 'Gender 18 - 24')
	            ->setCellValue('A12', 'Gender 25 - 34')
	            ->setCellValue('A13', 'Gender 35 - 44')
	            ->setCellValue('A14', 'Gender 45 - 54')
	            ->setCellValue('A15', 'Gender 55 +')
	            ->setCellValue('B2', 'Торонто: 6:00 мск')
	            ->setCellValue('B3', 'Сан-Франциско: 8:00 мск')
	            ->setCellValue('B4', 'Петах-Тиква: 17:00 по мск')
	            ->setCellValue('B5', 'Петах-Тиква: 20:00 по мск')
	            ;
	// Add totals
	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('C1', 'Тотал')
				->setCellValue('C2', $total_webinarTT)
				->setCellValue('C3', $total_webinarSF)
				->setCellValue('C4', $total_webinarPH)
				->setCellValue('C5', $total_webinarMS)
				->setCellValue('C6', $total_webinarVS)
				->setCellValue('C7', $total_archive)
				->setCellValue('C8', $total_forum)
				->setCellValue('C9', $days[$date->format('Y-m-d')]['male'])
				->setCellValue('C10', $days[$date->format('Y-m-d')]['female'])
				->setCellValue('C11', $days[$date->format('Y-m-d')]['18-24'])
				->setCellValue('C12', $days[$date->format('Y-m-d')]['25-34'])
				->setCellValue('C13', $days[$date->format('Y-m-d')]['34-45'])
				->setCellValue('C14', $days[$date->format('Y-m-d')]['44-55'])
				->setCellValue('C15', $days[$date->format('Y-m-d')]['55plus'])
				;

	// Add single day column
	$letter = "C";
	foreach ($days as $day) {

		if (!array_key_exists('webinarTT', $day)) {
			$day['webinarTT']=0;
		}

		if (!array_key_exists('webinarSF', $day)) {
			$day['webinarSF']=0;
		}

		if (!array_key_exists('webinarPH', $day)) {
			$day['webinarPH']=0;
		}

		if (!array_key_exists('webinarMS', $day)) {
			$day['webinarMS']=0;
		}

		if (!array_key_exists('webinarVS', $day)) {
			$day['webinarVS']=0;
		}

		if (!array_key_exists('archive', $day)) {
			$day['archive']=0;
		}

		if (!array_key_exists('forum', $day)) {
			$day['forum']=0;
		}

		$letter++;
		$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue($letter.'1', $day['header'])
				->setCellValue($letter.'2', $day['webinarTT'])
				->setCellValue($letter.'3', $day['webinarSF'])
				->setCellValue($letter.'4', $day['webinarPH'])
				->setCellValue($letter.'5', $day['webinarMS'])
				->setCellValue($letter.'6', $day['webinarVS'])
				->setCellValue($letter.'7', $day['archive'])
				->setCellValue($letter.'8', $day['forum'])
				;
	}

	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle('экспорт отчета');


	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);


	// Redirect output to a client’s web browser (Excel5)
	header('Content-Type: application/vnd.ms-excel');
	header('Content-Disposition: attachment;filename="Namaste-reposts-KabAcademy.xls"');
	header('Cache-Control: max-age=0');
	// If you're serving to IE 9, then the following may be needed
	header('Cache-Control: max-age=1');

	// If you're serving to IE over SSL, then the following may be needed
	header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
	header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
	header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
	header ('Pragma: public'); // HTTP/1.0

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$objWriter->save('php://output');
	exit;


function get_statistics($the_date, $lesson_id){
	global $wpdb;

	$sql = "SELECT `for_item_type`, COUNT(*) FROM " . $wpdb->prefix . "namaste_history WHERE `date` = '".$the_date->format('Y-m-d')."' AND `for_item_id` = ".$lesson_id." GROUP BY `for_item_type`";

	$result = $wpdb->get_results($sql,ARRAY_A);

	return $result;
}


function get_users_by_date($the_date, $lesson_id){
	
	return $wpdb->get_results('SELECT user_id FROM wp_namaste_history WHERE (date = \''.$the_date->format('Y-m-d').'\' AND for_item_id ='.$lesson_id.')', ARRAY_A);	
	
}

// returns weeks in a year
function getIsoDaysInYear($year) {
    $date = new DateTime;
    $date->setISODate($year, 53);
    return ($date->format("W") === "53" ? 366 : 365);
}