<?php
/**
 * @package   Genesis Updater
 * @author    Dazzle Software https://dazzlesoftware.org
 * @copyright Copyright (C) 2026 Dazzle Software, LLC
 * @license   GNU/GPLv3 and later
 */

namespace Joomla\Component\Genesisupdater\Administrator\Platform;

\defined('_JEXEC') or die;

/**
 * Contract implemented by every pluggable update-feed platform generator
 * (Joomla, WordPress, Mobile, or any third-party plugin registered via the
 * `onGenesisUpdaterRegisterPlatforms` event).
 */
interface PlatformInterface
{
    /**
     * A unique, filesystem/URL-safe slug for this platform, e.g. "joomla".
     *
     * @return  string
     */
    public function getSlug(): string;

    /**
     * Human readable label shown in the admin UI, e.g. "Joomla".
     *
     * @return  string
     */
    public function getLabel(): string;

    /**
     * File extension (without the dot) generated files should use, e.g. "xml" or "json".
     *
     * @return  string
     */
    public function getExtension(): string;

    /**
     * Whether this platform can generate a standalone changelog file.
     *
     * @return  boolean
     */
    public function supportsChangelog(): bool;

    /**
     * Whether this platform makes use of the "compatibility" fields
     * (requires / tested / requires_php) on a version row.
     *
     * @return  boolean
     */
    public function supportsCompatibilityFields(): bool;

    /**
     * Whether this platform makes use of the mobile-only fields
     * (build_number / url_ios / url_android / force_update) on a version row.
     *
     * @return  boolean
     */
    public function supportsMobileFields(): bool;

    /**
     * Generate the update-feed file contents for a product.
     *
     * @param   array  $product  The product record (associative array, versions already decoded).
     *
     * @return  string  The raw file contents to write to disk.
     */
    public function generate(array $product): string;

    /**
     * Generate the changelog file contents for a product, if supported.
     *
     * @param   array  $changelog  The changelog record (associative array, entries already decoded).
     * @param   array  $product    The linked product record.
     *
     * @return  string|null  The raw file contents, or null if this platform has nothing to write.
     */
    public function generateChangelog(array $changelog, array $product): ?string;
}
