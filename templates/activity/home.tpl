<div id="topbar" class="black">
	<div id="leftnav">
		<a href="{devblocks_url}{/devblocks_url}iphone/"><img alt="home" src="{devblocks_url}c=resource&p=cerberusweb.iphone&f=Framework/images/home.png{/devblocks_url}" /></a>
	</div>
</div>
<div id="content">

	<span class="graytitle">Activity</span>
	<ul class="pageitem">			
		{foreach from=$actions item=action}
		<li class="menu"><a href="{devblocks_url}{/devblocks_url}iphone/activity/{$action->params['uri']}">
			<span class="name">{$action->params['title']}</span><span class="arrow"></span></a>
		</li>
		{/foreach}
	</ul>
</div>