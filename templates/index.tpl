{include file="$core_tpl/header.tpl"}
{if !empty($page) && $page->isVisible()}
	{$page->render()}
{/if}
{include file="$core_tpl/footer.tpl"}