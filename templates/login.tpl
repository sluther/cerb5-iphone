<div id="topbar" class="black">
	<div id="title">Sign on</div>
</div>
<div id="content">
	<span class="graytitle">{$translate->_('header.signon')|capitalize}</span>
	<form action="{devblocks_url}{/devblocks_url}" method="post" id="iPhoneLoginForm">
		<input type="hidden" name="c" value="iphone">
		<input type="hidden" name="a" value="login">
		<input type="hidden" name="pageAction" value="authenticate">
		<input type="hidden" name="original_path" value="{$original_path}">
		<ul class="pageitem">
			<li class="bigfield"><input placeholder="E-mail" type="text" name="email" /></li>
			<li class="bigfield">
			<input placeholder="Password" type="password" name="password" /></li>
			<li class="button">
			<input name="login" type="submit" value="{$translate->_('header.signon')|capitalize}" /></li>
		</ul>
	</form>
</div>