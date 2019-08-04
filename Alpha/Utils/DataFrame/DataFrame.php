<?php
namespace Alpha\Utils\DataFrame;
/**
 * 
 */
class DataFrame
{

	#	Filters
	public const FILTER_EQUAL = 'EQ';
	public const FILTER_NOT_EQUAL = 'NEQ';
	public const FILTER_GREATER_OR_EQUAL = 'GEQ';
	public const FILTER_GREATER = 'GT';
	public const FILTER_LESS_OR_EQUAL = 'LEQ';
	public const FILTER_LESS = 'LT';

	function __construct(Array $data = [])
	{
		$this->data = $data;
	}

	public static function sum(Array $array)
	{
		return array_sum($array);
	}

	public static function mode($array)
	{
		return max($array);
	}

	public static function mean(Array $array)
	{
		return array_sum($array)/count($array);
	}

	public static function median(Array $array)
	{		
		sort($array);
		if ((count($array) % 2) == 0) {
			$_p = round((count($array) / 2));
			return ($array[$_p]+$array[$_p+1])/2;
		}else {
			$n = round((count($array) / 2));
			return $array[$n];
		}
	}

	public function fill_null($map)
	{
		foreach (array_keys($map) as $key) {
			if (!isset($this->data()[$key])) { throw new Exception("Error Column with identifier `{$key}` does not exist. Avaialable Column names are : ". implode(' , ', array_keys($this->data))); }
		}
		foreach (array_keys($map) as $key) {
			foreach ($this->data[$key] as $index => $value) {
				if (empty(trim($value))) { $this->data[$key][$index] = $map[$key]; }
			}
		}
		return $this;
	}

	public function column_size()
	{
		return count($this->data());
	}

	public function row_size()
	{
		$first_label = array_keys($this->data())[0];
		return count($this->data()[$first_label]);
	}

	public function dimension()
	{
		return "{$this->column_size()} x {$this->row_size()}";
	}

	public function data()
	{
		return $this->data;
	}

	public function index($index)
	{
		$f_key = array_keys($this->data())[0];
		if ($index > (count($this->data()[$f_key]) - 1)) {
			throw new Exception("Out of index Error");
		}

		$retval = [];

		foreach ($this->data() as $key => $value) {
			$retval[$key][] = $value[$index];
		}
		return new DataFrame($retval);
	}

	public function clone()
	{
		return new DataFrame($this->data()); 
	}

	public function random($no_of_records,int $seed=null)
	{

		$f_key = array_keys($this->data())[0];
		$tmp_data = $this->data()[$f_key];

		if ($no_of_records > (count($tmp_data))) {
			throw new Exception("Out of index Error");
		}
		$keys = array_keys($tmp_data);

		if (is_numeric($seed)) { srand($seed); }

		shuffle($keys);
		$random_columns = array_slice($keys,0,$no_of_records);

		if (is_numeric($seed)) { srand(); }

		foreach ($this->data() as $key => $value) {
			foreach ($random_columns as $index) {
				$retval[$key][] = $value[$index];
			}
		}

		return new DataFrame($retval);
	}

	public function add_column($data,$label)
	{
		if (count($data) != $this->row_size()) {
			throw new Exception("Error The datasets should have equal number of rows");
		}
		$this->data[$label] = $data;
		return $this;
	}

	public function add_columns($data,$labels)
	{
		if (count($data) != count($labels)) {
			throw new Exception("Error The columns should have equal number of labels");
		}
		foreach ($data as $key => $d) {	
			if (count($d) != $this->row_size()) {
				throw new Exception("Error The datasets should have equal number of rows");
			}
			$this->data[$labels[$key]] = $d;
		}
		return $this;
	}

	public static function read_csv($filename,$skip_lines=1,$first_line_is_label=true)
	{
		
		$output = [];
		$fd = fopen($filename, 'r');
		$line = 1;
		while ($data = fgetcsv($fd,1024)) {
			if ($line <= $skip_lines){
				$line++;
				if ($first_line_is_label) {
					foreach ($data as $title) {
						$output[$title] = [];
					}
				}
				continue;
			}
			foreach ($data as $key => $value) {
				$key = array_keys($output)[$key] ?? $key;
				$output[$key] = $output[$key] ?? [];
				$output[$key][] = is_numeric($value) ? floatval($value) : $value;
			}
			$line++;
		}
		$output = new DataFrame($output);
		$output->filename = $filename;
		return $output;
	}

	public function set_labels(...$labels)
	{
		if (count($labels) != count($this->data)) {
			throw new Exception("Labels do not match columns");
		}
		$d = [];
		$i = 0;
		foreach ($this->data as $key => $value) {
			$d[$labels[$i]] = $this->data[$key];
			$i++;
		}
		$this->data = $d;
		return $this;
	}

	public function label_column($index,$label)
	{
		if ((count($this->data()) - 1) < $index) {
			throw new Exception("Error Out Index, DataFrame column selected");
		}
		$column_label = array_keys($this->data())[$index];
		$this->data[$label] = $this->data[$column_label];
		unset($this->data[$column_label]);
		return $this;
	}

	public function rename_column($old_label,$new_label)
	{
		if (!isset($this->data[$old_label])) {
			throw new Exception("Label does not match any column");
		}
		$this->data[$new_label] = $this->data[$old_label];
		unset($this->data[$old_label]);
		return $this;
	}

	public function get($key)
	{
		if (!isset($this->data[$key])) {
			throw new Exception("Error Column with identifier `{$key}` does not exist. Avaialable Column names are : ". implode(' , ', array_keys($this->data)));
		}
		return $this->data[$key];
	}

	public function remove(Array $identifiers, $return=false)
	{
		if (count($identifiers) > count($this->data)) {
			throw new Exception("Identifiers Exceeded number of columns");			
		}
		if ($return) {
			$tmp_data = $this->data;
		}
		foreach ($identifiers as $identifier) {
			if (!isset($this->data[$identifier])) {
				throw new Exception("Column with identifier `{$identifier}` does not exist. Avaialable Column names are : ". implode(' , ', array_keys($this->data)));
				break;				
			}
			if ($return) {
				unset($tmp_data[$identifier]);
			}else{
				unset($this->data[$identifier]);
			}
		}
		return isset($tmp_data) ? new DataFrame($tmp_data) : $this;
	}

	public function filter($key,$value,$condition='EQ')
	{
		if (!isset($this->data[$key])) {
			throw new Exception("Error Column with identifier `{$key}` does not exist. Avaialable Column names are : ".implode(' , ', array_keys($this->data)));
		}
		$tmp_data = $this->data();
		$column = $tmp_data[$key];
		asort($column);
		list($higher,$lower) = $this->binary_search($column,$value);
		switch ($condition) {
			case 'EQ':
				$leq_values = $lower;
				$keys = array_keys($leq_values,$value);
				break;
			case 'GEQ':
				$g_keys = array_keys($higher);
				$eq_keys = array_keys($lower,$value);
				$keys = array_merge($g_keys,$eq_keys);
				break;
			case 'LEQ':
				$leq_values = $lower;
				$keys = array_keys($leq_values);
				break;
			case 'GT':
				$keys = array_keys($higher);
				break;
			case 'LT':
				$eq_keys = array_keys($lower,$value);
				$keys = array_diff(array_keys($lower),$eq_keys);
				break;
			case 'NEQ':
				$eq_keys = array_keys($lower,$value);
				$all = array_merge(array_keys($lower),array_keys($higher));
				$keys = array_diff($all, $eq_keys);
				break;
			default:
				throw new Exception("Error Processing Filter");				
				break;
		}
		$ret_data = [];
		foreach ($keys as $key_index => $key) {
			foreach ($this->data() as $data_key => $value) {
				$ret_data[$data_key][] = $value[$key];
			}
		}
		return new DataFrame($ret_data);
	}

	private function binary_search($data, $search_value)
	{
		$higher = [];
		$lower = [];

		#getting the mid point of the arranged array 
		$half_point = floor((count($data))/2);

		#geting index of the midpoint 
		$index = count($data) - $half_point;

		#Separating array into two

		#values below the midpoint
		$portion_below = array_slice($data,0,$index,true);

		#values above the midpoint
		$portion_above = array_slice($data, -$half_point,null,true);
		
		$i =0;
		while (true) {
			#check if one item remains
			if (count($data) == 1) {

				#if the last item is greater than the split value
				if ($data[array_keys($data)[0]] > $search_value) {
					$higher = ($higher + $portion_above);
				}else{
					$lower = ($lower + $portion_below);
				}
				break;
			}

			#if the midpoint value is greater than the split value
			if ($data[array_keys($data)[$index]] > $search_value) {
				$higher = ($higher + $portion_above);
				$data = $portion_below;
			}else {
				$lower = ($lower + $portion_below);
				$data = $portion_above;
			}

			#updating the values
			$half_point = floor((count($data))/2);
			$index = count($data) - $half_point;

			$portion_below = array_slice($data,0,$index,true);
			$portion_above = array_slice($data, -$half_point,null,true);
			$i++;
		}
		return[ $higher, $lower	];
	}

	public function __toString()
	{

		$retval = $this->filename ?? 'DataFrame';
		$retval .= "\n";
		$retval .= str_repeat('=', strlen($this->filename ?? 'DataFrame'));
		$retval .= "\n";
		$retval .= "\n";
		foreach (array_keys($this->data()) as $key => $value) {
			$space = 20 - strlen(strval($value));
			$retval .= "{$value}".str_pad(" ", $space);
		}
		$retval .= "\n";
		$retval .= str_repeat('==================', count(array_keys($this->data())));
		$retval .= "\n";
		$label_one = array_keys($this->data())[0];
		foreach ($this->get($label_one) as $tkey => $tvalue) {
			foreach ($this->data() as $key => $value) {
				$space = 20 - strlen(strval($value[$tkey]));
				$retval .= "{$value[$tkey]}".str_pad(" ", $space);
			}
			$retval .= ("\n");
		}
		$retval .= str_repeat('==================', count(array_keys($this->data())));;
		$retval .= "\n\n\n\n";
		return $retval;
	}

	public function __debuginfo()
	{
		return [ 'file_name' => $this->filename ?? 'DataFrame' ];
	}
}