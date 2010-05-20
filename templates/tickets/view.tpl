{assign var=total value=$results[1]}
{assign var=tickets value=$results[0]}
{if count($view->view_columns) < $maxcols}
	{assign var=sections value=1}
{else}
	{math assign=sections equation="floor(x/y)" x=count($view->view_columns) y=$maxcols}
	{if $sections > 3}
		{assign var=sections value=3}
	{/if}
{/if}
<div id="content">
	<ul class="pageitem">
		{foreach from=$tickets item=ticket}
		<li class="store">
			<a href="{devblocks_url}{/devblocks_url}iphone/tickets/display/{$ticket.t_id}/conversation/">
				<span class="name">{$ticket.t_subject|escape}</span>
				{section name=row loop=$sections}
				<span class="comment{if $smarty.section.row.index > 0}2{/if}">
					{foreach from=array_slice($view->view_columns, $smarty.section.row.index * $maxcols, $maxcols) item=column}

					{$view_fields.$column->db_label|capitalize}:
						{if $column=="t_id"}
							{$ticket.t_id}
						{elseif $column=="t_mask"}
							{$ticket.t_mask}
						{elseif $column=="t_is_waiting"}
						{if $ticket.t_is_waiting}{$translate->_('status.waiting')}{else}{/if}
						{elseif $column=="t_is_closed"}
						{if $ticket.t_is_closed}{$translate->_('status.closed')}{else}{/if}
						{elseif $column=="t_is_deleted"}
						{if $ticket.t_is_deleted}{$translate->_('status.deleted')}{else}{/if}
						{elseif $column=="t_last_wrote"}
						{$ticket.t_last_wrote|truncate:45:'...':true:true}
						{elseif $column=="t_first_wrote"}
						{$ticket.t_first_wrote|truncate:45:'...':true:true}
						{elseif $column=="t_created_date"}
						{$ticket.t_created_date|devblocks_prettytime}
						{elseif $column=="t_updated_date"}
							{$ticket.t_updated_date|devblocks_prettytime}
						{elseif $column=="t_due_date"}
						{if $ticket.t_due_date}{$ticket.t_due_date|devblocks_prettytime}{/if}
						{*{elseif $column=="t_tasks"}
						{if !empty($ticket.t_tasks)}{$ticket.t_tasks}{/if}*}
						{elseif $column=="t_team_id"}
							{assign var=ticket_team_id value=$ticket.t_team_id}
							{$teams.$ticket_team_id->name}
						{elseif $column=="t_interesting_words"}
						{$ticket.t_interesting_words|replace:',':', '}
						{elseif $column=="t_category_id"}
							{assign var=ticket_team_id value=$ticket.t_team_id}
							{assign var=ticket_category_id value=$ticket.t_category_id}

								{if 0 == $ticket_category_id}
									Inbox
								{else}
									{$buckets.$ticket_category_id->name}
								{/if}
							
						{elseif $column=="t_last_action_code"}
						
							{if $ticket.t_last_action_code=='O'}
								{assign var=action_worker_id value=$ticket.t_next_worker_id}
								New 
								{if isset($workers.$action_worker_id)}for {$workers.$action_worker_id->getName()}{else}from {$ticket.t_first_wrote|truncate:15:'...':true:true}{/if}
							{elseif $ticket.t_last_action_code=='R'}
								{assign var=action_worker_id value=$ticket.t_next_worker_id}
								{if isset($workers.$action_worker_id)}
									{'mail.received'|devblocks_translate} for {$workers.$action_worker_id->getName()}
								{else}
									{'mail.received'|devblocks_translate} from {$ticket.t_last_wrote|truncate:15:'...':true:true}
								{/if}
							{elseif $ticket.t_last_action_code=='W'}
								{assign var=action_worker_id value=$ticket.t_last_worker_id}
								{if isset($workers.$action_worker_id)}
									{'mail.sent'|devblocks_translate} from {$workers.$action_worker_id->getName()}
								{else}
									{'mail.sent'|devblocks_translate} from Helpdesk
								{/if}
							{/if}
						{elseif $column=="t_last_worker_id"}
							{assign var=action_worker_id value=$ticket.t_last_worker_id}
							{if isset($workers.$action_worker_id)}{$workers.$action_worker_id->getName()}{/if}
						{elseif $column=="t_next_worker_id"}
							{assign var=action_worker_id value=$ticket.t_next_worker_id}
							{if isset($workers.$action_worker_id)}{$workers.$action_worker_id->getName()}{/if}
						{elseif $column=="t_first_wrote_spam"}
						{$ticket.t_first_wrote_spam}
						{elseif $column=="t_first_wrote_nonspam"}
						{$ticket.t_first_wrote_nonspam}
						{elseif $column=="t_spam_score" || $column=="t_spam_training"}
							{math assign=score equation="x*100" format="%0.2f%%" x=$ticket.t_spam_score}
							{if empty($ticket.t_spam_training)}
								{if $active_worker->hasPriv('core.ticket.actions.spam')}{/if}
							
							{/if}
						{else}
						{if $ticket.$column}{$ticket.$column}{/if}
						{/if}
					{/foreach}
				</span>
				{/section}

				<span class="arrow"></span>
			</a>
		</li>
		{/foreach}
	</ul>
</div>
<div id="duobutton">
	<div class="links">

		{math assign=prevPageFromRow equation="(x*y)-9" x=$view->renderPage y=$view->renderLimit}
		{math assign=prevPageToRow equation="(x-1)+y" x=$prevPageFromRow y=$view->renderLimit}
		{math assign=nextPageFromRow equation="(x*y)+11" x=$view->renderPage y=$view->renderLimit}
		{math assign=nextPageToRow equation="(x-1)+y" x=$nextPageFromRow y=$view->renderLimit}
		{math assign=nextPage equation="x+1" x=$view->renderPage}
		{math assign=prevPage equation="x-1" x=$view->renderPage}
		{math assign=lastPage equation="ceil(x/y)-1" x=$total y=$view->renderLimit}
		
		{* Sanity checks *}
		{if $nextPageToRow > $total}{assign var=nextPageToRow value=$total}{/if}
		{if $nextPageFromRow > $nextPageToRow}{assign var=nextPageFromRow value=$nextPageToRow}{/if}

		{if $view->renderPage == $lastPage}<a href="{devblocks_url}{/devblocks_url}iphone/{$uri}/{$prevPage}">
			&lt;&lt; {$translate->_('common.previous_short')|capitalize} ({$prevPageFromRow} - {$prevPageToRow})</a><a href="#">
			{$translate->_('common.next')|capitalize}</a>{elseif $view->renderPage > 0}<a href="{devblocks_url}{/devblocks_url}iphone/{$uri}/{$prevPage}">
				{$translate->_('common.previous_short')|capitalize} ({$prevPageFromRow} - {$prevPageToRow})
				</a><a href="{devblocks_url}{/devblocks_url}iphone/{$uri}/{$nextPage}">
					{$translate->_('common.next')|capitalize} ({$nextPageFromRow} - {$nextPageToRow})
				</a>{else}<a href="#">
					{$translate->_('common.previous_short')|capitalize}
					</a>{if $nextPageToRow < $total}<a href="{devblocks_url}{/devblocks_url}iphone/{$uri}/{$nextPage}">
					{$translate->_('common.next')|capitalize} ({$nextPageFromRow} - {$nextPageToRow})
				</a>
			{/if}
		{/if}

	</div>
</div>