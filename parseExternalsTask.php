<?php
class parseExternalsTask extends Task {

	protected $externals = null;

	function setExternals($externals) {
		$this->externals = $externals;
	}

	function main() {
		$parsed = array();  //array of parsed externals lines

		$lines = explode("\n",$this->externals);
		foreach($lines as $line) {
			if (preg_match('/^\s*#/',$line)) continue; //skip comments
			if (preg_match('/^\s*$/',$line)) continue; //skip blank lines

			preg_match('/\s*(?P<folder>\w+)[\s\/]*(-r\s*(?P<revision>\d+))?.*?modules\/(?P<module>.+?)\/(?P<branch>.+)\s*/',$line,$matches);

			//backtick separated list of externals
			$matches['module'] = trim($matches['module'],' /');
			$matches['folder'] = trim($matches['folder'],' /');
			$parsed[] = "$matches[module]`$matches[folder]`$matches[revision]`$matches[branch]";
		}

		$parsedString = implode("\n",$parsed);

		$this->project->setProperty('externals2',$parsedString);
	}
}

?>