<!doctype lang="en">
<head>
    <title></title>
</head>
<body>
    
<?php
include("../nocache.php");
?>

<link href="//cdn.jsdelivr.net/npm/mana-font@latest/css/mana.min.css" rel="stylesheet" type="text/css" />
<link href="card.css" rel="stylesheet" type="text/css" />

<?php
/*
//download the sql file
if(!file_exists("AllPrintings.sql"))
{
    file_put_contents("AllPrintings.sql", fopen("https://mtgjson.com/api/v5/AllPrintings.sql", 'r'));
}
*/

/*
//check the hash
$file_hash = file_get_contents('https://mtgjson.com/api/v5/AllPrintings.sql.sha256');
$our_hash = hash_file('sha256', 'AllPrintings.sql');
echo "original hash: ".$file_hash."<br/>our file hash: ".$our_hash;
*/

//connect to the database
$db = NULL;
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try
{
    $db = new PDO("mysql:host=$host;dbname=lerietay_mtg;charset=utf8mb4", "lerietay_mtg", "password", $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

//get a random card
function getRandCard($db)
{
    $q = $db->query("select * from cards order by rand()");
    return $q->fetch();
}

//get a card by name
function getCardByName($db, $name)
{
    $q = $db->prepare("select * from cards where name = ?");
    $q->execute([$name]);
    return $q->fetch();
}

//replace the curlies with css stuff
function replaceWithIcons($text)
{
    $regex = [
       '/{T}/' => '<i class="ms ms-tap"></i>',
       '/({1})/' => '<i class="ms ms-1 ms-cost"></i>',
       '/({2})/' => '<i class="ms ms-2 ms-cost"></i>',
       '/({3})/' => '<i class="ms ms-3 ms-cost"></i>',
       '/({4})/' => '<i class="ms ms-4 ms-cost"></i>',
       '/({5})/' => '<i class="ms ms-5 ms-cost"></i>'
    ];
    
    return preg_replace(array_keys($regex), array_values($regex), $text);
}

$card = getRandCard($db);

/*switch($card['colorIdentity'])
{
    case "R":
        $color = "#f00";
        $card_bg = "img/red.png";
    break;
    
    case "W":
        $color = "#fff";
        $card_bg = "img/white.png";
    break;
    case "B":
        $color = "silver";
        $card_bg = "img/black.png";
    break;
    case "G":
        $color = "#0f0";
        $card_bg = "img/green.png";
    break;
    
    case "U":
        $color = "#00f";
        $card_bg = "img/blue.png";
    break;
}*/

//die(var_dump(replaceWithIcons($card['originalText'])));
//echo "<p>".$card['name']. " - ".$card['originalText']."</p>";

preg_match('/cardKingdom\':\s\'(.+?)\'/', $card['purchaseUrls'], $match);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $match[1]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
$src = curl_exec($ch);
curl_close($ch);

preg_match('/notoggle\s"\shref[=]\"\"><img\sclass[=]\"\"\ssrc[=]\"(.+?)"\salt/', $src, $match);

echo '<img src="'.$match[1].'" />';
?>

<!--
<style>
#mana-icon
{
    background-color:<?=$color?> !important;
}

.card-container
{
    background-color:<?=$card['borderColor']?> !important;
}

.card-background
{
    background-image: url('<?=$card_bg?>');
}
</style>

<div class="card-container">
  <div class="card-background">
    <div class="card-frame">

      <div class="frame-header">
        <h1 class="name"><?=$card['name']?></h1>
        <i class="ms ms-<?=$card['colorIdentity']?>" id="mana-icon"></i>
      </div>

      <img class="frame-art" src="https://image.ibb.co/fqdLEn/nissa.jpg" alt="nissa art">

      <div class="frame-type-line">
        <h1 class="type"><?=$card['subtypes']?></h1>
        <img src="https://image.ibb.co/kzaLjn/OGW_R.png" id="set-icon" alt="OGW-icon">
      </div>

      <div class="frame-text-box">
        <p class="description ftb-inner-margin"><?=replaceWithIcons($card['text'])?></p>
        <p class="description"></p>
        <p class="flavour-text"><?=$card['flavorText']?></p>
      </div>

      <div class="frame-bottom-info inner-margin">
        <div class="fbi-left">
          <p>140/184 R</p>
        <p>OGW &#x2022; EN <?=$card['artist']?></p> 
        </div>

        <div class="fbi-center"></div>

        <div class="fbi-right">
          &#x99; &amp; &#169; 2016 Wizards of the Coast
        </div>
      </div>

    </div>
  </div>
</div>
-->

</body>
</html>