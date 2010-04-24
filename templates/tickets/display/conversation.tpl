	<ul class="pageitem">
		{if !empty($ticket)}
			{if !empty($convo_timeline)}
				{foreach from=$convo_timeline item=convo_set name=items}
				<li class="textbox">
					<!-- Doo -->
					{if $convo_set.0=='m'}
						{assign var=message_id value=$convo_set.1}
						{assign var=message value=$messages.$message_id}
						<!-- <div id="{$message->id}t" style="background-color:rgb(255,255,255);"> -->
							{assign var=expanded value=false}
							{if $expand_all || $latest_message_id==$message_id || isset($message_notes.$message_id)}{assign var=expanded value=true}{/if}
							{include file="$core_tpl/tickets/display/message.tpl" expanded=$expanded}
						<!-- </div> -->

					{elseif $convo_set.0=='c'}
						{assign var=comment_id value=$convo_set.1}
						{assign var=comment value=$comments.$comment_id}
						<!-- <div id="comment{$comment->id}" style="background-color:rgb(255,255,255);"> -->
							{include file="$core_tpl/tickets/display/comment.tpl"}
						<!-- </div> -->

					{elseif $convo_set.0=='d'}
						{assign var=draft_id value=$convo_set.1}
						{assign var=draft value=$drafts.$draft_id}
						<!-- <div id="draft{$draft->id}" style="background-color:rgb(255,255,255);"> -->
							{include file="$core_tpl/tickets/display/draft.tpl"}
						<!-- </div> -->
					{/if}
					</li>
				{/foreach}
			{/if}

		{else}
		  <li class="textbox">
		  {$translate->_('display.convo.no_messages')}
		  <br>
		  </li>
		{/if}

	</ul>