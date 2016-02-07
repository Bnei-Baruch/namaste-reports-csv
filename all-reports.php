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
	$date_start_week = new DateTime($start_date);
	$start_week = $date_start_week->format("W");
	$date_end_week = new DateTime($end_date);
	$end_week = $date_end_week->format("W");
	$total_number_of_weeks = $end_week - $start_week;


	//check if not in the same year
	if($total_number_of_weeks < 0 ){
		$number_of_weeks = getIsoWeeksInYear(explode('-', $start_date)[0]);
		$total_number_of_weeks = ($number_of_weeks-$start_week+1) + $end_week;
	}

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

	/*
	* Loop from the start of the courses till the last record on weekly base 
	*/
	$weeks = array();
	$ts = strtotime($start_date);
    $start_week_date = (date('w', $ts) == 0) ? $ts : strtotime('last monday', $ts);
	$end_week_date = strtotime('+6 day', $start_week_date);

	for ( $i = 0; $i <= $total_number_of_weeks; $i++) {
		
		if ($i !=0) {
			$start_week_date = strtotime("+1 day", $end_week_date);
			$end_week_date = strtotime('+6 day', $start_week_date);
		}

		$weeks[$i]['start']	= date('Y-m-d',$start_week_date);
		$weeks[$i]['end']	= date('Y-m-d',$end_week_date);

		$request = get_statistics($weeks[$i]['start'],$weeks[$i]['end'],$lesson_id);
		if (count($request)==0) {
			$weeks[$i]['archive']  	= 0;
			$weeks[$i]['forum']		= 0;
			$weeks[$i]['webinarTT']	= 0;
			$weeks[$i]['webinarSF']	= 0;
			$weeks[$i]['webinarPH']	= 0;
			$weeks[$i]['webinarMS']	= 0;
			$weeks[$i]['webinarVS']	= 0;
		} else{ 

			foreach ($request as $element) {
				switch ($element["for_item_type"]) {
					case 'archive':
						$weeks[$i]['archive'] = $element["COUNT(*)"];
						break;
					
					case 'forum':
						$weeks[$i]['forum'] = $element["COUNT(*)"];
						break;

					case 'webinarTT':
						$weeks[$i]['webinarTT'] = $element["COUNT(*)"];
						break;

					case 'webinarSF':
						$weeks[$i]['webinarSF'] = $element["COUNT(*)"];
						break;
					case 'webinarPH':
						$weeks[$i]['webinarPH'] = $element["COUNT(*)"];
						break;

					case 'webinarMS':
						$weeks[$i]['webinarMS'] = $element["COUNT(*)"];
						break;

					case 'webinarVS':
						$weeks[$i]['webinarVS'] = $element["COUNT(*)"];
						break;
				}
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
								 ->setLastModifiedBy("Maarten Balliauw")
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
				;

	// Add single weeks
	$letter = "C";
	foreach ($weeks as $week) {

		if (!array_key_exists('webinarTT', $week)) {
			$week['webinarTT']=0;
		}

		if (!array_key_exists('webinarSF', $week)) {
			$week['webinarSF']=0;
		}

		if (!array_key_exists('webinarPH', $week)) {
			$week['webinarPH']=0;
		}

		if (!array_key_exists('webinarMS', $week)) {
			$week['webinarMS']=0;
		}

		if (!array_key_exists('webinarVS', $week)) {
			$week['webinarVS']=0;
		}

		if (!array_key_exists('archive', $week)) {
			$week['archive']=0;
		}

		if (!array_key_exists('forum', $week)) {
			$week['forum']=0;
		}

		$letter++;
		$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue($letter.'1', $week['start'].' - '.$week['end'])
				->setCellValue($letter.'2', $week['webinarTT'])
				->setCellValue($letter.'3', $week['webinarSF'])
				->setCellValue($letter.'4', $week['webinarPH'])
				->setCellValue($letter.'5', $week['webinarMS'])
				->setCellValue($letter.'6', $week['webinarVS'])
				->setCellValue($letter.'7', $week['archive'])
				->setCellValue($letter.'8', $week['forum'])
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


function get_statistics($start_date, $end_date,$lesson_id){
	global $wpdb;

	$sql = "SELECT `for_item_type`, COUNT(*) FROM " . $wpdb->prefix . "namaste_history WHERE `date`BETWEEN '".$start_date."' AND '".$end_date."' AND `for_item_id` = ".$lesson_id." GROUP BY `for_item_type`";

	$result = $wpdb->get_results($sql,ARRAY_A);

	return $result;
}

// returns weeks in a year
function getIsoWeeksInYear($year) {
    $date = new DateTime;
    $date->setISODate($year, 53);
    return ($date->format("W") === "53" ? 53 : 52);
}