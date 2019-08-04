<?php
namespace Alpha\ML\Supervised\Classification;

/**
 * Linear Discriminant Analysis Classifier
 * @author Claret Nnamocha <devclareo@gmail.com>
 */
class LDA
{
	/**
	* Constructor
	* @param Array $data_set
	*/
	function __construct(Array $data_set)
	{
		$this->check_structure($data_set);
		$this->run();
	}

	/**
	* Learning process
	*/
	private function run()
	{		
		$this->groups = $this->separate_classes($this->data_set);
		$this->train($this->groups);
	}

	/**
	* Learning process
	*/
	private function check_structure(Array $data_set) : bool 
	{
		if (count($data_set) != 2)
			throw new Exception("Invalid Data Format");
		$r = ((array_keys($data_set) == ['X','Y']) or (array_keys($data_set) == ['Y','X']) or (array_keys($data_set) == ['x','y']) or (array_keys($data_set) == ['y','x']));
		if (!$r)
			throw new Exception("Invalid labels used");
		$d = [];
		foreach (array_keys($data_set) as $key => $value) {
			$d[strtolower($value)] = $data_set[$value];
		}
		if (count($d['x']) != count($d['y']))
			throw new Exception("Incomplete Dataset");
		$this->data_set = $d;
		return true;
	}

	/**
	* Learning process
	*/
	private function separate_classes(Array $data_set)
	{
		$groups = [];
		$classes = array_unique($data_set['y']);
		foreach ($classes as $class_key => $class) {
			foreach(array_keys($data_set['y'],$class) as $d_key => $d_value){
				$groups[$class][] = $data_set['x'][$d_value];
			}
		}
		$this->data_set = $data_set;
		return $groups;
	}

	/**
	* Learning process
	*/
	private function train(Array $grouped_data)
	{
		$averages = [];
		$probabilities = [];
		$square_differences = [];
		$variance = [];

		foreach ($grouped_data as $key => $value) {
			#Averages
			$averages[$key] = array_sum($value) / count($value);


			#Probabilities
			$probabilities[$key] = count($value)/count($this->data_set['x']);
		}

		#Square differences
		foreach ($grouped_data as $g_key => $in) {
			foreach ($in as $x_key => $x) {
				$square_differences[$g_key][] = pow($x - $averages[$g_key], 2);
			}
			$square_differences[$g_key] = array_sum($square_differences[$g_key]);
		}

		foreach ($square_differences as $key => $value) {
			$variance[$key] = (1/(count($grouped_data[$key]) - count($square_differences))) * array_sum($square_differences);
		}
		$this->averages = $averages;
		$this->classes = array_keys($averages);
		$this->probabilities = $probabilities;
		$this->square_differences = $square_differences;
		$this->variance = $variance;
	}

	/**
	* Speculating a result based on data given
	* @param double $value
	* @return Int|string $prediction
	*/
	public function predict(Float $value)
	{
		$predictions = [];
		foreach ($this->averages as $avg_key => $avg) {
			$predictions[$avg_key] = $value * ($avg/$this->variance[$avg_key]) - (pow($avg, 2)/(2*$this->variance[$avg_key])) + log(0.5);
		}
		return $this->labels[array_keys($predictions,max($predictions))[0]] ?? array_keys($predictions,max($predictions))[0];
	}

	public function set_labels(String ...$labels)
	{
		$classes = array_unique($this->data_set['y']);
		if (count($classes) != count($labels))
			throw new Exception("Number of Labels is not Equal to Number of Classess");
		$this->labels = [];
		sort($classes);
		foreach ($classes as $key => $value) {
			$this->labels[$value] = $labels[$key];
		}
	}

	public function get_labels()
	{
		return $this->labels ?? None;
	}
}
