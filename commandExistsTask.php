<?php
class commandExistsTask extends Task {

	protected $checkCommand = null;

	function setCheckCommand($checkCommand) {
		$this->checkCommand = $checkCommand;
	}

	protected function commandExists($command) {
		$returnValue = 0;
		$output = '';
		$result = exec("type $command &> /dev/null", $output, $returnValue);

		//on ubuntu zero is returned as a return value, even if command not found, so the extra strpos check is necessary
		if ($returnValue == 0 && strpos($result, " not found") === false) return true;
		else return false;
	}

	/** Checks if the given command exists on the current platform.
	Returns true if it exists, false if it does not exist. */
	function main() {
		$exists = $this->commandExists($this->checkCommand);

		if (!$exists) {
			echo("The command '$this->checkCommand' does not exist. Please install this command. Task aborted.\n\n");
			die();
		}
	}
}

?>