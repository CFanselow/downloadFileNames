<?php

/**
 * @file plugins/generic/downloadFileNames/DownloadFileNamesPlugin.inc.php
 *
 * Copyright (c) 2017-2020 Simon Fraser University
 * Copyright (c) 2017-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class DownloadFileNamesPlugin
 * @ingroup plugins_generic_downloadFileNames
 *
 * @brief Download File Names plugin class.
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class DownloadFileNamesPlugin extends GenericPlugin {

	/**
	 * @copydoc Plugin::getDisplayName()
	 */
	public function getDisplayName() {
		return __('plugins.generic.downloadFileNames.displayName');
	}

	/**
	 * @copydoc Plugin::getDescription()
	 */
	public function getDescription() {
		return __('plugins.generic.downloadFileNames.description');
	}

	/**
	 * @copydoc Plugin::register()
	 */
	public function register($category, $path, $mainContextId = null) {
		$success = parent::register($category, $path, $mainContextId);
		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return $success;
		if ($success && $this->getEnabled($mainContextId)) {
			//HookRegistry::register('File::formatFilename', [$this, 'formatFilenameHandler']);
			HookRegistry::register('File::download', [$this, 'downloadHandler']);
		}
		return $success;
	}
	
	function downloadHandler($hookName, $params){

		$pathFile =& $params[0];
		$originalFilename =& $params[1];
		
		// get file infos
		$contextId = Application::get()->getRequest()->getContext()->getId();
		
		$pathSubmissionFile	= Application::get()->getRequest()->getRequestPath();
		$pathAsArray = explode("/",$pathSubmissionFile);
		$sizeOfArray = sizeof($pathAsArray);
		
		$submissionId = $pathAsArray[$sizeOfArray-3];
		$galleyId = $pathAsArray[$sizeOfArray-2];
		$submissionFileId = $pathAsArray[$sizeOfArray-1];
		
		$extension = strtolower(pathinfo($pathFile, PATHINFO_EXTENSION));
		
		$locale = AppLocale::getLocale(); // ggf. testen als Alternative: $journal->getName($journal->getPrimaryLocale())
		$acronym = Application::get()->getRequest()->getContext()->getData('acronym',"en_US");		
	
		$submissionDao = DAORegistry::getDAO('SubmissionDAO');
		$submission = $submissionDao->getById($submissionId);
		
		$issueDao = DAORegistry::getDAO('IssueDAO');
		$issueId = $submission->getCurrentPublication()->getData('issueId');
		$issue = $issueDao->getById($issueId, $submission->getContextId());
		$volume = $issue->getVolume();
		$number = $issue->getNumber();	
		$pages = $submission->getCurrentPublication()->getData('pages'); // select setting_name, setting_value from publication_settings where setting_name="pages";
		$fileId = $submissionFileId;
		
		// switch through types
		$type = $this->getSetting($contextId, 'type');
		$filename = "";
		switch ($type) {
			case 1:
				$useAcronym = $this->getSetting($contextId, 'acronym');
				$useVolume = $this->getSetting($contextId, 'volume');
				$useNumber = $this->getSetting($contextId, 'number');
				$usePages = $this->getSetting($contextId, 'pages');
				$useFileId = $this->getSetting($contextId, 'fileId');				
				if ($useAcronym && isset($acronym)) {$filename .= "_".$acronym ;}
				if ($useVolume && isset($volume)) {$filename .= "_".$volume ;}
				if ($useNumber && isset($number)) {$filename .= "_".$number ;}
				if ($usePages && isset($pages)) {$filename .= "_".$pages ;}
				if ($useFileId && isset($fileId)) {$filename .= "_".$fileId ;}				
				if (substr($filename, 0, 1)=="_") {$filename = substr($filename,-(strlen($filename)-1));}
				break;
			case 2:
				if ($originalFilename!=="") {$filename = $originalFilename;}
				$filename = str_replace(" ","_",$filename);
				break;
			case 3:
				$filename = strip_tags($submission->getLocalizedTitle());
				$filename = str_replace(" ","_",$filename);
				if (strlen($filename)>100) {$filename = substr($filename,0,100)."...";}
				break;
		}
		if ($filename=="") {$filename = "document";}
		$filename .= ".".$extension;
		$originalFilename = $filename;

		return false;		
	
	}

	/**
	 * @see Plugin::getActions()
	 */
	public function getActions($request, $actionArgs) {

		$actions = parent::getActions($request, $actionArgs);

		if (!$this->getEnabled()) {
			return $actions;
		}

		$router = $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$linkAction = new LinkAction(
			'settings',
			new AjaxModal(
				$router->url(
					$request,
					null,
					null,
					'manage',
					null,
					array(
						'verb' => 'settings',
						'plugin' => $this->getName(),
						'category' => 'generic'
					)
				),
				$this->getDisplayName()
			),
			__('manager.plugins.settings'),
			null
		);

		array_unshift($actions, $linkAction);

		return $actions;
	}

	/**
	 * @see Plugin::manage()
	 */
	public function manage($args, $request) {
		switch ($request->getUserVar('verb')) {
			case 'settings':
				$this->import('DownloadFileNamesSettingsForm');
				$form = new DownloadFileNamesSettingsForm($this);

				if ($request->getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						return new JSONMessage(true);
					}
				}

				$form->initData();
				return new JSONMessage(true, $form->fetch($request));
		}
		return parent::manage($args, $request);
	}

}
