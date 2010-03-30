<?php
    $feed = $this->getVariable("feed");
?>
<h2> <?php print $feed["title"]; ?></h2>
<p> <?php print $feed["description"]; ?></p>
<?php
    if (isset($feed["image"])) {
        $url = $feed["image"]["url"];
        $title = $feed["image"]["title"];
        $link = $feed["image"]["link"];
?>
	<a href="<?php print $link;?>" title="<?php print $title; ?>">
		<img src="<?php print $url; ?>" alt="<?php print $title; ?>" style="float: none;"/>
	</a>
<?php }?>
<br/>
<br/>
<hr/>
<?php if (count($feed["items"])>0) {
    foreach ($feed["items"] as $item) {?>

		<h3> <a href="<?php print $item["link"]; ?>" title="<?php print $item["title"]; ?>"><?php print $item["title"]; ?></a> </h3>
		<br/>
		<p> <?php print $item["description"]; ?></p>
		<br/>
		<?php if (isset($item["pubDate"]) && (!empty($item["pubDate"]))) { ?>
		<p>Publicado el <?php print $item["pubDate"];?></p>
		<?php }?>
		<hr/>

    <?php }


}?>
