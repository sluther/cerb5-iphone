<div id="topbar" class="black">
	<div id="title">Display</div>
	<div id="leftnav">
		<a href="{devblocks_url}{/devblocks_url}iphone/"><img alt="home" src="{devblocks_url}c=resource&p=cerberusweb.iphone&f=Framework/images/home.png{/devblocks_url}" /></a>
		<a href="{devblocks_url}{/devblocks_url}iphone/opportunities">Opportunities</a>
	</div>
</div>
<div id="tributton">
	<div class="links">
		{foreach from=$tab_manifests item=tab_manifest}<a {if $selected_tab==$tab_manifest->params.uri}id="pressed"{/if}href="{devblocks_url}{/devblocks_url}iphone/opportunities/display/{$opp->id}/{$tab_manifest->params.uri}">{$tab_manifest->params.title}</a>{/foreach}

	</div>
</div>
<div id="content">
	<span class="graytitle">{$opp->name}</span>
	<ul class="pageitem">
		<li class="textbox"><span class="header">{'common.status'|devblocks_translate|capitalize}</span>
			{if $opp->is_closed}{if $opp->is_won}{'crm.opp.status.closed.won'|devblocks_translate|capitalize}{else}{'crm.opp.status.closed.lost'|devblocks_translate|capitalize}{/if}{else}{'crm.opp.status.open'|devblocks_translate|capitalize}{/if}
		</li>
		<li class="textbox"><span class="header">{'common.email'|devblocks_translate|capitalize}</span>
			<p>
				{$address->first_name} {$address->last_name} &lt;{$address->email}&gt;
			</p>
		</li>
		<li class="textbox"><span class="header">{$translate->_('crm.opportunity.amount')|capitalize}</span>
			<p>
				{if empty($opp->amount)}0{else}{math equation="floor(x)" x=$opp->amount}{/if}.{if empty($opp->amount)}00{else}{math equation="(x-floor(x))*100" x=$opp->amount}{/if}
			</p>
		</li>
		<li class="textbox"><span class="header">{'crm.opportunity.created_date'|devblocks_translate|capitalize}</span>
			<p>
				{$opp->created_date|devblocks_prettytime}
			</p>
		</li>
	</ul>
	{$tab->showTab()}
</div>