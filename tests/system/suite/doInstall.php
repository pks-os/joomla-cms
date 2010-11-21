<?php
/**
 * @version		$Id$
 * @package		Joomla.SystemTest
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * Does a standard Joomla! installation
 */

require_once 'SeleniumJoomlaTestCase.php';

class DoInstall extends SeleniumJoomlaTestCase
{
	function testDoInstall()
	{
		$this->setUp();
		$cfg = $this->cfg;
		$configFile = $cfg->folder.$cfg->path."configuration.php";

		if (file_exists($configFile)) {
			echo "Delete configuration file\n";
			unlink($configFile);
		}
		else {
			echo "No configuration file found\n";
		}

		echo("Starting Installation\n");
		echo "Page through screen 1\n";
		$this->open($cfg->path ."/installation/index.php");
		$this->click("link=Next");
		$this->waitForPageToLoad("30000");

		echo "Page through screen 2\n";
		$this->click("link=Next");
		$this->waitForPageToLoad("30000");

		echo "Page through screen 3\n";
		$this->click("link=Next");
		$this->waitForPageToLoad("30000");

		echo "Enter database information\n";
		$dbtype = (isset($cfg->db_type)) ? $cfg->db_type : 'MySQL';
		$this->select("jform_db_type", "label=".$dbtype);
		$this->type("jform_db_host", $cfg->db_host);
		$this->type("jform_db_user", $cfg->db_user);
		$this->type("jform_db_pass", $cfg->db_pass);
		$this->type("jform_db_name", $cfg->db_name);
		$this->click("jform_db_old0");
		$this->click("link=Next");
		$this->waitForPageToLoad("30000");

		echo "Enter site information\n";
		$this->click("link=Next");
		$this->waitForPageToLoad("30000");
		$this->type("jform_site_name", $cfg->site_name);
		$this->type("jform_admin_user", $cfg->username);
		$this->type("jform_admin_email", $cfg->admin_email);
		$this->type("jform_admin_password", $cfg->password);
		$this->type("jform_admin_password2", $cfg->password);

		// Default is install with sample data
		if ($cfg->sample_data !== false)
		{
			echo "Install sample data and wait for success message\n";
			$this->click("instDefault");

			// wait up to 30 seconds for success message on sample data
			for ($second = 0; ; $second++) {
				if ($second >= 30) {
					$this->fail('timeout');
				}

				try {
					if (stripos($this->getValue("instDefault"),'SUCCESS')) {
						break;
					}
				}
				catch (Exception $e) {
				}

				sleep(1);
			}
		}
		else {
			echo "Install without sample data\n";
		}

		echo "Finish installation\n";
		$this->click("link=Next");
		$this->waitForPageToLoad("30000");
		$this->assertTrue(true);

		echo "Login to back end\n";
		$this->gotoAdmin();
		$this->doAdminLogin();

		echo "Check for site menu\n";
		$this->assertEquals("Site", $this->getText("link=Site"));

		echo "Change error level to maximum\n";
		$this->jClick('Global Configuration');
		$this->click("server");
		$this->select("jform_error_reporting", "label=Maximum");

		echo "Set caching to $cfg->cache\n";
		$this->click("system");

		switch ($cfg->cache)
		{
			case 'on-basic':
				$this->select("jform_caching", "label=ON - Conservative caching");
				break;

			case 'on-full' :
				$this->select("jform_caching", "label=ON - Progressive caching");
				break;

			case 'off'	:
			default:
				$this->select("jform_caching", "label=OFF - Caching disabled");
				break;
		}

		$this->click("//li[@id='toolbar-save']/a/span");
		$this->waitForPageToLoad("30000");
		$this->doAdminLogout();
		$this->deleteAllVisibleCookies();
	}
}
