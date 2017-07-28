{*
@package jp.co.dipps.carrot3
@subpackage DevelopTableReport
@author 小石達也 <tkoishi@b-shock.co.jp>
*}
{include file='AdminHeader'}
{include file='ErrorMessages' hide_error_code=true}

<nav class="bread_crumbs">
  <a href="/{$module.name}/">データベース一覧</a>
  <a href="#">{$action.title}</a>
</nav>

<h1>{$action.title}</h1>

<div>
  <input id="update_button" type="button" value="テーブル情報更新">
  <input id="optimize_button" type="button" value="最適化">
</div>

<h2>基本情報</h2>
<table class="detail">
  {foreach from=$database.attributes key=key item=value}
    <tr>
      <th>{$key|translate:'carrot.TableReport'}</th>
      <td>{$value|default:'(空欄)'}</td>
    </tr>
  {foreachelse}
    <tr>
      <td colspan="2">基本情報がありません。</td>
    </tr>
  {/foreach}
</table>

<h2>テーブル</h2>
<table>
  <tr>
    <th width="240">物理テーブル名</th>
    <th width="240">論理テーブル名</th>
  </tr>
  {foreach from=$database.tables item='table'}
    <tr>
      <td width="240">
        <a href="/{$module.name}/Table?database={$database.name}&table={$table}">{$table}</a>
      </td>
      <td width="240">{$table|translate}</td>
    </tr>
  {foreachelse}
    <tr>
      <td colspan="2" class="alert">該当するテーブルがありません。</td>
    </tr>
  {/foreach}
</table>

<script>
document.observe('dom:loaded', function () {
  $('update_button').observe('click', function() {
    CarrotLib.redirect('{$module.name}', 'Update', null, {
      'database': '{$database.name}'
    });
  });
  $('optimize_button').observe('click', function() {
    CarrotLib.redirect('{$module.name}', 'Optimize', null, {
      'database': '{$database.name}'
    });
  });
});
</script>

{include file='AdminFooter'}

