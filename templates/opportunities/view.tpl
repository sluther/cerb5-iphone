{assign var=total value=$results[1]}
{assign var=opportunities value=$results[0]}
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
		{foreach from=$opportunities item=opp}
		<li class="store"><a href="{devblocks_url}{/devblocks_url}iphone/opportunities/display/{$opp.o_id}/">
			<span class="name">{$opp.o_name}</span><span class="arrow"></span></a>
			{section name=row loop=$sections}
				<span class="comment{if $smarty.section.row.index > 0}2{/if}">
					{foreach from=array_slice($view->view_columns, $smarty.section.row.index * $maxcols, $maxcols) item=column}
						{$view_fields.$column->db_label}: {$opp.$column}
					{/foreach}
				</span>
			{/section}
		</li>
		{/foreach}
	</ul>
</div>