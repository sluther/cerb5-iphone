<form action="{devblocks_url}{/devblocks_url}" method="POST">
	<input type="hidden" name="c" value="iphone">
	<input type="hidden" name="a" value="opportunities">
	<input type="hidden" name="pageAction" value="saveProperties">
	
	<input type="hidden" name="opp_id" value="{$opp->id}">
	<span class="graytitle">Properties</span>
	<ul class="pageitem">
		<li class="smallfield">
			<span class="name">Email</span>
			<input type="text" value="{$address->email}" name="email" />
		</li>
		<li class="smallfield">
			<span class="name">Title</span>
			<input type="text" value="{$opp->name}" name="name" />
		</li>
		<li class="smallfield">
			<span class="name">Amount</span>
			<input type="text" value="{if empty($opp->amount)}0{else}{math equation="floor(x)" x=$opp->amount}{/if}.{if empty($opp->amount)}00{else}{math equation="(x-floor(x))*100" x=$opp->amount}{/if}" name="amount" />
		</li>
		<li class="select">
			<select name="status">
				<option value="">- Select Status -</option>
				<option value="open"{if $opp->is_closed==0} selected="selected"{/if}>Open</option>
				<option value="won"{if $opp->is_closed==1 && $opp->is_won==1} selected="selected"{/if}>Won</option>
				<option value="lost"{if $opp->is_closed==1 && $opp->is_won==0} selected="selected"{/if}>Lost</option>
			</select>
			<span class="arrow"></span>
		</li>		
		<li class="select">
			<select name="worker">
				<option value="">- Select Worker -</option>
				{foreach from=$workers item=worker}
				<option value="{$worker->id}"{if $worker->id==$opp->worker_id} selected="selected"{/if}>{$worker->getName()}</option>
				{/foreach}
			</select>
			<span class="arrow"></span>
		</li>
		{include file="file:$core_tpl/custom_fields.tpl" bulk=false}
		<li class="button">
			<input name="Submit input" type="submit" value="Save" />
		</li>
	</ul>
</form>