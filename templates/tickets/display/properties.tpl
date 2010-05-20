<form action="{devblocks_url}{/devblocks_url}" method="POST">
	<input type="hidden" name="c" value="iphone">
	<input type="hidden" name="a" value="tickets">
	<input type="hidden" name="pageAction" value="saveProperties">
	
	<input type="hidden" name="ticket_id" value="{$ticket->id}">
	<span class="graytitle">Properties</span>
	<ul class="pageitem">
		<li class="smallfield">
			<span class="name">Subject</span>
			<input type="text" value="{$ticket->subject}" name="subject" />
		</li>
		<li class="select">
			<select name="status">
				<option value="">- Status -</option>
				<option value="open"{if $ticket->is_closed==0} selected="selected"{/if}>Open</option>
				<option value="waiting"{if $ticket->is_waiting} selected="selected"{/if}>Waiting for Reply</option>
				<option value="closed"{if $ticket->is_closed==1} selected="selected"{/if}>Closed</option>
				<option value="deleted"{if $ticket->is_deleted==1} selected="selected"{/if}>Deleted</option>
			</select>
			<span class="arrow"></span>
		</li>		
		<li class="smallfield">
			<span class="name">Reopen date:</span>
			<input type="text" value="{$ticket->reopen_date|devblocks_prettytime}" name="ticket_reopen" />
		</li>
		<li class="select">
			<select name="next_worker_id">
				<option value="">- Who should handle the next reply? -</option>
				{foreach from=$workers item=worker}
				<option value="{$worker->id}"{if $worker->id==$ticket->next_worker_id} selected="selected"{/if}>{$worker->getName()}</option>
				{/foreach}
			</select>
			<span class="arrow"></span>
		</li>
		<li class="smallfield">
			<span class="name">Unlock date:</span>
			<input type="text" value="{$ticket->unlock_date|devblocks_prettytime}" name="unlock_date" />
		</li>
		{include file="file:$core_tpl/custom_fields.tpl" bulk=false}
		<li class="button">
			<input name="Submit input" type="submit" value="Save" />
		</li>
	</ul>
</form>