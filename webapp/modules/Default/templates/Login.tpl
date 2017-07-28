{*
@package jp.co.dipps.carrot3
@subpackage Default
@author 小石達也 <tkoishi@b-shock.co.jp>
*}
{assign var='body.id' value='login_page'}
{include file='AdminHeader'}

{form}
  <h1>{const name='app_name_ja'}</h1>

  {include file='ErrorMessages'}

  <table>
    <tr>
      <th>メールアドレス</th>
      <td>
        <input type="email" name="email" value="{$email}" size="24" maxlength="64" class="english" autofocus>
      </td>
    </tr>
    <tr>
      <th>パスワード</th>
      <td>
        <input type="password" name="password" size="24" maxlength="64" class="english">
      </td>
    </tr>
    <tr>
      <td colspan="2" class="bottom">
        <input type="submit" value="ログイン">
      </td>
    </tr>
  </table>
{/form}

{include file='AdminFooter'}

