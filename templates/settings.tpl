{**
 * plugins/generic/downloadFileNames/templates/settings.tpl
 *
 * Copyright (c) 2017-2020 Simon Fraser University
 * Copyright (c) 2017-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 *
 *}
<script>
	$(function() {ldelim}
		// Attach the form handler.
		$('#downloadFileNamesSettingsForm').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
	{rdelim});
</script>

<form class="pkp_form" id="downloadFileNamesSettingsForm" method="post" action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}">
	{csrf}

	{fbvFormArea id="downloadFileNamesPluginSettings"}
		{fbvFormSection list=true title="plugins.generic.downloadFileNames.settings.types"}
			<p>{translate key="plugins.generic.downloadFileNames.settings.typesDescription"}</p>		
			{fbvElement type="radio" name="type" id="type1" value=1 checked=($type==="1") label={translate key="plugins.generic.downloadFileNames.variableType.label"} translate=false}		
				<div style="text-indent: 50px">
				{fbvFormSection list=true}
					{fbvElement type="checkbox" id="acronym" checked=$acronym label={translate key="plugins.generic.downloadFileNames.acronym.description"} translate=false}
					{fbvElement type="checkbox" id="volume" checked=$volume label={translate key="plugins.generic.downloadFileNames.volume.description"} translate=false}
					{fbvElement type="checkbox" id="number" checked=$number label={translate key="plugins.generic.downloadFileNames.number.description"} translate=false}
					{fbvElement type="checkbox" id="pages" checked=$pages label={translate key="plugins.generic.downloadFileNames.pages.description"} translate=false}				
					{fbvElement type="checkbox" id="fileId" checked=$fileId label={translate key="plugins.generic.downloadFileNames.fileId.description"} translate=false}
					{fbvElement type="checkbox" id="author" checked=$author label={translate key="plugins.generic.downloadFileNames.author.description"} translate=false}				
					{fbvElement type="checkbox" id="title" checked=$title label={translate key="plugins.generic.downloadFileNames.title.description"} translate=false}					
				{/fbvFormSection}
				</div>			
			{fbvElement type="radio" name="type" id="type2" value=2 checked=($type==="2") label={translate key="plugins.generic.downloadFileNames.originalType.label"} translate=false}				
		{/fbvFormSection}
	
	{/fbvFormArea}

	{fbvFormButtons}
</form>
