<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang='en' xml:lang='en'>
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		<title>Demo | <?= empty($page_title) ? '' : $page_title?></title>

		<?=$html->load_style('main.css')?>
		<?=$html->load_script('jquery/jquery-1.2.3.js')?>
		<?=$html->load_script('init.js')?>

	</head>
	<body>
		<div id="wraper">
			<h1>Some headline</h1>
			<ul id="navigation">

				<li><?=$html->link_to('List',array('controller' => 'blogs',
					'action' => 'list'
				));?></li>

				<li><?=$html->link_to('Create',array('controller' => 'blogs',
					'action' => 'create'
				));?></li>
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
