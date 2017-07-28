{*
@package jp.co.dipps.carrot3
@subpackage AdminMemcache
@author 小石達也 <tkoishi@b-shock.co.jp>
*}
{include file='AdminHeader'}

<h1>{$action.title}</h1>

<nav class="tabs">
  <ul>
    {foreach from=$servers key='name' item='server'}
      <li><a href="#{$name}_pane">{$name}</a></li>
    {/foreach}
  </ul>

  {foreach from=$servers key='name' item='server'}
    <div id="{$name}_pane">
      <table class="detail">
        {foreach from=$server key='key' item='value'}
          <tr>
            <th>{$key}</th>
            <td>{$value}</td>
          </tr>
        {foreachelse}
          <tr>
            <th></th>
            <td class="alert">未接続です。</td>
          </tr>
        {/foreach}
      </table>
    </div>
  {/foreach}
</nav>

{include file='AdminFooter'}

