<?php
class svnCommitExternalsTask extends Task {

	protected $base = null;
	protected $list = null;

	function setBase($base) {
		$this->base = $base;
	}

	function setList($list) {
		$this->list = $list;
	}

	function main() {
		if (!is_dir($this->base)) {
			throw new BuildException("Invalid base directory: $this->base");
		}

		$cwd = realpath(getcwd());
		chdir($this->base);

		$command = "svn commit -m \"API-CHANGE: removing externals: $this->list\"";
		
		echo("Committing externals change: $command\n");
		exec("svn cleanup");
		$output = exec($command);
		echo $output."\n";

		chdir($cwd);
	}
}

?>