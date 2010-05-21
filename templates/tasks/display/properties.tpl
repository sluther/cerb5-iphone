<form action="{devblocks_url}{/devblocks_url}" method="POST">
	<input type="hidden" name="c" value="iphone">
	<input type="hidden" name="a" value="tasks">
	<input type="hidden" name="pageAction" value="saveProperties">
	
	<input type="hidden" name="task_id" value="{$task->id}">
	<span class="graytitle">Properties</span>
	<ul class="pageitem">
		<li class="smallfield">
			<span class="name">Title:</span>
			<input type="text" value="{$task->title}" name="title" />
		</li>
		<li class="smallfield">
			<span class="name">Due Date:</span>
			<input type="text" value="{$task->due_date|devblocks_prettytime}" name="due_date" />
		<li class="select">
			<select name="worker_id">
				<option value="">- Select Worker -</option>
				{foreach from=$workers item=worker}
				<option value="{$worker->id}"{if $worker->id==$task->worker_id} selected="selected"{/if}>{$worker->getName()}</option>
				{/foreach}
			</select>
			<span class="arrow"></span>
		</li>
		<li class="checkbox">
			<span class="name">Completed:</span>
			<input type="checkbox" name="completed"{if $task->is_completed == 1} checked="checked"{/if}>
		{include file="file:$core_tpl/custom_fields.tpl" bulk=false}
		<li class="button">
			<input name="Submit input" type="submit" value="Save" />
		</li>
	</ul>
</form>