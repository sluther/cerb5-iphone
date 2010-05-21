<div id="topbar" class="black">
	<div id="title">Display</div>
	<div id="leftnav">
		<a href="{devblocks_url}{/devblocks_url}iphone/"><img alt="home" src="{devblocks_url}c=resource&p=cerberusweb.iphone&f=Framework/images/home.png{/devblocks_url}" /></a>
		<a href="{devblocks_url}{/devblocks_url}iphone/tasks/">Mail</a>
	</div>
</div>
<div id="duobutton">
	<div class="links">
		{foreach from=$tab_manifests item=tab_manifest}<a {if $selected_tab==$tab_manifest->params.uri}id="pressed"{/if}href="{devblocks_url}{/devblocks_url}iphone/tasks/display/{$task->id}/{$tab_manifest->params.uri}">{$tab_manifest->params.title}</a>{/foreach}

	</div>
</div>
<div id="content">
	<span class="graytitle">{$task->title|escape}</span>
	<ul class="pageitem">
		<li class="textbox">
			<span class="header">{'task.is_completed'|devblocks_translate|capitalize}:</span>
			{if $task->is_completed}{'common.yes'|devblocks_translate|capitalize}{else}{'common.no'|devblocks_translate|capitalize}{/if}
		</li>
		{if !empty($task->updated_date)}
		<li class="textbox">
			<span class="header">{'task.updated_date'|devblocks_translate|capitalize}:</span>
			<abbr title="{$task->updated_date|devblocks_date}">{$task->updated_date|devblocks_prettytime}</abbr>
		</li>
		{/if}
		{if !empty($task->due_date)}
		<li class="textbox">
			<span class="header">{'task.due_date'|devblocks_translate|capitalize}:</span>
			<abbr title="{$task->due_date|devblocks_date}">{$task->due_date|devblocks_prettytime}</abbr>
		</li>
		{/if}
		
		{assign var=task_worker_id value=$task->worker_id}
		{if !empty($task_worker_id) && isset($workers.$task_worker_id)}
		<li class="textbox">
			<span class="header">{'common.worker'|devblocks_translate|capitalize}:</span>
			{$workers.$task_worker_id->getName()}
		</li>
		{/if}
	</ul>
	{$tab->showTab()}
</div>