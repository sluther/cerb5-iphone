<span class="graytitle">Notes</span>
<ul class="pageitem">
	{foreach from=$notes item=note}
	{assign var=worker_id value=$note.n_worker_id}
	<li class="textbox"><span class="header">{if isset($workers.$worker_id)}{$workers.$worker_id->getName()}{else}{'common.anonymous'|devblocks_translate}{/if}</span>
		<p>
			{$note.n_content|escape|nl2br}
		</p>
	</li>
	{/foreach}
</ul>
<span class="graytitle">Add Note</span>
<form>
	<ul class="pageitem">
		<li class="textbox"><span class="header">Insert text</span><textarea name="note" rows="4"></textarea></li>
		<li class="button"><input name="Submit input" type="submit" value="Add note" /></li>
	</ul>
</form>