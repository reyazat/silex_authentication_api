<?php 
namespace Helper;

use Helper\Utility;

class DateTimeFunc extends Utility{
	protected $app;
	
	public function __construct($app){		
		$this->app = $app;	
    }
	
	public function isValidTimezone($timezone) {
		$timezone = self::trm($timezone);
		if (self::notEmpty($timezone)) {
		return in_array($timezone, \DateTimeZone::listIdentifiers());
		}else{
			return false;
		}
	}
	
	public function convertDateTime($dateTime, $oldTimeZone = 'UTC' , $newTimeZone = 'UTC' , $format = 'Y-m-d H:i:s')
    {
        $dateTime = date_create($dateTime, timezone_open($oldTimeZone));
		date_timezone_set($dateTime, timezone_open($newTimeZone));
		return($dateTime->format($format));
    }
	
	public function createByTimezone($date , $timeZone = 'UTC', $format = 'Y-m-d H:i:s')
	{
	    $date = date_create($date, timezone_open($timeZone));
		return($date->format($format));
	}
		

	public function isDate($date)
	{
	    return (bool) date_create($date, timezone_open('UTC'));
	}
		
	public function nowDate($format = 'Y-m-d'){
		
		return date($format,strtotime(date('Y-m-d')));
		
	}
	
	public function nowDateTime($format = 'Y-m-d H:i:s'){
		
		return date($format,strtotime(date('Y-m-d H:i:s')));
		
	}
	
	public function getDate($date,$when,$format = 'Y-m-d'){
		
		// first day of next month
		// last day of next month
		// last day of last month
		// first day of last month
		// first day of this month
		// last day of this month
		return date($format, strtotime($when.' '.$date));
		
	}
	
	public function checkValidDate($date,$format = 'Y-m-d'){
		
		if (\DateTime::createFromFormat($format, $date) !== FALSE) {
			
		  return true;
			
		}else{
			
			return false;
			
		}
		
	}
	
	public function changeDateFormat($date , $format){
	
		$dateTimeObj = new \DateTime($date);
        $formattedTimeDate = $dateTimeObj->format($format);
        return $formattedTimeDate;
	}
	
	public function dateRepeat($date = '', $period = '', $repetition_every = '',$format = 'Y-m-d'){

		if ($period == 'Monthly') {

			$return_period = self::date_addMonth($date,self::notEmpty($repetition_every)?$repetition_every:1,$format);
			
		}
		if ($period == 'Quarterly') {
			
			$return_period = self::date_addMonth($date,3,$format);
			
		}
		if ($period == 'Annual' OR $period == 'Yearly') {

			$return_period = self::date_addYear($date,self::notEmpty($repetition_every)?$repetition_every:1,$format);
			
		}
		if ($period == 'Weekly') {

			$return_period = self::date_add($date,self::notEmpty($repetition_every)?($repetition_every * 7):7,$format);
			
		}
		if ($period == 'Fortnightly') {
			
			$return_period = self::date_add($date,14,$format);
			
		}
		if ($period == 'Four Weekly') {
			
			$return_period = self::date_add($date, 28,$format);
			
		}
		if ($period == 'Daily') {
			
			$return_period = self::date_add($date,self::notEmpty($repetition_every)?$repetition_every:1,$format);
			
		}
		
		return $return_period;
	}
	
	public function date_add($sdate,$day,$format = 'Y-m-d'){
		
		$sdate = self::changeDateFormat($sdate, 'Y-m-d');
		//if sdate not date format
		try {
			$date=date_create($sdate);
		}catch (Exception $e) {
			return '';
		}
		
        date_add($date,date_interval_create_from_date_string($day." days"));
		
        return date_format($date,$format);
	}

	public function date_subDay($sdate,$day,$format = 'Y-m-d'){
		
		$sdate = self::changeDateFormat($sdate, 'Y-m-d');
		//if sdate not date format
		try {
			$date=date_create($sdate);
		}catch (Exception $e) {
			return '';
		}
		
        date_sub($date,date_interval_create_from_date_string($day." days"));
        return date_format($date,$format);
	}

	public function date_addMonth($sdate,$count_month,$format = 'Y-m-d'){
		
		$sdate = self::changeDateFormat($sdate, 'Y-m-d');
		
		//if sdate not date format
		try {
			$date = $date_temp2 =date_create($sdate);
		}catch (Exception $e) {
			return '';
		}
		$date_temp = strtotime($sdate);

		$year = date("Y", $date_temp);
		$month = date("m", $date_temp);
		$day = date("d", $date_temp);

		$last_day = $date_temp2->modify('last day of this month')->format('d');

		$year += floor($count_month / 12);
		$count_month = $count_month % 12;
		$month += $count_month;
		
		if ($month > 12) {
			$year++;
			$month = $month % 12;
			if ($month === 0) {
				$month = 12;
			}

		}
		if (!checkdate($month, $day, $year) OR $day == $last_day) {
			
			$new_date = \DateTime::createFromFormat($format, $year . '-' . $month . '-01');
			$new_date->modify('last day of');
			
		} else {
			
			$new_date = \DateTime::createFromFormat($format, $year . '-' . $month . '-' . $day);
			
		}
        return date_format($new_date,$format);
	}

	public function date_subMonth($sdate,$count_month,$format = 'Y-m-d'){
		
		$sdate = self::changeDateFormat($sdate, 'Y-m-d');
		//if sdate not date format
		try {
			$date = $date_temp2 =date_create($sdate);
		}catch (Exception $e) {
			return '';
		}
		$date_temp = strtotime($sdate);

		$year = date("Y", $date_temp);
		$month = date("m", $date_temp);
		$day = date("d", $date_temp);

		$last_day = $date_temp2->modify('last day of this month')->format('d');
		
		$year -= floor($count_month/12);
        $count_month = $count_month%12;
        $month -= $count_month;
        if($month < 1) {
            $year --;
            $month = $month % 12;
            if($month === 0){
                $month = 12;
			}
			else{
				$month = 12 + $month;
			}
        }
		
        if(!checkdate($month, $day, $year) OR $day == $last_day) {
			
            $new_date = \DateTime::createFromFormat($format, $year.'-'.$month.'-01');
            $new_date->modify('last day of');

        }else {
			
            $new_date = \DateTime::createFromFormat($format, $year.'-'.$month.'-'.$day);
			
        }
        return date_format($new_date,$format);
	}
	
	
	public function date_addYear($sdate,$years,$format = 'Y-m-d'){
		
		$sdate = self::changeDateFormat($sdate, 'Y-m-d');
		//if sdate not date format
		try {
			$date=date_create($sdate);
		}catch (Exception $e) {
			return '';
		}
        date_add($date,date_interval_create_from_date_string($years." years"));
        return date_format($date,$format);
	}
	

	public function date_subYear($sdate,$years,$format = 'Y-m-d'){
		
		$sdate = self::changeDateFormat($sdate, 'Y-m-d');
		//if sdate not date format
		try {
			$date=date_create($sdate);
		} catch (Exception $e) {
			return '';
		}
        date_sub($date,date_interval_create_from_date_string($years." years"));
        return date_format($date,$format);
	}
	
	public function date2MicroTime($date){
		
        if (is_integer($date) || is_numeric($date)) {
            return intval($date);
        }
        else {
            return strtotime($date);
        }
		
    }
	
	/*
%	Literal %	%
Y	Years, numeric, at least 2 digits with leading 0	01, 03
y	Years, numeric	1, 3
M	Months, numeric, at least 2 digits with leading 0	01, 03, 12
m	Months, numeric	1, 3, 12
D	Days, numeric, at least 2 digits with leading 0	01, 03, 31
d	Days, numeric	1, 3, 31
a	Total number of days as a result of a DateTime::diff() or (unknown) otherwise	4, 18, 8123
H	Hours, numeric, at least 2 digits with leading 0	01, 03, 23
h	Hours, numeric	1, 3, 23
I	Minutes, numeric, at least 2 digits with leading 0	01, 03, 59
i	Minutes, numeric	1, 3, 59
S	Seconds, numeric, at least 2 digits with leading 0	01, 03, 57
s	Seconds, numeric	1, 3, 57
F	Microseconds, numeric, at least 6 digits with leading 0	007701, 052738, 428291
f	Microseconds, numeric	7701, 52738, 428291
R	Sign "-" when negative, "+" when positive	-, +
r	Sign "-" when negative, empty when positive	-,
Example : $interval->format('%R%a days')
	*/
	public function dateDiff($d1, $d2 , $format='%r%a'){
		$datetime1 = new \DateTime($d1);
		$datetime2 = new \DateTime($d2);
		$interval = $datetime1->diff($datetime2);
		return $interval->format($format);
	}
		
	public function dateDifference($d1, $d2, $select = 'day'){
		
        $d = (self::date2MicroTime($d1) - self::date2MicroTime($d2));
        if (strtolower($select) == 'day') {
            return abs(round($d / (60 * 60 * 24)));
        }
		else if (strtolower($select) == '-day') {
            return (floor($d / (60 * 60 * 24)));
        }

        return $d;

    }
	
	public function periodConvert($period,$format = 'Y-m-d'){
		
		$date = [];
		switch($period){
				
			case'planned' :
				
				$date['start'] = '';
				$date['end'] = '';
				
			break;
				
			case'overdue' :
				
				$date['start'] = '';
				$date['end'] = self::getDate(self::nowDate($format),'-1 day',$format);
				
			break;
				
			case'today' :
				
				$date['start'] = self::nowDate($format);
				$date['end'] = self::nowDate($format);
				
			break;
				
			case'tomorrow' :
				
				$date['start'] = self::getDate(self::nowDate($format),'+1 day',$format);
				$date['end'] = self::getDate(self::nowDate($format),'+1 day',$format);
				
			break;
				
			case'this week' :
				
				$date['start'] = self::getDate(self::nowDate($format),'monday this week',$format);
				$date['end'] = self::getDate(self::nowDate($format),'sunday this week',$format);
				
			break;
				
			case'next week' :
				
				$date['start'] = self::getDate(self::nowDate($format),'monday next week',$format);
				$date['end'] = self::getDate(self::nowDate($format),'sunday next week',$format);
				
			break;
				
			default: 
				
				$expoRange = [];
				$expoRange = explode(',',$period);
				
				$date['start'] = date($format,strtotime($expoRange[0]));
				$date['end'] = date($format,strtotime($expoRange[1]));
				
		}
		
		return $date;
		
	}

	
}
