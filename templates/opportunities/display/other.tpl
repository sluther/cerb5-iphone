{if isset($sub_tab)}
	{$sub_tab->showTab()}
{else}
	<div id="content">
		<ul class="pageitem">

		{foreach from=$tab_manifests item=tab_manifest}
		<li class="menu">
			<a href="{devblocks_url}{/devblocks_url}iphone/opportunities/display/{$opp->id}/other/{$tab_manifest->params.uri}">
			<span class="name">{$tab_manifest->params.title}</span><span class="arrow"></span></a>
		</li>
		{/foreach}
		</ul>
	</div>
{/if}