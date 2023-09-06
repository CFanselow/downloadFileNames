<?php
/**
 * @file DownloadFileNamesSettingsForm.inc.inc.php
 *
 * Copyright (c) 2017-2020 Simon Fraser University
 * Copyright (c) 2017-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class DownloadFileNamesSettingsForm
 * @ingroup plugins_generic_downloadFileNames
 *
 * @brief Form for site admins to modify Download File Name settings.
 */


import('lib.pkp.classes.form.Form');

class DownloadFileNamesSettingsForm extends Form {

	/** @var $plugin object */
	public $plugin;

	/**
	 * Constructor
	 * @param $plugin object
	 */
	public function __construct($plugin) {
		parent::__construct($plugin->getTemplateResource('settings.tpl'));
		$this->plugin = $plugin;
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
	}

	/**
	* @copydoc Form::init
	*/
	public function initData() {
		$request = Application::get()->getRequest();
		$context = $request->getContext();
		$contextId = $context ? $context->getId() : 0;
		$this->setData('type', $this->plugin->getSetting($contextId, 'type'));
		$this->setData('acronym', $this->plugin->getSetting($contextId, 'acronym'));
		$this->setData('volume', $this->plugin->getSetting($contextId, 'volume'));
		$this->setData('number', $this->plugin->getSetting($contextId, 'number'));
		$this->setData('pages', $this->plugin->getSetting($contextId, 'pages'));
		$this->setData('fileId', $this->plugin->getSetting($contextId, 'fileId'));		
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	public function readInputData() {
		$this->readUserVars(array(
			'type',
			'acronym',
			'volume',
			'number',
			'pages',
			'fileId'
		));
	}

	/**
	 * @copydoc Form::fetch()
	 */
	public function fetch($request, $template = null, $display = false) {
		$context = $request->getContext();
		$contextId = $context ? $context->getId() : 0;

		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign(array(
			'pluginName' => $this->plugin->getName()
		));

		return parent::fetch($request, $template, $display);
	}

	/**
	 * @copydoc Form::execute()
	 */
	public function execute(...$functionArgs) {
		$request = Application::get()->getRequest();
		$context = $request->getContext();
		$contextId = $context ? $context->getId() : 0;
		
		$this->plugin->updateSetting($contextId, 'type',$this->getData('type'));
		$this->plugin->updateSetting($contextId, 'acronym',$this->getData('acronym'));
		$this->plugin->updateSetting($contextId, 'volume',$this->getData('volume'));
		$this->plugin->updateSetting($contextId, 'number',$this->getData('number'));
		$this->plugin->updateSetting($contextId, 'pages',$this->getData('pages'));
		$this->plugin->updateSetting($contextId, 'fileId',$this->getData('fileId'));
		
		import('classes.notification.NotificationManager');
		$notificationMgr = new NotificationManager();
		$user = $request->getUser();
		$notificationMgr->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_SUCCESS, array('contents' => __('common.changesSaved')));

		return parent::execute(...$functionArgs);
	}
}

