<?php
class svnRemoveExternalsTask extends Task {

	protected $externals = null;
	protected $regex = null;
	protected $base = null;

	function setExternals($externals) {
		$this->externals = $externals;
	}

	function setRegex($regex) {
		$this->regex = $regex;
	}

	function setBase($base) {
		$this->base = $base;
	}

	function main() {
		if (!$this->regex) throw new BuildException("No regex to replace svn externals specified\n");
		if (!$this->externals) throw new BuildException("No svn externals specified\n");
		if (!is_dir($this->base)) {
			throw new BuildException("Invalid base directory: $this->base");
		}

		//remove the externals we are going to replace
		$newExternals = preg_replace($this->regex,'',$this->externals);

		$temp = tempnam(sys_get_temp_dir(),'ext');
		file_put_contents($temp, $newExternals);    //write the new externals to a file

		$command = "svn propset svn:externals $this->base -F $temp";
		echo("Setting new SVN externals with this command: $command\n");
		$output = exec($command);
		echo $output."\n";

		unlink($temp);  //delete temp file
	}
}

?>