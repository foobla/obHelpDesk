<?php
/**
* @package		$Id: table_chart.php 2 2013-07-30 08:16:00Z thongta $
* @author 		foobla.com
* @copyright	2007-2014 foobla.com. All rights reserved.
* @license		GNU/GPL.
*/

// no direct access
defined('_JEXEC') or die;

class SA_TableChart implements SA_Chart {

	var $package = 'table';
	
	var $data;
	var $dataHolder;
	var $graphContainer;
	var $columns;
	var $from;
	var $to;
	var $unit;
	var $type;

	function __construct($_data, $_columns, $_dataHolder = 'data', $_graphContainer = 'table_div',$_from, $_to, $_unit,$_type= '') {
		$this->data = $_data;
		$this->dataHolder = $_dataHolder;
		$this->graphContainer = $_graphContainer;
		$this->columns = $_columns;
		$this->from = $_from;
		$this->to = $_to;
		$this->unit= $_unit;
		$this->type = $_type;
		
	}
	
	public function loadGraphData($limit=0)
	{
		$report_fields_output = "";
		$report_output = "";
		
		$record_counter=0;
		$counter=0;
		//lay mot object stdClass
		foreach ($this->data as $sa_record) {
			$sa_record_fields = get_object_vars($sa_record);
			
			$field_counter=0;
			//lay cac Field Name va Field Data trong mot object
			foreach($sa_record_fields as $sa_field_name => $sa_field_value) {
				
				//neu la record dau tien, addColumn
				if ($record_counter==0) {
					//cot dau tien: mac dinh la string
					if ($field_counter==0) {
						$report_fields_output .= $this->dataHolder . ".addColumn('string', '" . JText::_($sa_field_name) . "');\n";
					
					} else {
					//cot tiep theo: 
					#TODO : check xem co trong columns khong
						$report_fields_output .= $this->dataHolder . ".addColumn('number', '" . JText::_($sa_field_name) . "');\n";								
					}
					
				}
				
				//neu la field dau tien, addValue
				if($field_counter ==0){
					$_row_name = $this->dataHolder . ".setValue(" . $record_counter . ", " . $field_counter . ", '" . $sa_field_value . "');\n";
					
				}else{
					//neu khong thi them setValue
					
					#TODO : check xem co trong columns khong
					$formatted_value = $sa_field_value;
					if (strlen($_row_name)>0) {
						$report_output .= $_row_name;
						$_row_name = '';
						$counter++;
					}
					$report_output .= $this->dataHolder . ".setValue(" . $record_counter . ", " . "1" . ", " . $sa_field_value . ", '" . $formatted_value . "');\n";
						
				}
				
				$field_counter ++;
			}
			$record_counter ++;
			
			//net set limit thi break
			if (($limit>0) && ($counter>$limit)) break;			
		}
		
		$report_columns_output = $this->dataHolder . ".addRows(" . $counter . ");";
		//var_dump($report_fields_output,$report_columns_output,$report_output);
		return $report_fields_output . $report_columns_output . $report_output;
	}
	
	public  function loadGraphData_1()
	{
		$record_counter=0;
		$data_date_name = '';
		$data_value_name = '';
		$list_data_date= '';
		$list_data_value='';
		
		foreach ($this->data as $sa_record) {
			$sa_record_fields = get_object_vars($sa_record);
			$field_counter=0;
			foreach($sa_record_fields as $sa_field_name => $sa_field_value) {
				if($record_counter==0){
					if($field_counter == 0){
						$data_date_name = $sa_field_name;
					}else{
						$data_value_name= $sa_field_name;
					}
					
				}
				if($field_counter == 0){
					$list_data_date[]=$sa_field_value;
				}else{
					$list_data_value[]= $sa_field_value;
				}
				$field_counter++;		
			}
			$record_counter++;	

		}
		$report_fields_output = $this->dataHolder . ".addColumn('string', '" . JText::_( $data_date_name) . "');\n";
		$report_fields_output .= $this->dataHolder . ".addColumn('number', '" . JText::_( $data_value_name) . "');\n";

		
		if($this->unit == "day"){
			
			$startTime = strtotime($this->from); 
			$endTime = strtotime($this->to); 
			
			// Loop between timestamps, 1 day at a time 
			$record_counter2 = 0;
			do {
			   	$startTime = strtotime('+1 day',$startTime); 
			   	$day= date("Y-m-d", $startTime);
	   			$value = "0";
				if($list_data_date !=null){
					foreach ($list_data_date as $lid => $ldate)
		   			{
			   			if($day == $ldate){
			   				$value = $list_data_value[$lid];
							break;
			   			}
					}
				}
	   			

				$list_data_date2[]= $day;
				$list_data_value2[] = $value;
				$record_counter2++;
			   
			} while ($startTime < $endTime);
			   
			  
		}else if ($this->unit == "month")
		{
			
		}
		$report_columns_output = $this->dataHolder . ".addRows(" . $record_counter2 . ");";
		
		//tao data cho graph
		$report_output="";
		for($i=0; $i < count($list_data_date2);$i++)
		{
			$report_output .= $this->dataHolder . ".setValue(" . $i . ", " . "0" . ", '" . $list_data_date2[$i] . "');\n";
			$report_output .= $this->dataHolder . ".setValue(" . $i . ", " . "1" . ", " . $list_data_value2[$i] . ", '" .  $list_data_value2[$i] . "');\n";
		}

		return $report_fields_output . $report_columns_output . $report_output;
		
		
	}
	public function getChartDataOutput($limit=0) {
		if($this->type=="show"){
			return $this->loadGraphData_1();
		}else{
			return $this->loadGraphData();	
		}
		
	}
	
	public function getPackage() {
		return $this->package;
	}

}

?>