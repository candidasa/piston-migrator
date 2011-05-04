<?php
class checkExternalsMappingsExistTask extends Task {

	protected $externals = null;

	function setExternals($externals) {
		$this->externals = $externals;
	}

	function setMappings($mappings) {
		$this->mappings = $mappings;
	}

	protected function parseMappings($mappingsString) {
		$mappings = array();

		$maps = explode("\n",$mappingsString);
		foreach($maps as $map) {
			$nameToURL = explode(' ',$map);
			$mappings[$nameToURL[0]] = $nameToURL[1];
		}

		return $mappings;
	}

	protected function parseExternals($externalsString) {
		$mappings = array();

		$externals = explode("\n",$externalsString);
		foreach($externals as $ex) {
			$exMappings = explode('`',$ex);
			$mappings[$exMappings[0]] = array(  //module name
				'folder' => $exMappings[1],
				'revision' => $exMappings[2],
				'branch' => $exMappings[3]
			);
		}

		return $mappings;
	}

	protected function serializeExternals($externalsArray) {
		$output = array();
		foreach($externalsArray as $mname => $frb) {
			$output[] = "$mname`$frb[folder]`$frb[revision]`$frb[branch]";
		}
		return implode("\n",$output);
	}

	protected function lsRemote($gitURL, $branch) {
		$output = null;
		exec("git ls-remote $gitURL",$output);
		$output = implode("\n",$output);

		$matches = array();
		$c = preg_match_all('/.*?refs\/(heads|tags)\/(?P<refs>.*)/',$output,$matches,PREG_PATTERN_ORDER);

		if ($c > 0) {
			foreach($matches['refs'] as $ref) {
				if (strcasecmp($ref, $branch) == 0) return true;    //branch found in git
			}
		}

		return false;
	}



	function main() {
		$mappingsArray = $this->parseMappings($this->mappings);

		$externalsArray = $this->parseExternals($this->externals);

		//remove any externals that don't have mappings to git repos
		foreach($externalsArray as $mname => $frb) {
			if (!isset($mappingsArray[$mname])) {
				echo "Mapping for '$mname' not found. Skipping.\n";
				unset($externalsArray[$mname]);
			} else {
				//echo "Using mapping of '$mname' to '".$mappingsArray[$mname]."'\n";
			}
		}

		//remove any externals that don't have a branch or rev in the git repo
		$mnameToBranchName = array();
		foreach($externalsArray as $mname => $frb) {
			if (!empty($frb['revision'])) {
				echo "module '$mname' linked to specific revision '$frb[revision]'. Can't switch to git/piston\n";
				unset($externalsArray[$mname]);
				continue;
			}

			$gitURL = $mappingsArray[$mname];
			if (!$frb['branch'] || $frb['branch'] == "trunk") { //trunk is good == master
				$mnameToBranchName[$mname] = "HEAD";
				continue;
			}

			$branchRef = explode('/',$frb['branch']);
			$svnRef = $branchRef[1];
			$found = $this->lsRemote($gitURL,$svnRef);
			if (!$found) {
				echo "module '$mname' found, but branch/tag '$svnRef' cannot be found in git refs. Can't switch to git/piston\n";
				unset($externalsArray[$mname]);
				continue;
			} else {
				$mnameToBranchName[$mname] = $svnRef;
			}
		}

		if (count($externalsArray) == 0) {
			echo "No modules to process\n";
			return;
		}

		//generate the piston commands to run to import new modules
		$pistonCommands = array();
		foreach($externalsArray as $mname => $frb) {
			$pistonCommands[] = "piston import --commit ".$mnameToBranchName[$mname]." ".$mappingsArray[$mname];
		}

		//make a list of all the obsolete svn folders to delete
		$foldersToRemove = array();
		foreach($externalsArray as $mname => $frb) {
			$foldersToRemove[] = $frb['folder'];
		}

		//generate the svn commands to run to remove old modules
		$modulesPregList = implode('|',array_keys($externalsArray));
		$svnPregReplaceCommand = '/^\s*('.$modulesPregList.')[\/]?\s.*/m';


		//what is left in the array is valid externals to convert to git/piston
		//$serial = $this->serializeExternals($externalsArray);

		$this->project->setProperty('foldersToRemove',implode(",",$foldersToRemove));
		$this->project->setProperty('pistonCommands',implode("\n",$pistonCommands));
		$this->project->setProperty('svnPregReplaceCommand',$svnPregReplaceCommand);
	}
}

?>