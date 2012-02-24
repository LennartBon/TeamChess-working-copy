<?php

define ('SUITE_MIN_LENGTH', 4);
	
/**
 * Class to hold a running suite in memory while counting
 */
class suite
{		
	var $kind           = '';           // wins / draws / no losses
	var $player_id      = 0;            // 
	var $player_name    = '';           //
	var $first_match    = 0;            // number (id) of 1st match in suite
	var $first_date     = '1972-01-01'; // date of same
	var $last_match     = 0;            // number (id) of last match
	var $last_date      = '1972-01-01'; // date of above
	var $suite_length   = 0;            // length of suite
	var $suite_points   = 0;            // accumulated points
	var $base           = '';           // (temporary) db to save a suite into

	/**
	 * Initializer
	 * @param -- see below
	 * @return an initialized object
	 */
	function suite ($t, $id, $s, $base) {
		$this->kind        = tch_striptags ($t);
		$this->player_id   = (int) $id;
		$this->player_name = tch_striptags ($s);
		$this->base        = $base;
	}
	
	/**
	 * Appends one more game/match to this suite
	 * @param -- m => match number; d => date of match; p => result
	 * @return nothing but object is updated
	 */
	function append_suite ($m, $d, $p) {
		if (0 == $this->suite_length) {				// New suite
			$this->first_match = (int) $m;
			$this->first_date = tch_striptags ($d);
		} else {
			$this->last_match = (int) $m;
			$this->last_date = tch_striptags ($d);
		}
		$this->suite_length++;
		$this->suite_points += (int) $p;
	}

	/**
	 * Closes this suite and saves data in a temporary table
	 * @param -- open => (bool) whether this suite is still open
	 * @return nothing but object is reset to virgin state
	 */
	function close_suite ($open) {
		if (SUITE_MIN_LENGTH <= $this->suite_length) {
			$open = (bool) $open;
			
			// Since temp_suite is a temporary table, we can relax error checking.
			$q  = "INSERT INTO temp_suites VALUES ";
			$q .= "('{$this->kind}', '{$this->player_id}', '{$this->player_name}',";
			$q .= " '{$this->first_match}', '{$this->first_date}',";
			$q .= " '{$this->last_match}', '{$this->last_date}',";
			$q .= " '{$this->suite_length}', '{$this->suite_points}', '{$open}')";

			$result1 = $this->base->query ($q);
		}
		
		// Clean up
		$this->suite_length = 0;
		$this->suite_points = 0;
	}
}

?>
