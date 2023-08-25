<?php
/**
 * @defgroup plugins_generic_downloadfilenames
 */
/**
 * @file plugins/generic/downloadfilenames/index.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_generic_downloadfilenames
 * @brief Wrapper for Download File Names plugin.
 *
 */
require_once('DownloadFileNamesPlugin.inc.php');
return new DownloadFileNamesPlugin();
