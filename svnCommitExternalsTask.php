<?php
class svnCommitExternalsTask extends Task {

	protected $base = null;
	protected $list = null;
	protected $add = false; //remove by default

	function setBase($base) {
		$this->base = $base;
	}

	function setList($list) {
		$this->list = $list;
	}

	function setAdd($add) {
		if (!$add) $this->add = false;
		$this->add = $add;
	}

	function main() {
		if (!is_dir($this->base)) {
			throw new BuildException("Invalid base directory: $this->base");
		}

		$cwd = realpath(getcwd());

		chdir($this->base);

		if ($this->add == false) {
			$command = "svn commit -m \"API-CHANGE: removing externals: $this->list\"";
			echo("Committing externals change: $command\n");

			exec("svn cleanup");

			$exList = explode(',',$this->list);
			foreach($exList as $l) {
				exec("svn rm $l");
			}
		} else {
			$command = "svn commit -m \"API-CHANGE: adding git/piston externals: $this->list\"";
			echo("Committing externals change: $command\n");
		}

		$output = exec($command);
		echo $output."\n";

		chdir($cwd);
	}
}

?>