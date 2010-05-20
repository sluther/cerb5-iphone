<div id="topbar" class="black">
	<div id="title">Display</div>
	<div id="leftnav">
		<a href="{devblocks_url}{/devblocks_url}iphone/"><img alt="home" src="{devblocks_url}c=resource&p=cerberusweb.iphone&f=Framework/images/home.png{/devblocks_url}" /></a>
		<a href="{devblocks_url}{/devblocks_url}iphone/feedback">Feedback</a>
	</div>
</div>
<form action="{devblocks_url}{/devblocks_url}" method="POST">
	<input type="hidden" name="c" value="iphone">
	<input type="hidden" name="a" value="feedback">
	<input type="hidden" name="pageAction" value="saveFeedback">
	
	<input type="hidden" name="id" value="{$feedbackEntry->id}">
	<div id="content">
		<span class="graytitle">{$feedbackEntry->quote_text}</span>
		<ul class="pageitem">
			<li class="smallfield">
				<span class="name">Author E-mail</span>
				<input type="text" value="{$address->email}" name="email" />
			</li>
			<li class="radiobutton">
				<span class="name">{'feedback.mood.praise'|devblocks_translate}</span>
				<input name="mood" type="radio" value="1"{if $feedbackEntry->quote_mood == 1} checked="checked"{/if} />
			</li>
			<li class="radiobutton">
				<span class="name">{'feedback.mood.criticism'|devblocks_translate}</span>
				<input name="mood" type="radio" value="2"{if $feedbackEntry->quote_mood == 2} checked="checked"{/if} />
			</li>
			<li class="radiobutton">
				<span class="name">{'feedback.mood.neutral'|devblocks_translate}</span>
				<input name="mood" type="radio" value="0"{if $feedbackEntry->quote_mood != 1 && $feedbackEntry->quote_mood != 2} checked="checked"{/if} />
			</li>
			<li class="textbox">
				<span class="header">Quote</span>
				<textarea name="quote" rows="4">{$feedbackEntry->quote_text}</textarea>
			</li>
			<li class="smallfield">
				<span class="name">Link</span>
				<input name="url" type="text" value="{$feedbackEntry->source_url}">
			</li>
			{include file="file:$core_tpl/custom_fields.tpl" bulk=false}
			<li class="button">
				<input name="Submit input" type="submit" value="Save" />
			</li>
		</ul>
	</div>
</form>