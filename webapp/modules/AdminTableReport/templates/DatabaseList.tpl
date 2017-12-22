{*
@package jp.co.b-shock.carrot3
@subpackage AdminTableReport
@author 小石達也 <tkoishi@b-shock.co.jp>
*}
{include file='AdminHeader'}
{include file='ErrorMessages' hide_error_code=true}

<nav class="bread_crumbs">
  <a href="#">{$action.title}</a>
</nav>

<h1>{$action.title}</h1>

<table>
  <tr>
    <th width="90">接続名</th>
    <th width="60">DBMS</th>
    <th width="600">DSN</th>
  </tr>
  {foreach from=$databases item='database'}
    <tr>
      <td width="90">
        <a href="/{$module.name}/Database?database={$database.connection_name}">{$database.connection_name}</a>
      </td>
      <td width="60">{$database.dbms}</td>
      <td width="600">{$database.dsn}</td>
    </tr>
  {foreachelse}
    <tr>
      <td colspan="3" class="alert">該当するデータベースがありません。</td>
    </tr>
  {/foreach}
</table>

{include file='AdminFooter'}

