<?php
include(__DIR__ . '/webdata/init.inc.php');
if ($_GET['method'] == 'save_config') {
    setcookie('locale', $_POST['locale']);
}

if (array_key_exists('locale', $_COOKIE) and $_COOKIE['locale'] and $l = json_decode($_COOKIE['locale'])) {
    $cookie_locale = $l;
} else {
    $cookie_locale = new StdClass;
}

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>NewsHelper i18n Helper</title>
<script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.css">
</head>
<body>
<div class="container">
    <h1>NewHelper i18n Helper</h1>
    關於新聞小幫手翻譯，可參考:<a href="https://g0v.hackpad.com/v76DaDdfUDl">https://g0v.hackpad.com/v76DaDdfUDl</a>
    <form method="post" id="form">
        <table class="table">
            <tr>
                <th width="50%">
                    中文
                </th>
                <th width="50%">
                    英文
                </th>
            </tr>
            <?php foreach (I18nLib::getWords() as $w) { ?>
            <tr>
                <td><?= htmlspecialchars($w) ?></td>
                <td>
                    <input type="text"
                    data-word="<?= htmlspecialchars($w) ?>"
                    class="text-word"
                    placeholder="<?= htmlspecialchars(I18nLib::i18n($w, 'en')) ?>"
                    style="width: 100%"
                    value="<?= htmlspecialchars((property_exists($cookie_locale, 'en') and property_exists($cookie_locale->{'en'}, $w)) ? $cookie_locale->{'en'}->{$w} : '') ?>"
                    >
                </td>
            </tr>
            <?php } ?>
        </table>
        <button type="submit">儲存</button>
    </form>
<script>
$('#form').submit(function(e){
    e.preventDefault();
    var map = {en:{}};
    $('.text-word').each(function(){
        var dom = $(this);
        if (dom.val()) {
            map.en[dom.data('word')] = dom.val();
        }
    });
    $.post('i18n.php?method=save_config', 'locale=' + encodeURIComponent(JSON.stringify(map)), function(){
        alert('測試資料更新成功');
        document.location = document.location;
        
    });
});
</script>
</div>
</html>
