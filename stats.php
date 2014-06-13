<?php
require_once('skills.php');
require_once('ehp.php');

class Stats {
	public $xp;
	public $ranks;
	public $time;

	private $_clamp_xp, $_lowest_xp;
	private $_rank_sum, $_nn_count, $_thm_count;
	private $_virtual_total, $_time_to_nn, $_time_to_thm, $_ehp, $_combat_level;

	function __construct($xp, $ranks, $time=0) {
		global $current_time, $TIME_TO_THM;
		$this->xp = $xp;
		$this->ranks = $ranks;
		$this->time = ($time ? $time : $current_time);
	}

	function __get($name) {
		global $TIME_TO_THM;
		switch($name) {
		case 'virtual_total':
			if(isset($this->_virtual_total)) return $this->_virtual_total;
			$this->_virtual_total = 0;
			for($i = 1; $i < 24; $i++) {
				$this->_virtual_total += level_from_xp($this->xp[$i]);
			}
			return $this->_virtual_total;
		case 'time_to_nn':
			if(isset($this->_time_to_nn)) return $this->_time_to_nn;
			$this->_time_to_nn = calc_time($this->xp, 13034431);
			break;
		case 'time_to_thm':
			if(isset($this->_time_to_thm)) return $this->_time_to_thm;
			return $this->_time_to_thm = calc_time($this->xp, 200000000);
			break;
		case 'ehp':
			if(isset($this->_ehp)) return $this->_ehp;
			return ($this->_ehp = $TIME_TO_THM - $this->time_to_thm);
			break;
		case 'combat_level':
			if(isset($this->_combat_level)) return $this->_combat_level;
			$this->combat_level = combat_level(
				level_from_xp($this->xp[1]), level_from_xp($this->xp[2]), level_from_xp($this->xp[3]),
				level_from_xp($this->xp[4]), level_from_xp($this->xp[5]), level_from_xp($this->xp[6]),
				level_from_xp($this->xp[7]));
			break;
		case 'clamp_xp':
		case 'rank_sum':
		case 'nn_count':
		case 'thm_count':
		case 'lowest_xp':
			$hidden_var = "_{$name}";
			if(isset($this->$hidden_var)) return $this->$hidden_var;
			for($i = 1; $i < 24; $i++) {
				if($xp[$i] == 200000000) {
					$this->_thm_count++;
				}
				if($xp[$i] >= 13034431) {
					$this->_nn_count++;
					$this->_clamp_xp += 13034431;
				} else {
					$this->_clamp_xp += $xp[$i];
				}

				if($xp[$i] < $this->_lowest_xp) {
					$this->_lowest_xp = $xp[$i];
				}

				$this->_rank_sum += $ranks[$i];
			}
			return $this->$hidden_var;
		}
	}

	function save($id) {
		global $SKILLS, $SKILL_COUNT;
		$sql = "INSERT INTO stats VALUES ($id";
		for($i = 0; $i < $SKILL_COUNT; $i++) {
			$sql .= ",{$this->xp[$i]},{$this->ranks[$i]}";
		}
		$sql .= ') ON DUPLICATE KEY UPDATE ';
		for($i = 0; $i < $SKILL_COUNT; $i++) {
			$sql .= "{$SKILLS[$i]}XP={$this->xp[$i]},{$SKILLS[$i]}Rank={$this->ranks[$i]}";
			if($i != $SKILL_COUNT - 1) {
				$sql .= ',';
			}
		}
		return mysql_query($sql);
	}

	static function from_hiscores($player) {
		$xp = array();
		$ranks = array();
		$file = fopen(HISCORES_LITE_URL . $player, 'r');

		if($file) {
			for($i = 0; $i < 24; $i++) {
				$line = explode(',', fgets($file));
				$ranks[$i] = (int)$line[0];
				$xp[$i] = (int)$line[2];
			}
			return new Stats($xp, $ranks);
		}
		return null;
	}

	static function from_database($player) {
		if(is_string($player)) {
			$result = mysql_query("SELECT LastCheckTime AS Time,stats.* FROM players JOIN stats ON players.ID=stats.ID WHERE Name='{$player}'" );
		} else {
			$result = mysql_query("SELECT LastCheckTime AS Time,stats.* FROM players JOIN stats ON players.ID=stats.ID WHERE players.ID={$player}");
		}
		if($result && ($row = mysql_fetch_array($result))) {
			return self::from_database_row($row);
		}
		return null;
	}

	static function from_database_row($row) {
		global $SKILLS;
		$xp = array();
		$ranks = array();
		for($i = 0; $i < 24; $i++) {
			$xp[$i] = (int)$row["{$SKILLS[$i]}XP"];
			$ranks[$i] = (int)$row["{$SKILLS[$i]}Rank"];
		}
		return new Stats($xp, $ranks, (isset($row['Time']) ? $row['Time'] : 0));
	}

	static function subtract($stats, $stats_earlier) {
		if(!($stats && $stats_earlier)) return array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
		$xp = array();
		for($i = 0; $i < 24; $i++) {
			if($stats->xp[$i] >= $stats_earlier->xp[$i]) {
				$xp[$i] = $stats->xp[$i] - $stats_earlier->xp[$i];
			} else {
				return null; //negative xp gain
			}
		}
		return $xp;
	}
}

?>