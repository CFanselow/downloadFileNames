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

		$path =& $params[0];
		$filename =& $params[1];
		$contextId = Application::get()->getRequest()->getContext()->getId();
		$primaryLocale = Application::get()->getRequest()->getJournal()->getPrimaryLocale();
		$locale = AppLocale::getLocale(); 
		$acronym = Application::get()->getRequest()->getContext()->getData('acronym',$locale);
		if ($acronym==null) {
			$acronym = Application::get()->getRequest()->getContext()->getData('acronym',$primaryLocale);
		}

		// get file info
		$pathArray = explode("/",$path);
		$submissionId = $pathArray[3];
		$submissionDao = DAORegistry::getDAO('SubmissionDAO');
		$submission = $submissionDao->getById($submissionId);

		$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
		$requestPath = Application::get()->getRequest()->getRequestPath();
		$requestPathArray = explode("/",$requestPath);
		$fileId = null;
		if ($extension=="pdf") {$fileId = $requestPathArray[sizeof($requestPathArray)-1];}

		$issueDao = DAORegistry::getDAO('IssueDAO');
		$issueId = $submission->getCurrentPublication()->getData('issueId');
		$issue = $issueDao->getById($issueId, $submission->getContextId());
		$volume = $issue->getVolume();
		$number = $issue->getNumber();
		$pages = $submission->getCurrentPublication()->getData('pages'); // select setting_name, setting_value from publication_settings where setting_name="pages";

		// switch through types
		$type = $this->getSetting($contextId, 'type');
		$newFilename = "";
		switch ($type) {
			case 1:
				$useAcronym = $this->getSetting($contextId, 'acronym');
				$useVolume = $this->getSetting($contextId, 'volume');
				$useNumber = $this->getSetting($contextId, 'number');
				$usePages = $this->getSetting($contextId, 'pages');
				$useFileId = $this->getSetting($contextId, 'fileId');				
				if ($useAcronym && isset($acronym)) {$newFilename .= "_".$acronym ;}
				if ($useVolume && isset($volume)) {$newFilename .= "_".$volume ;}
				if ($useNumber && isset($number)) {$newFilename .= "_".$number ;}
				if ($usePages && isset($pages)) {$newFilename .= "_".$pages ;}
				if ($useFileId && isset($fileId)) {$newFilename .= "_".$fileId ;}				
				if (substr($newFilename, 0, 1)=="_") {$newFilename = substr($newFilename,-(strlen($newFilename)-1));}
				break;
			case 2:
				if ($filename!=="") {
					$newFilename = $filename;
					$newFilename = str_replace(" ","-",$newFilename);
				}
				break;
			case 3:
				$authors = $submission->getAuthors();
				$primaryAuthor = $authors[0]->getFamilyName($locale);
				if (empty($primaryAuthor)) {
					$primaryAuthor = $authors[0]->getFamilyName($primaryLocale);
				}	
				if (!empty($primaryAuthor)) {$newFilename = $primaryAuthor."-";}
				$newFilename .= strip_tags($submission->getLocalizedTitle());
				$newFilename = str_replace(" ","_",$newFilename);
				if (strlen($newFilename)>100) {$newFilename = substr($newFilename,0,100)."...";}
				break;
		}
		if ($newFilename=="") {$newFilename = "document";}
		$newFilename .= ".".$extension;
		$filename = $newFilename;

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
