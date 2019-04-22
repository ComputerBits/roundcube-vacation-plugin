<?php
include('VacationConfig.class.php');

class CustomVacationConfig extends VacationConfig {
	public function __construct() {
		$this->allowedOptions['twentyivac'] = array('token' => 'required');
		parent::__construct('plugins/vacation/cbits_config.ini');
	}
}
