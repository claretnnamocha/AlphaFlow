<?php
namespace AlphaLearn\ML\Supervised\Classification;

/**
 * KNearestNieghbor Classifier 
 * @author Claret Nnamocha <devclareo@gmail.com>
 */
class KNN
{
	/**
	* Create a new instance
	* @param Array $data_set
	* @param Int $k
	*/
	function __construct(Array $data_set, int $k)
	{
		$this->k = $k;
		if (($k%2) == 1) {
			throw new Exception("The value of 'k' should be an odd number");			
		}
		if (!$this->check_structure($data_set)) {
			throw new Exception("Invalid Data structure");
		}
	}

	/**
	* Ensuring the interity of data is matained
	* @param Array $data_set The input and output values to be evaluated
	* @return Boolean $status The valiidity of data integrity
	*/
	private function check_structure(Array $data_set) : bool 
	{
		if (count($data_set) != 2)
			throw new Exception("Invalid Data Format", 1);
		$r = ((array_keys($data_set) == ['X','Y']) or (array_keys($data_set) == ['Y','X']) or (array_keys($data_set) == ['x','y']) or (array_keys($data_set) == ['y','x']));
		if (!$r)
			throw new Exception("Invalid labels used", 1);
		$d = [];
		foreach (array_keys($data_set) as $key => $value) {
			$d[strtolower($value)] = $data_set[$value];
		}
		foreach ($d['x'] as $x) {
			if (count($x) != count($d['y']))
				throw new Exception("Incomplete Dataset", 1);
		}
		if (count($d['x']) > 1) {
			$this->sgd = true;
		}
		$this->data_set = $d;
		return true;
	}


	/**
	* Calculate the distance between each point and every other point
	* @param Array $input_data
	* @return Array $distances
	*/
	private function get_euclidean_distances(Array $input_data) : Array
	{
		$squares = [];
		$sums = [];
		$distances = []; 
		if (count($input_data) != count($this->data_set)) 
			throw new Exception('Invalid data structure');
		foreach ($this->data_set['x'] as $index => $x) {
			foreach ($x as $i => $value) {
				$squares[$index][] = pow($input_data[$index] - $value, 2);
			}
		}
		foreach ($squares[0] as $index => $value) {
			foreach ($squares as $i => $sq) {
				$sums[$index] = $sums[$index] ?? 0;
				$sums[$index] += $squares[$i][$index];
			}
			$distances[$index] = pow($sums[$index], 0.5);
		}
		return $distances;
	}


	/**
	* Get value of k elements closest to the current centroid
	* @param Array $input_data
	* @return Array $neighbors 
	*/
	private function get_nearest_neighbors(Array $input_data) : Array
	{
		$neighbors = [];
		for ($i=0; $i < $this->k; $i++){
			$key = array_keys($input_data,min($input_data))[0];
			$neighbors[$key] = min($input_data);
			unset($input_data[$key]);
		}
		return $neighbors;
	}


	/**
	* Decide the value of centroid with max number of neighbors
	* @param $neighbors
	* @return Int $class
	*/
	private function classify(Array $neighbors): int
	{
		$freq = [];
		foreach ($neighbors as $i => $value) {
			$freq[$this->data_set['y'][$i]] = $freq[$this->data_set['y'][$i]] ?? 0;
			$freq[$this->data_set['y'][$i]]++;		 
		}
		return array_keys($freq,max($freq))[0];
	}

	/**
	* Set values of labels
	* @param Array $labels
	*/
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


	/**
	* Return a list of labels
	* @return Array $labels
	*/
	public function get_labels()
	{
		return $this->labels ?? None;
	}


	/**
	* Speculating a result based on data given
	* @param Array $input_data
	* @return Int|string  $class
	*/
	public function predict(Array $input_data)
	{
		$distances = $this->get_euclidean_distances($input_data);
		$neighbors = $this->get_nearest_neighbors($distances);
		$class = $this->classify($neighbors);
		return $this->labels[$class] ?? $class;
	}
}