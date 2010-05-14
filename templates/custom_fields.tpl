	{foreach from=$custom_fields item=f key=f_id}
		<input type="hidden" name="field_ids[]" value="{$f_id}">
			{if $f->type=='S' || $f->type=='U' || $f->type=='N'}
				<li class="smallfield">
					<span class="name">{$f->name}</span>
					<input type="text" name="field_{$f_id}" size="45" maxlength="255" value="{$custom_field_values.$f_id|escape}">
				</li>
			{elseif $f->type=='T'}
				<li class="textbox">
					<span class="header">{$f->name}</span>
					<textarea name="field_{$f_id}" rows="4" cols="50">{$custom_field_values.$f_id|escape}</textarea>
				</li>
			{elseif $f->type=='C'}
				<li class="checkbox">
					<span class="name">{$f->name}</span>
					<input type="checkbox" name="field_{$f_id}" value="1" {if $custom_field_values.$f_id}checked="checked"{/if}>
				</li>
				
			{elseif $f->type=='X'}
				{foreach from=$f->options item=opt}
					<li class="checkbox"><span class="name">{$opt}</span><input type="checkbox" name="field_{$f_id}[]" value="{$opt|escape}" {if isset($custom_field_values.$f_id.$opt)}checked="checked"{/if}><br></li>
				{/foreach}
			{elseif $f->type=='D'}
				<li class="select">
					<select name="field_{$f_id}">
						<option value="">- {$f->name} -</option>
						{foreach from=$f->options item=opt}
							<option value="{$opt|escape}" {if $opt==$custom_field_values.$f_id}selected="selected"{/if}>{$opt}</option>
						{/foreach}
					</select>
					<span class="arrow"></span>
				</li>
			{elseif $f->type=='M'}
				<li class="select">
					<span class="header">{$f->name}</span>
					<select name="field_{$f_id}[]" size="5" multiple="multiple">
						{foreach from=$f->options item=opt}
						<option value="{$opt|escape}" {if isset($custom_field_values.$f_id.$opt)}selected="selected"{/if}>{$opt}</option>
						{/foreach}
					</select><br>
					<!-- <i><small>{$translate->_('common.tips.multi_select')}</small></i> -->
					<span class="arrow"></span>
				</li>
			{elseif $f->type=='W'}
				{if empty($workers)}
					{$workers = DAO_Worker::getAllActive()}
				{/if}
				<li class="select">
					<select name="field_{$f_id}">
						<option value="">- Select Worker -</option>
						<!-- <option value="">- Select Worker -</option> -->
						{foreach from=$workers item=worker}
							<option value="{$worker->id}" {if $worker->id==$custom_field_values.$f_id}selected="selected"{/if}>{$worker->getName()}</option>
						{/foreach}
					</select>
					<span class="arrow"></span>
				</li>
			{elseif $f->type=='F'}
				<input type="file" name="field_{$f_id}" size="45" maxlength="255" value="{$custom_field_values.$f_id|escape}">
			{elseif $f->type=='E'}
				<li class="smallfield">
					<span class="name">{$f->name}</span>
					<input type="text" name="field_{$f_id}" size="45" maxlength="255" value="{$custom_field_values.$f_id|escape}">
				</li>
				<!-- <div id="dateCustom{$f_id}"></div>
				<input type="text" id="field_{$f_id}" name="field_{$f_id}" size="30" maxlength="255" value="{if !empty($custom_field_values.$f_id)}{$custom_field_values.$f_id|devblocks_date}{/if}"><button type="button" onclick="devblocksAjaxDateChooser('#field_{$f_id}','#dateCustom{$f_id}');">&nbsp;<span class="cerb-sprite sprite-calendar"></span>&nbsp;</button> -->
			{/if}


	{/foreach}

