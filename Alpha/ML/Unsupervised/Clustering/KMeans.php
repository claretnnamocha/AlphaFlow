<?php 
namespace Alpha\ML\Unsupervised\Clustering;

/**
 * KMeans Clustering Classifier 
 * @author Claret Nnamocha <devclareo@gmail.com>
 */

class KMeans
{
	
	function __construct(Array $data_set, int $k, int $max_iteration = 300)
	{
		if(!$this->check_structure($data_set))
			throw new Exception("Invalid Data structure");
		$this->k = $k;
		$c = [];
		$rand = array_rand($this->data_set[0],$this->k);
		foreach ($rand as $k => $v) {
			foreach ($this->data_set as $kk => $vv) {
				$c[$k][$kk] = $vv[$v];
			}
		}
		$this->centroids = $c;
		$this->max_iteration = $max_iteration;
	}

	public function run()
	{
		$this->trained = true;
		for ($iteration=0; $iteration < $this->max_iteration; $iteration++) { 
			$c = $this->predict($this->centroids);
			if ($c == $this->centroids) {
				break;
			}
			$this->centroids = $c;
		}
	}

	public function get_classifications()
	{
		if ((!isset($this->trained)) or (!$this->trained)) {
			throw new Exception("Error data is not computed yet");	
		}
		return [
			'x' => $this->data_set,
			'y' => $this->classifications
		];	
	}

	private function predict(Array $centroids){
		$distances = $this->get_euclidean_distances($centroids);
		$class_1 = $this->classify_1($distances);
		$class_2 = $this->classify_2($class_1);
		return $this->get_mean_centroids($class_2);
	}

	private function check_structure(Array $data_set) : bool 
	{
		if (gettype($data_set[0]) != 'array')
			throw new Exception("Invalid Data Format");
		foreach ($data_set as $key => $value) {
			if (count($value) != count($data_set[0]))
				throw new Exception("Incomplete Dataset");
		}
		$this->data_set = $data_set;
		return true;
	}

	private function get_euclidean_distances(Array $centroids)
	{
		$vals = [];
		$squares = [];
		$sums = [];
		$distances = [];
		$class = []; 
		if (count($centroids) != $this->k) 
			throw new Exception('Invalid number of centroids');

		for ($k=0; $k < $this->k; $k++) {
			foreach ($this->data_set as $index => $x) {
				foreach ($x as $i => $value) {
					$squares[$k][$index][] = pow($centroids[$index][$k] - $value, 2);
				}
			}
			foreach ($squares[$k][0] as $index => $value) {
				foreach ($squares[$k] as $i => $sq) {
					$sums[$k][$index] = $sums[$k][$index] ?? 0;
					$sums[$k][$index] += $squares[$k][$i][$index];
				}
				$distances[$k][$index] = pow($sums[$k][$index], 0.5);
			}
		}
		return $distances;
	}

	private function classify_1(Array $distances){
		foreach ($distances[0] as $key => $value) {
			foreach ($distances as $i => $d) {
				$vals[$key][$i] = $distances[$i][$key];
			}
		}
		foreach ($vals as $key => $value) {
			$class[$key] = array_keys($value,min($value))[0];
		}
		$this->classifications = $class;
		return $class;
	}

	private function classify_2(Array $class){
		$groups = [];
		for ($k=0; $k < $this->k; $k++) {
			$groups[$k] = (array_keys($class,$k));
		}

		$pairs = [];
		foreach ($groups as $kk => $vv) {
			foreach ($vv as $key => $value) {
				foreach ($this->data_set as $k => $v) {
					$pairs[$kk][$key][$k] = $v[$value];
				}
			}
		}
		print_r($groups);
		return $pairs;
	}

	private function get_mean_centroids(Array $pairs)
	{
		$sum = [];
		$tot = [];
		foreach ($pairs as $pair_key => $pair) {
			foreach ($pair as $key => $value) {
				foreach ($this->data_set as $k => $v) {
					$sum[$pair_key][$k] = $sum[$pair_key][$k] ?? 0;
					$tot[$pair_key][$k] = $tot[$pair_key][$k] ?? 0;
					$sum[$pair_key][$k] += $pairs[$pair_key][$key][$k];
					$tot[$pair_key][$k] += 1;
				}
			}
		}
		// print_r($sum);
		// print_r($tot);
		$avg = []; 
		foreach ($sum as $k => $v) {
			foreach ($v as $kk => $vv) {
				$avg[$k][$kk] = ($sum[$k][$kk] / $tot[$k][$kk]);
			}
		}
		// print_r($avg);
		return $avg;
	}
}