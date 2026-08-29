<?php
/**
 * @package   Genesis Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
\defined('_JEXEC') or die;

/**
 * Installation script for com_genesisupdater.
 *
 * Uses the legacy install-script convention (unnamespaced class named
 * "{ComponentName}InstallerScript", referenced via <scriptfile> in the manifest)
 * which is the pattern used by every bundled Joomla extension in this codebase
 * (e.g. administrator/components/com_admin/script.php).
 */
class Com_GenesisupdaterInstallerScript
{
	private function updateProductSchema(): void
	{
		$db = \Joomla\CMS\Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
		$table = $db->replacePrefix('#__genesisupdater_products');
		$columns = array_change_key_case($db->getTableColumns($table, false), CASE_LOWER);
		$required = [
			'platform' => "VARCHAR(32) NOT NULL DEFAULT 'joomla'",
			'catid' => 'INT UNSIGNED NOT NULL DEFAULT 0',
			'download_category' => "VARCHAR(255) NOT NULL DEFAULT ''",
			'preview_image' => "VARCHAR(1024) NOT NULL DEFAULT ''",
			'download_size' => 'BIGINT UNSIGNED NOT NULL DEFAULT 0',
			'download_count' => 'BIGINT UNSIGNED NOT NULL DEFAULT 0',
		];

		foreach ($required as $name => $definition) {
			if (!isset($columns[$name])) {
				$query = 'ALTER TABLE ' . $db->quoteName($table) . ' ADD ' . $db->quoteName($name) . ' ' . $definition;
				$db->setQuery($query)->execute();
			}
		}
	}

	private function updateBundleSchema(): void
	{
		$db = \Joomla\CMS\Factory::getContainer()->get(\Joomla\Database\DatabaseInterface::class);
		$query = "CREATE TABLE IF NOT EXISTS " . $db->quoteName('#__genesisupdater_bundles') . " (
			id INT UNSIGNED NOT NULL AUTO_INCREMENT, title VARCHAR(255) NOT NULL DEFAULT '', description VARCHAR(1024) NOT NULL DEFAULT '',
			platform VARCHAR(32) NOT NULL DEFAULT 'joomla', output_slug VARCHAR(255) NOT NULL DEFAULT '', filename VARCHAR(255) NOT NULL DEFAULT 'list.xml', product_ids LONGTEXT NOT NULL,
			state TINYINT NOT NULL DEFAULT 0, ordering INT NOT NULL DEFAULT 0, created DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00',
			created_by INT UNSIGNED NOT NULL DEFAULT 0, modified DATETIME NOT NULL DEFAULT '1000-01-01 00:00:00', modified_by INT UNSIGNED NOT NULL DEFAULT 0,
			checked_out INT UNSIGNED NULL, checked_out_time DATETIME NULL, PRIMARY KEY (id), KEY idx_state (state)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
		$db->setQuery($query)->execute();
		$table = $db->replacePrefix('#__genesisupdater_bundles');
		$columns = array_change_key_case($db->getTableColumns($table, false), CASE_LOWER);
		if (!isset($columns['platform'])) { $db->setQuery('ALTER TABLE ' . $db->quoteName($table) . ' ADD ' . $db->quoteName('platform') . " VARCHAR(32) NOT NULL DEFAULT 'joomla' AFTER " . $db->quoteName('description'))->execute(); }
	}

    /**
     * Called after any type of action (install, update, discover_install).
     *
     * @param   string  $type       install|update|discover_install
     * @param   object  $parent     The parent object (installer adapter)
     *
     * @return  boolean
     */
    public function postflight($type, $parent)
    {
		$this->updateProductSchema();
		$this->updateBundleSchema();

        return true;
    }

    /**
     * Called before any type of action.
     *
     * @param   string  $type
     * @param   object  $parent
     *
     * @return  boolean
     */
    public function preflight($type, $parent)
    {
        return true;
    }

    /**
     * Called on uninstall.
     *
     * @param   object  $parent
     *
     * @return  void
     */
    public function uninstall($parent)
    {
    }
}
