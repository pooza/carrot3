{*
@package jp.co.b-shock.carrot3
@subpackage AdminTableReport
@author 小石達也 <tkoishi@b-shock.co.jp>
*}
{include file='AdminHeader'}

<nav class="bread_crumbs">
  <a href="/{$module.name}/">データベース一覧</a>
  <a href="/{$module.name}/Database?database={$database.name}">データベース:{$database.name}</a>
  <a href="#">{$action.title}</a>
</nav>

<h1>{$action.title}</h1>

<h2>基本属性</h2>
<table class="detail">
  <tr>
    <th>物理テーブル名</th>
    <td>{$table.name}</td>
  </tr>
  <tr>
    <th>論理テーブル名</th>
    <td>{$table.name_ja}</td>
  </tr>
  <tr>
    <th>テーブルクラス名</th>
    <td>{$table.class}</td>
  </tr>
  <tr>
    <th>レコードクラス名</th>
    <td>{$table.record_class}</td>
  </tr>
</table>

<h2>フィールド</h2>
<table>
  <tr>
    <th width="120">フィールド名</th>
    <th width="90">データ型</th>
    <th width="60">データ長</th>
    <th width="30">NULL</th>
    <th width="180">既定値</th>
    <th width="90">外部キー</th>
    <th width="120">その他</th>
  </tr>
  {foreach from=$table.fields item=field}
    <tr>
      <td width="120">
        {$field.column_name}<br>
        <small>{$field.column_name|translate:$table.name}</small>
      </td>
      <td width="90">{$field.data_type}</td>
      <td width="60" align="right">{$field.character_maximum_length}</td>
      <td width="30" align="center">{if $field.is_nullable}可{/if}</td>
      <td width="180">{$field.column_default}</td>
      <td width="90">
        {if $field.extrenal_table}
          <a href="/{$module.name}/Table?database={$database.name}&amp;table={$field.extrenal_table}">{$field.extrenal_table}</a>
        {/if}
      </td>
      <td width="120">{$field.extra}</td>
    </tr>
  {foreachelse}
    <tr>
      <td colspan="7">フィールド情報がありません。</td>
    </tr>
  {/foreach}
</table>

<h2>制約</h2>
<table>
  <tr>
    <th width="210">制約名</th>
    <th width="120">制約種類</th>
    <th width="270">対象フィールド（参照先）</th>
  </tr>
  {foreach from=$table.constraints item=constraint}
    <tr>
      <td width="210">{$constraint.name}</td>
      <td width="120">{$constraint.type|default:'(不明)'}</td>
      <td width="270">
        {foreach from=$constraint.fields item=field}
          {$field.column_name}
          {if $field.referenced_table_name}
            (<a href="/{$module.name}/Table?database={$database.name}&amp;table={$field.referenced_table_name}">{$field.referenced_table_name}</a>.{$field.referenced_column_name})
          {/if}
          <br>
        {/foreach}
      </td>
    </tr>
  {foreachelse}
    <tr>
      <td colspan="3">キー情報がありません。</td>
    </tr>
  {/foreach}
</table>

{include file='AdminFooter'}

