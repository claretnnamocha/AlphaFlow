<?php
namespace Alpha\ML\Supervised\Classification;

/**
 * LogisticRegression Classifier
 * @author Claret Nnamocha <devclareo@gmail.com>
 */
class LogisticRegression
{

	/**
	* Constructor
	* @param Array $data_set
	* @param string $label
	* @param bool $sgd
	* @param int $epochs
	* @param double $alpha
	* @return LogisticRegression
	*/
	function __construct(Array $data_set,string $label='label',bool $sgd = true, int $epochs = 10,double $alpha=0.3)
	{
		$this->data_set = $data_set;
		if ($sgd) {
			if (!($epochs > 0))
				throw new Exception("Invalid value for epochs", 1);
			$this->sgd = true;
		}
		if (count(array_unique($data_set[$label])) != 2) {
			throw new Exception("Error Only two classes required");			
		}
		$this->label = $label;
		$this->epochs = $epochs;
		return $this->train($alpha);	
	}


	/**
	* Learning the relationhip between the data
	* @param double $alpha learning rate of algorithm
	* @return LogisticRegression 
	*/
	private function train($alpha)
	{
		$slope = [];
		$intercept = 0;
		$y_preidcted = [];
		$error = 0;
		$total_error = 0;

		$tmp_data = $this->data_set;
		unset($tmp_data[$this->label]);

		$first_label = array_keys($tmp_data)[0];

		$this->classes = (array_values(array_unique($this->data_set[$this->label])));

		for ($epoch=0; $epoch < $this->epochs; $epoch++) {
			foreach ($tmp_data[$first_label] as $index => $x) {
				$mx = 0;
				foreach ($tmp_data as $i => $_x) {
					$slope[$i] = $slope[$i] ?? 0;
					$mx += $_x[$index]*$slope[$i];
				}
				$y_preidcted[$index] = 1/(1+pow(2.71828, -($intercept + $mx)));
				$error = array_keys($this->classes,$this->data_set[$this->label][$index])[0] - $y_preidcted[$index];
				
				$intercept = $intercept + $alpha * $error * $y_preidcted[$index] * (1 - $y_preidcted[$index]);
				foreach ($tmp_data as $i => $_x) {
					$slope[$i] = $slope[$i] + $alpha * $error * $y_preidcted[$index] * (1 - $y_preidcted[$index]) * $_x[$index];
				}
			}
			if (!$this->sgd) { break; }
		}
		$this->slope = $slope;
		$this->intercept = $intercept;
		return $this;
	}


	/**
	* Speculating a result based on data given
	* @param Array $input_x
	* @return Int $value
	*/
	public function predict(Array $input_x)
	{
		$tmp_data = $this->data_set;
		unset($tmp_data[$this->label]);
		unset($input_x[$this->label]);
		$first_label = array_keys($tmp_data)[0];

		if (count($input_x) != count($tmp_data)) { throw new Exception("Incomplete Dataset"); }

		$mx = 0;
		foreach ($input_x as $i => $_x) {
			if (count($input_x[$i]) != 1) { throw new Exception("Error Invalid number of rows"); }
			$mx += $input_x[$i][0] * $this->slope[$i];
		}

		$value = 1/(1+pow(2.71828, -($this->intercept + $mx)));
		return $this->classes[round($value)];
	}

}

