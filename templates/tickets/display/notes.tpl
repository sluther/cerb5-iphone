{if isset($message_notes.$message_id) && is_array($message_notes.$message_id)}
	{foreach from=$message_notes.$message_id item=note name=notes key=note_id}
		<div class="message_note" style="margin:10px;margin-left:20px;">
			{if 1 == $note->type}
				<h3 style="display:inline;color:rgb(255,153,0);">warning:</h3>&nbsp;
			{elseif 2 == $note->type}
				<h3 style="display:inline;color:rgb(255,50,50);">error:</h3>&nbsp;
			{else}
				{assign var=note_worker_id value=$note->worker_id}
				{if $workers.$note_worker_id}
					<h3 style="display:inline;"><span style="color:rgb(222,73,0);background-color:rgb(255,235,104);">{$translate->_('display.ui.sticky_note')|lower}</span> <a href="javascript:;" onclick="genericAjaxPanel('c=contacts&a=showAddressPeek&email={$workers.$note_worker_id->email|escape:'url'}', this, false, '500');" title="{$workers.$note_worker_id->email|escape}">{if empty($workers.$note_worker_id->first_name) && empty($workers.$note_worker_id->last_name)}&lt;{$workers.$note_worker_id->email}&gt;{else}{$workers.$note_worker_id->getName()}{/if}</a></h3>&nbsp;
				{else}
					<h3 style="display:inline;"><span style="color:rgb(222,73,0);background-color:rgb(255,235,104);">{$translate->_('display.ui.sticky_note')|lower}</span> (Deleted Worker)</h3>&nbsp;
				{/if}
			{/if}
			<a href="javascript:;" onclick="genericAjaxGet('{$message_id}notes','c=display&a=deleteNote&id={$note_id}');">{$translate->_('common.delete')|lower}</a><br>
			<b>{$translate->_('message.header.date')|capitalize}:</b> {$note->created|devblocks_date}<br>
			{if !empty($note->content)}<pre>{$note->content|escape|devblocks_hyperlinks}</pre>{/if}
		</div>
	{/foreach}
{/if}
