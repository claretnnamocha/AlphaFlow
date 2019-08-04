<?php
namespace Alpha\ML\Supervised\Classification; 

use Alpha\Utils\DataFrame\DataFrame;

/**
 * A Classification and Regression Tree 
 */
class DecesionTree
{
	/**
	* Constructor
	* @param Array $data_set
	* @param string $label
	* @param int $min_samples
	* @param int $max_iteration
	*/
	function __construct($data_set,$label='label',$min_samples=1,$max_iteration=3){
		$this->data_set =  $data_set;
		$this->label = $label;
		$this->train($min_samples,$max_iteration);
	}

	/**
	* Learning process
	*/
	private function train($min_samples,$max_iteration)
	{	
		$this->tree = $this->main($this->data_set, $this->label,0,$min_samples,$max_iteration);
	}

	/**
	* Learning process
	*/
	private function main($data,$label='label',$counter=0,$min_samples=0,$max_iteration=0)
	{
		$df = new DataFrame($data);

		if ($this->is_pure($df->get($label)) or (count($df->get($label)) < $min_samples) or $counter == $max_iteration) {
			return $this->classify($df->get($label));
		}

		$counter += 1;

		$p_splits_df = $df->clone()->remove([$label]);
		$p_splits = $this->get_all_potential_split_points($p_splits_df->data());
		list($best_split_column,$best_split_value) = $this->get_best_split($df->data(),$p_splits,$label);

		$question = "is {$best_split_column} <= {$best_split_value}";

		$no = $df->filter($best_split_column,$best_split_value,'GT');
		$yes = $df->filter($best_split_column,$best_split_value,'LEQ');

		if ($yes == $no) {
			$sub_tree = $yes;
		}else{
			$sub_tree = [
				$question => [				
					'yes' => $this->main($yes->data(), $label, $counter,$min_samples,$max_iteration),
					'no' => $this->main($no->data(), $label, $counter,$min_samples,$max_iteration)
				]
			];
		}
		return $sub_tree;
	}

	/**
	* Learning process
	*/
	private function get_all_potential_split_points($data)
	{
		$split_points = [];
		foreach ($data as $key => $value) {
			$split_points[$key] = $this->get_potential_split_points($value);
		}
		return $split_points;
	}

	/**
	* Learning process
	*/
	private function get_potential_split_points($data)
	{
		$unique_values = array_unique($data);

		sort($unique_values);

		$potential_split_points = [];

		foreach ($unique_values as $key => $value) {
			#if the current item is the first item  skip to the next
			if ($key == 0) {
				continue;
			}
			$previous_value = $unique_values[$key - 1];
			$potential_split_points[] = (($value -  $previous_value)/2) + $previous_value;
		}
		return $potential_split_points;
	}

	/**
	* Learning process
	*/
	private function is_pure($data)
	{
		sort($data);
		return $data[0] == $data[count($data) - 1];
	}

	/**
	* Learning process
	*/
	private function classify($data)
	{
		return max($data);
	}


	/**
	* Learning process
	*/
	private function calculate_entropy($data)
	{
		$total = count($data);
		$probabilities = [];
		$entropies = [];
		$sums = [];
		$entropy = 0;
		$unique_values = array_unique($data);
		foreach ($unique_values as $key => $value) {
			$sums[$key] = count(array_keys($data,$value));
			$probabilities[$key] = $sums[$key]/$total;
			$entropies[$key] = $probabilities[$key] * (-log($probabilities[$key],2));
		}
		$entropy = array_sum($entropies);
		return is_nan($entropy) ? 0 : $entropy;
	}

	/**
	* Learning process
	*/ 
	private function calculate_overall_entropy($above,$below)
	{
		$total = count($above) + count($below);
		$p_above = count($above)/$total;
		$p_below = count($below)/$total;
		return (($p_above * $this->calculate_entropy($above)) + ($p_below * $this->calculate_entropy($below)));
	}


	/**
	* Learning process
	*/
	private function get_best_split($data,$potential_split_points,$column_name='label')
	{
		$overall_entropy = 6000000000000000000;
		$data = new DataFrame($data);
		foreach ($potential_split_points as $label => $split_values) {
			foreach ($split_values as $split_value) {
				$above = $data->filter($label,$split_value,'GT')->get($column_name);
				$below = $data->filter($label,$split_value,'LEQ')->get($column_name);
				$current_overall_entropy = $this->calculate_overall_entropy($above,$below);

				if ($current_overall_entropy <= $overall_entropy) {
					$overall_entropy = $current_overall_entropy;
					$best_split_column = $label;
					$best_split_value = $split_value;
				}
			}
		}
		return[ $best_split_column, $best_split_value ];
	}

	/**
	* Learning process
	*/
	private function predict_values($data,$tree)
	{
		$question = array_keys($tree)[0];
		$question_parts = explode(' ', $question);
		$answer = ($data[$question_parts[1]][0] <= doubleval($question_parts[3])) ? $tree[$question]['yes'] : $tree[$question]['no'];

		if (!is_array($answer)) {
			return $answer;
		}else{
			return $this->predict_values($data, $answer);
		}
	}


	/**
	* Speculating a result based on data given
	* @param Array $data
	* @return Int|string $value
	*/	
	public function predict($data)
	{
		return $this->predict_values($data,$this->tree);
	}


	/**
	* Calculate percentage of accuracy with test data
	* @param Array $test_data
	* @param string $label
	* @return double $percentage
	*/
	public function accuracy($test_data,$label='label')
	{
		$first_label = array_keys($test_data)[0];
		$correct = 0;
		$total = count($test_data[$first_label]);
		$current_data_set = [];
		foreach (range(0, $total-1) as $index => $value) {
			foreach ($test_data as $label => $column) {
				$current_data_set[$label][] = $column[$index];
			}
			$accurate = ($this->predict($current_data_set) == $current_data_set[$label][0]);
			if($accurate){
				$correct++;
			}
			$current_data_set = [];
		}

		return ($correct/$total);
	}
}