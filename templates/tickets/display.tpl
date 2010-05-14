<div id="topbar" class="black">
	<div id="title">Display</div>
	<div id="leftnav">
		<a href="{devblocks_url}{/devblocks_url}iphone/"><img alt="home" src="{devblocks_url}c=resource&p=cerberusweb.iphone&f=Framework/images/home.png{/devblocks_url}" /></a>
		<a href="{devblocks_url}{/devblocks_url}iphone/tickets/">Mail</a>
	</div>
</div>
<div id="tributton">
	<div class="links">
		{foreach from=$tab_manifests item=tab_manifest}<a {if $selected_tab==$tab_manifest->params.uri}id="pressed"{/if}href="{devblocks_url}{/devblocks_url}iphone/tickets/display/{$ticket->id}/{$tab_manifest->params.uri}">{$tab_manifest->params.title}</a>{/foreach}

	</div>
</div>
<div id="content">
	<span class="graytitle">Ticket #{$ticket->mask}</span>
	{$tab->showTab()}
</div>