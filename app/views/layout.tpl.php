<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang='en' xml:lang='en'>
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />

		<title>Demo blog | <?= empty($page_title) ? '' : $page_title?></title>
		<style type="text/css">
			@import '/public/stylesheets/main.css?<?=rand(100,200)?>';
		</style>

		<?=$html->script('jquery/jquery-1.2.3.js')?>
		<?=$html->script('init.js?'.rand(100,200))?>

	</head>
	<body>
		<div id="wraper">
			<h1>The Demo Blog application</h1>
			<ul id="navigation">
			<li><?=$html->link_to('List',array('controller' => 'blogs',
				'action' => 'list'));?></li>
			<li><?=$html->link_to('Create',array('controller' => 'blogs',
				'action' => 'create'));?></li>
			</ul>
			<div id="content">
				<div id="debug" style="display:<?= empty($message) ? 'none' : 'block'?>">
					<div>
						<?=(isset($message)) ? $message : ''?>
					</div>
				</div>

				<?php $this->yield();?>
			</div>
		</div>
	</body>
</html>
