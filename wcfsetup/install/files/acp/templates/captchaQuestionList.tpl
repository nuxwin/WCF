{include file='header' pageTitle='wcf.acp.captcha.question.list'}

<header class="boxHeadline">
	<h1>{lang}wcf.acp.captcha.question.list{/lang}</h1>
</header>

<script data-relocate="true">
	//<![CDATA[
	$(function() {
		new WCF.Action.Delete('wcf\\data\\captcha\\question\\CaptchaQuestionAction', '.jsQuestionRow');
		new WCF.Action.Toggle('wcf\\data\\captcha\\question\\CaptchaQuestionAction', '.jsQuestionRow');
		
		var options = { };
		{if $pages > 1}
			options.refreshPage = true;
			{if $pages == $pageNo}
				options.updatePageNumber = -1;
			{/if}
		{else}
			options.emptyMessage = '{lang}wcf.global.noItems{/lang}';
		{/if}
		
		new WCF.Table.EmptyTableHandler($('#captchaQuestionTabelContainer'), 'jsQuestionRow', options);
	});
	//]]>
</script>

<div class="contentNavigation">
	{pages print=true assign=pagesLinks controller="CaptchaQuestionList" link="pageNo=%d"}
	
	<nav>
		<ul>
			<li><a href="{link controller='CaptchaQuestionAdd'}{/link}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.captcha.question.add{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsTop'}
		</ul>
	</nav>
</div>

{hascontent}
	<div id="captchaQuestionTabelContainer" class="tabularBox tabularBoxTitle marginTop">
		<header>
			<h2>{lang}wcf.acp.captcha.question.list{/lang} <span class="badge badgeInverse">{#$items}</span></h2>
		</header>
		
		<table class="table">
			<thead>
				<tr>
					<th class="columnID columnQuestionID active ASC" colspan="2">{lang}wcf.global.objectID{/lang}</th>
					<th class="columnText columnQuestion">{lang}wcf.acp.captcha.question.question{/lang}</th>
					
					{event name='columnHeads'}
				</tr>
			</thead>
			
			<tbody>
				{content}
					{foreach from=$objects item='question'}
						<tr class="jsQuestionRow">
							<td class="columnIcon">
								<span class="icon icon16 icon-check{if $question->isDisabled}-empty{/if} jsToggleButton jsTooltip pointer" title="{lang}wcf.global.button.{if $question->isDisabled}enable{else}disable{/if}{/lang}" data-object-id="{@$question->questionID}" data-disable-message="{lang}wcf.global.button.disable{/lang}" data-enable-message="{lang}wcf.global.button.enable{/lang}"></span>
								<a href="{link controller='CaptchaQuestionEdit' id=$question->questionID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon16 icon-pencil"></span></a>
								<span class="icon icon16 icon-remove jsDeleteButton jsTooltip pointer" title="{lang}wcf.global.button.delete{/lang}" data-object-id="{@$question->questionID}" data-confirm-message="{lang}wcf.acp.captcha.question.delete.confirmMessage{/lang}"></span>
								
								{event name='rowButtons'}
							</td>
							<td class="columnID columnQuestionID">{$question->questionID}</td>
							<td class="columnText columnQuestion"><a href="{link controller='CaptchaQuestionEdit' id=$question->questionID}{/link}">{lang}{$question->question}{/lang}</a></td>
							
							{event name='columns'}
						</tr>
					{/foreach}
				{/content}
			</tbody>
		</table>
	</div>
{hascontentelse}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/hascontent}

<div class="contentNavigation">
	{@$pagesLinks}
	
	<nav>
		<ul>
			<li><a href="{link controller='CaptchaQuestionAdd'}{/link}" class="button"><span class="icon icon16 icon-plus"></span> <span>{lang}wcf.acp.captcha.question.add{/lang}</span></a></li>
			
			{event name='contentNavigationButtonsBottom'}
		</ul>
	</nav>
</div>

{include file='footer'}
 