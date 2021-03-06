<?php
/*
 * B-studio : Contents Management System
 * Copyright (c) BigBeat Inc. all rights reserved. (http://www.bigbeat.co.jp)
 *
 * Licensed under the GPL, LGPL and MPL Open Source licenses.
*/
$form_config = array(
	array('class' => 'B_Hidden', 'name' => 'baseHref', 'value' => B_SITE_BASE),
	array('class' => 'B_Hidden', 'name' => 'node_id'),
	array('class' => 'B_Hidden', 'name' => 'contents_id'),
	array('class' => 'B_Hidden', 'name' => 'update_datetime'),
	array(
		'start_html'	=> '<div class="editor_container bframe_adjustwindow" param="margin:8" >',
		'end_html'		=> '</div>',
		array(
			'start_html'	=> '<div id="html" class="text_editor bframe_adjustparent" param="margin:21">',
			'end_html'		=> '</div>',
			array(
				'name'				=> 'html1',
				'class'				=> 'B_TextArea',
				'special_html'		=> 'class="textarea bframe_adjustparent bframe_texteditor" param="margin:32" style="width:100%"',
				'no_trim'			=> true,
			),
			array(
				'id'				=> 'open_widgetmanager',
				'class'				=> 'B_Link',
				'link'				=> 'index.php',
				'value'				=> 'ウィジェット',
				'special_html'		=> 'title="ウィジェット" style="display:none"',
				'fixedparam'		=>
				array(
					'terminal_id'	=> TERMINAL_ID,
					'module'		=> 'widget', 
					'page'			=> 'select_tree',
					'target_id'		=> 'html1',
				),
			),
		),
		array(
			'start_html'	=> '<div id="visual" class="visual_editor bframe_adjustparent" param="margin:21" style="display:none">',
			'end_html'		=> '</div>',
			array(
				'start_html'	=> '<iframe id="inline_frame" name="inline_frame" class="bframe_adjustparent" frameborder="0" align="top" scrolling="auto" noresize="noresize" width="100%">',
				'end_html'		=> '</iframe>',
			),
		),
		array(
			'start_html'	=> '<div id="css" class="text_editor bframe_adjustparent" param="margin:21" style="display:none">',
			'end_html'		=> '</div>',
			array(
				'id'				=> 'css_editor',
				'name'				=> 'css',
				'class'				=> 'B_TextArea',
				'special_html'		=> 'class="textarea bframe_adjustparent bframe_texteditor" param="margin:32" syntax="css" style="width:100%"',
				'no_trim'			=> true,
			),
		),
		array(
			'start_html'	=> '<div id="php" class="text_editor bframe_adjustparent" param="margin:21" style="display:none">',
			'end_html'		=> '</div>',
			array(
				'id'				=> 'php_editor',
				'name'				=> 'php',
				'class'				=> 'B_TextArea',
				'special_html'		=> 'class="textarea bframe_adjustparent bframe_texteditor" param="margin:32" syntax="php" style="width:100%"',
				'no_trim'			=> true,
			),
		),
		array(
			'start_html'	=> '<div id="config" class="bframe_adjustparent" param="margin:25" style="display:none">',
			'end_html'		=> '</div>',
			array(
				'name'				=> 'config_form',
			),
		),
		array(
			'start_html'	=> '<div id="preview" class="bframe_adjustparent" param="margin:22" style="display:none;position:relative;">',
			'end_html'		=> '</div>',
			array(
				'start_html'	=> '<div id="preview_frame_container" class="bframe_adjustparent" style="margin: 0 auto;">',
				'end_html'		=> '</div>',
				array(
					'start_html'	=> '<iframe id="preview_frame" name="preview_frame" class="bframe_adjustparent" frameborder="0" align="top" scrolling="auto" noresize="noresize" style="width:100%">',
					'end_html'		=> '</iframe>',
				),
			),
			array(
				'start_html'	=> '<div class="bframe_emulator">',
				'end_html'		=> '</div>',
			),
		),
	),
);

//tab control
$tab_control_config = array(
	'start_html'	=> '<ul class="tabcontrol">',
	'end_html'		=> '</ul>',
	array(
		'name'				=> 'html_editor_index',
		'class'				=> 'B_Link',
		'start_html'		=> '<li>',
		'end_html'			=> '</li>',
		'link'				=> 'html',
		'special_html'		=> 'class="bframe_tab" onclick="bframe.inline.blur()"',
		'value'				=> 'HTML',
	),
	array(
		'name'				=> 'visual_editor_index',
		'class'				=> 'B_Link',
		'start_html'		=> '<li>',
		'end_html'			=> '</li>',
		'link'				=> 'visual',
		'special_html'		=> 'class="bframe_tab" onclick="bframe.inline.submit(\'F1\', \'' . B_SITE_BASE . 'index.php' . '\', \'inline\', \'inline_frame\'); return false;"',
		'value'				=> 'ビジュアル',
	),
	array(
		'name'				=> 'css_editor_index',
		'class'				=> 'B_Link',
		'start_html'		=> '<li>',
		'end_html'			=> '</li>',
		'link'				=> 'css',
		'value'				=> 'CSS',
		'special_html'		=> 'class="bframe_tab"',
	),
	array(
		'name'				=> 'php_editor_index',
		'class'				=> 'B_Link',
		'start_html'		=> '<li>',
		'end_html'			=> '</li>',
		'link'				=> 'php',
		'value'				=> 'PHP',
		'special_html'		=> 'class="bframe_tab"',
	),
	array(
		'name'				=> 'config_index',
		'class'				=> 'B_Link',
		'start_html'		=> '<li>',
		'end_html'			=> '</li>',
		'link'				=> 'config',
		'value'				=> '設定',
		'special_html'		=> 'class="bframe_tab"',
	),
	array(
		'name'				=> 'preview_index',
		'class'				=> 'B_Link',
		'start_html'		=> '<li>',
		'end_html'			=> '</li>',
		'link'				=> 'preview',
		'value'				=> 'プレビュー',
		'special_html'		=> 'class="bframe_tab" onclick="bframe.preview.submit(\'F1\', \'' . B_SITE_BASE . 'index.php' . '\', \'preview\', \'preview_frame\'); return false;"',
	),
	array(
		'name'				=> 'regist_button',
		'start_html'		=> '<li class="regist">',
		'end_html'			=> '</li>',
		array(
			'start_html'	=> '<div class="input-container">',
			'end_html'		=> '</div>',
			array(
				'name'			=> 'regist',
				'start_html'	=> '<span id="regist" class="regist-button" onclick="bframe.ajaxSubmit.submit(\'F1\', \'' . $this->module . '\', \'form\', \'regist\', \'confirm\', true)">',
				'end_html'		=> '</span>',
				'value'			=> '<img src="images/common/save.png" alt="保存" />保存',
			),
		),
		array(
			'start_html'	=> '<div class="message-container">',
			'end_html'		=> '</div>',
			array(
				'start_html'	=> '<span id="message">',
				'end_html'		=> '</span>',
			),
		),
	),
);

$config_form_config = array(
	array(
		// テーブル
		'start_html'	=> '<table class="form" border="0" cellspacing="0" cellpadding="0"><tbody>',
		'end_html'		=> '</tbody></table>',

		// タイトル
		array(
			'start_html'    => '<tr>',
			'end_html'	    => '</tr>',
			array(
				'start_html'			=> '<th>',
				'end_html'				=> '</th>',
				array(
					'value'				=> 'タイトル',
					'no_linefeed'		=> true,
				),
			),
			array(
				'start_html'			=> '<td>',
				'end_html'				=> '</td>',
				array(
					'name'					=> 'title',
					'class'					=> 'B_InputText',
					'special_html'			=> 'class="title bframe_textarea"',
					'no_trim'				=> true,
				),
			),
		),
		// テンプレート
		array(
			'start_html'    => '<tr>',
			'end_html'	    => '</tr>',
			array(
				'start_html'		=> '<th>',
				'end_html'			=> '</th>',
				'value'				=> 'テンプレート　　',
			),
			array(
				'start_html'		=> '<td>',
				'end_html'			=> '</td>',
				array(
					'name'				=> 'template_name',
					'class'				=> 'B_InputText',
					'special_html'		=> 'class="template ime-off" readonly="readonly"',
				),
				array(
					'class'				=> 'B_Hidden',
					'name'				=> 'template_id',
				),
				array(
					'name'				=> 'open_template',
					'class'				=> 'B_Link',
					'link'				=> 'index.php',
					'special_html'		=> 'title="テンプレート設定" class="settings-button" onclick="top.bframe.modalWindow.activate(this, window, \'template_id\'); return false;" params="width:350,height:400"',
					'fixedparam'		=>
					array(
						'terminal_id'		=> TERMINAL_ID,
						'module'			=> 'template', 
						'page'				=> 'select_tree',
					),
					'specialchars'		=> 'none',
					'value'				=> '<img alt="テンプレート" src="images/common/gear_gray.png" />',
				),
				array(
					'class'				=> 'B_Link',
					'link'				=> '#',
					'special_html'		=> 'title="クリア" class="clear-button" onclick="bstudio.clearText(\'template_name\', \'template_id\'); return false;" ',
					'specialchars'		=> 'none',
					'value'				=> '<img alt="クリア" src="images/common/clear_gray.png" />',
				),
			),
		),
		// breadcrumbs
		array(
			'start_html'    => '<tr>',
			'end_html'	    => '</tr>',
			array(
				'start_html'			=> '<th>',
				'end_html'				=> '</th>',
				array(
					'value'				=> 'breadcrumbs',
					'no_linefeed'		=> true,
				),
			),
			array(
				'start_html'			=> '<td>',
				'end_html'				=> '</td>',
				array(
					'name'					=> 'breadcrumbs',
					'class'					=> 'B_InputText',
					'special_html'			=> 'class="breadcrumbs bframe_textarea"" ',
					'no_trim'				=> true,
				),
			),
		),
		// keywords
		array(
			'start_html'    => '<tr>',
			'end_html'	    => '</tr>',
			array(
				'start_html'			=> '<th>',
				'end_html'				=> '</th>',
				'value'					=> 'keywords',
			),
			array(
				'start_html'			=> '<td>',
				'end_html'				=> '</td>',
				array(
					'name'					=> 'keywords',
					'class'					=> 'B_TextArea',
					'special_html'			=> 'class="keyword bframe_textarea"',
				),
			),
		),
		// description
		array(
			'start_html'    => '<tr>',
			'end_html'	    => '</tr>',
			array(
				'start_html'			=> '<th>',
				'end_html'				=> '</th>',
				'value'					=> 'description',
			),
			array(
				'start_html'			=> '<td>',
				'end_html'				=> '</td>',
				array(
					'name'					=> 'description',
					'class'					=> 'B_TextArea',
					'special_html'			=> 'class="description bframe_textarea"',
				),
			),
		),
		// 外部css
		array(
			'start_html'    => '<tr>',
			'end_html'	    => '</tr>',
			array(
				'start_html'			=> '<th>',
				'end_html'				=> '</th>',
				'value'					=> '外部css',
			),
			array(
				'start_html'			=> '<td>',
				'end_html'				=> '</td>',
				array(
					'name'					=> 'external_css',
					'class'					=> 'B_TextArea',
					'special_html'			=> 'class="external_css bframe_textarea"',
				),
			),
		),
		// 外部javascript
		array(
			'start_html'    => '<tr>',
			'end_html'	    => '</tr>',
			array(
				'start_html'			=> '<th>',
				'end_html'				=> '</th>',
				'value'					=> '外部javascript',
			),
			array(
				'start_html'			=> '<td>',
				'end_html'				=> '</td>',
				array(
					'name'					=> 'external_js',
					'class'					=> 'B_TextArea',
					'special_html'			=> 'class="external_js bframe_textarea"',
				),
			),
		),
		// ヘッダー要素
		array(
			'start_html'    => '<tr>',
			'end_html'	    => '</tr>',
			array(
				'start_html'			=> '<th>',
				'end_html'				=> '</th>',
				'value'					=> 'ヘッダー要素',
			),
			array(
				'start_html'			=> '<td>',
				'end_html'				=> '</td>',
				array(
					'class'					=> 'B_TextArea',
					'name'					=> 'header_element',
					'special_html'			=> 'class="header_element bframe_textarea"',
				),
			),
		),
	),
);
