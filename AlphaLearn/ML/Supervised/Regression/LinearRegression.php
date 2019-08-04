<?php
namespace AlphaLearn\ML\Supervised\Regression;

/**
 * Linear Regressor
 * @author Claret Nnamocha <devclareo@gmail.com>
 */
class LinearRegression
{

	/**
	* @var Array $data_set Input and resultant values to be evaluated
	*/
	private $data_set = [];

	/**
	* @var float $slope The coeffiecient of the input values
	*/
	private $slope = 0;

	/**
	* @var float $intercept The coeffient of the entire dataset
	*/
	private $intercept = 0;

	/**
	* @var int $epochs Number of iterations to undergo for the training process
	*/
	private $epochs = 0;

	/**
	* @var Boolean $sgd Whether or not the SGD approach is used
	*/
	private $sgd = false;


	function __construct(Array $data_set, bool $sgd = true, int $epochs = 10)
	{
		if ($sgd) {
			if (!($epochs > 0))
				throw new Exception("Invalid value for epochs", 1);	
			$this->sgd = true;
		}
		$this->epochs = $epochs;
		if ($this->check_structure($data_set))
			$this->train();
		else
			throw new Exception("Invalid Data structure", 1);
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
	* Speculating a result based on data given
	* @param Array $input_x 
	* @param Boolean $round 
	* @return Int $value
	*/	
	public function predict(Array $input_x,bool $round=false) : float
	{
		if (count($input_x) != count($this->data_set['x']))
			throw new Exception("Incomplete Dataset", 1);
		$mx = 0;
		foreach ($this->data_set['x'] as $i => $_x) {
			$mx += $input_x[$i]*$this->slope[$i];
		}
		$value = $mx + $this->intercept;
		return $round ? round($value) : $value;
	}

	/**
	* Learning the relationhip between the data
	*/
	private function train()
	{
		if ($this->sgd) {
			$this->sgd_train();
			return;
		}
		$mx = $this->get_mean($this->data_set['x']);
		$my = $this->get_mean($this->data_set['y']);

		$err_x = $this->get_error($mx,$this->data_set['x']);
		$err_y = $this->get_error($my,$this->data_set['y']);

		$num = $this->get_numerator($err_x,$err_y);
		$denom = $this->get_denominator($err_x);

		$slope = doubleval($num/$denom);
		$intercept = doubleval($my - ($slope * $mx));

		$this->slope = $slope;
		$this->intercept = $intercept;
	}

	/**
	* Learning the relationhip between the data using the SGD approach
	*/
	private function sgd_train()
	{
		$slope = [];
		$intercept = 0;
		$y_predicted = [];
		$error = 0;
		$total_error = 0;
		$mx = 0;
		for ($epoch=0; $epoch < $this->epochs; $epoch++) {
			foreach ($this->data_set['x'][0] as $index => $x) {
				$mx = 0;
				foreach ($this->data_set['x'] as $i => $_x) {
					$m = $slope[$i] ?? 0;
					$slope[$i] = $m;
					$mx += $_x[$index]*$m;
				}
				$y_predicted[$index] = $intercept + $mx;
				$error = $y_predicted[$index] - $this->data_set['y'][$index];
				$total_error += $error;
				$intercept = $intercept - 0.01 * $error;
				foreach ($this->data_set['x'] as $i => $_x) {
					$slope[$i] = $slope[$i] - 0.01 * $error * $this->data_set['x'][$i][$index];
				}
			}
		}
		$this->slope = $slope;
		$this->intercept = $intercept;
	}


	/**
	* Learning process
	*/
	private function get_numerator(Array $error_x,Array $error_y): float
	{
		$total = 0;
		if (count($error_x) != count($error_y))
			throw new Exception("Error Training : datasets do not match", 1);
		foreach ($error_x as $key => $x) {
			$total += $x * $error_y[$key];
		}
		return doubleval($total);
	}

	/**
	* Learning process
	*/
	private function get_denominator(Array $error_x): float
	{
		$total = 0;
		foreach ($error_x as $value) {
			$total += pow($value, 2);
		}
		return doubleval($total);
	}

	/**
	* Learning process
	*/
	private function get_error(float $mean, Array $data) : Array
	{
		$retval = [];
		foreach ($data as $value) {
			$retval[] = $value - $mean;
		}
		return $retval;
	}

	/**
	* Learning process
	*/
	private function get_mean(Array $data): float
	{
		$total = 0;
		foreach ($data as $value) {
			$total += $value;
		}
		return doubleval($total/count($data));
	}
}
