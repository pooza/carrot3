{*
@package jp.co.dipps.carrot3
@subpackage AdminLog
@author 小石達也 <tkoishi@b-shock.co.jp>
*}
{include file='AdminHeader'}
{form method='get'}
  {html_options name='date' options=$dates selected=$params.date}
  <input type="search" name="key" value="{$params.key}" placeholder="検索キーワード" size="20" autofocus>
  <input type="submit" value="表示">
{/form}

<h1>{$action.title}</h1>
<table>
  <tr>
    <th width="60">時刻</th>
    <th width="120">ホスト</th>
    <th width="600">内容</th>
  </tr>
  {foreach from=$entries item=log}
    <tr class="log {if $log.exception}alert{/if}">
      <td width="60">{$log.date|date_format:'H:i:s'}</td>
      <td width="150">{$log.remote_host}</td>
      <td width="600">{$log.message}</td>
    </tr>
  {foreachelse}
    <tr>
      <td colspan="3">該当するエントリーがありません。</td>
    </tr>
  {/foreach}
</table>

{include file='AdminFooter'}

