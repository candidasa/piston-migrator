# SVN to Git/Piston Migration

## Why migrate?
The main reason for migrating a project from SVN externals to Git/Piston is to make it easier to apply future updates. The SilverStripe codebase is now on github, so any future updates will have to be applied using "piston update".

Any project that is currently on SVN branches/2.4 or is being upgraded to SilverStripe 2.4+ should be migrated to Git/Piston.

## Project branches
If a project is on its own project branch, then migrating to Piston is difficult. This task involves switching the project to piston and merging any project-specific commits back into the repository (or integrating them into core, if they are relevant fixes).

Ideally, we combined this work with upgrading of the project to the latest version of SilverStripe. So, best to propose an upgrade to the client.

The way to handle switching from a project branch is to first change the svn:externals to a branch or tag (who's equivalent can be found on github), then run the script, then re-apply all the commits in the project branch, as necessary.

## Installation
Install this script by running the following commands:
  sudo pear install phing
  git clone git://github.com/candidasa/piston-migrator.git piston-migrator

Then run the script by running:
  phing -f piston-migrator/build.xml

## Script
Here is what the script does:

- Reads in the existing svn:externals
- Scans externals to see which have equivalent git repositories with the same branch/tag used in the external
- Generates all the commands to run to swap the externals to git/piston
- Displays a list of the commands it will run, asking for confirmation
- Runs the commands to do the following:

	* remove externals from svn:externals
	* delete the externals' folders
	* commit the removed svn:externals
	* run the piston import commands to import the new code
	* commit the new git/piston imported externals