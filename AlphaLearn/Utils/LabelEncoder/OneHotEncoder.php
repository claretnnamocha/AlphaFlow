<?php
namespace AlphaLearn\Utils\LabelEncoder;
/**
 * 
 */
class OneHotEncoder
{
	
	function __construct(Array $column_data = [])
	{
		$this->data_set = $column_data;
	}

	function load_data(Array $column_data)
	{
		$this->data_set = $column_data;
	}

	private function process_data($data_set)
	{
		$columns = [];
		$key = 0;
		$labels = array_unique($data_set);
		foreach ($labels as $label) {
			foreach ($data_set as $data_label) {
				$columns[$key][] = ($label == $data_label) ? 1 : 0;
			}
			$key += 1;
		}
		return $columns;
	}

	function extract()
	{
		if (!isset($this->data_set)) {
			throw new Exception("Error there is no data to work with, load data first", 1);
			
		}
		return $this->process_data($this->data_set);
	}
}

$data = ['A','B','C','A','A','B','A','C','C','B','A'];

$ohe = new OneHotEncoder();
$ohe->load_data($data);
$columns = $ohe->extract();
print_r($columns);