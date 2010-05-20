{assign var=total value=$results[1]}
{assign var=feedbackEntries value=$results[0]}
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
		{foreach from=$feedbackEntries item=feedbackEntry}
		<li class="store">
			<a href="{devblocks_url}{/devblocks_url}iphone/feedback/display/{$feedbackEntry.f_id}/">
			<span class="name">{$feedbackEntry.f_quote_text}</span>
			{section name=row loop=$sections}
				<span class="comment{if $smarty.section.row.index > 0}2{/if}">
					{foreach from=array_slice($view->view_columns, $smarty.section.row.index * $maxcols, $maxcols) item=column}
					{$view_fields.$column->db_label}:
					{if $column=="f_quote_mood"}
						{if $feedbackEntry.f_quote_mood==1}
							{'feedback.mood.praise'|devblocks_translate}
						{elseif $feedbackEntry.f_quote_mood==2}
							{'feedback.mood.criticism'|devblocks_translate}
						{else}
							{'feedback.mood.neutral'|devblocks_translate}
						{/if}						
					{else}
						{$feedbackEntry.$column}
					{/if}
					{/foreach}
				</span>
			{/section}
			<span class="arrow"></span></a>
		</li>
		{/foreach}
	</ul>
</div>
