<?php
/*
 * B-studio : Contents Management System
 * Copyright (c) BigBeat Inc. all rights reserved. (http://www.bigbeat.co.jp)
 *
 * Licensed under the GPL, LGPL and MPL Open Source licenses.
*/
	class index extends B_CommonModule {
		function __construct() {
			parent::__construct(__FILE__);
			global $admin_mode;

			if($admin_mode) {
				$this->node_view = B_WORKING_CONTENTS_NODE_VIEW;
				$this->contents_view = B_WORKING_CONTENTS_VIEW;
				$this->template_node_view = B_WORKING_TEMPLATE_NODE_VIEW;
				$this->template_view = B_WORKING_TEMPLATE_VIEW;
				$this->widget_node_view = B_WORKING_WIDGET_NODE_VIEW;
				$this->widget_view = B_WORKING_WIDGET_VIEW;

				if($this->post['method'] == 'preview' || $this->post['method'] == 'template_preview' || $this->post['method'] == 'widget_preview') {
					$this->view_mode = 'preview';
					return;
				}
				if($this->post['method'] == 'inline') {
					$this->view_mode = 'inline';
					return;
				}
			}
			else {
				$this->node_view = B_CURRENT_CONTENTS_NODE_VIEW;
				$this->contents_view = B_CURRENT_CONTENTS_VIEW;
				$this->template_node_view = B_CURRENT_TEMPLATE_NODE_VIEW;
				$this->template_view = B_CURRENT_TEMPLATE_VIEW;
				$this->widget_node_view = B_CURRENT_WIDGET_NODE_VIEW;
				$this->widget_view = B_CURRENT_WIDGET_VIEW;
			}

			$this->getFileInfo($this->file_name, $this->file_extension);

			switch($this->file_extension) {
			case'css':
				$this->getCSS();
				break;

			default:
				$this->getHtml();
				break;
			}
		}

		function getFileInfo(&$file_name, &$file_extension) {
			$file_name = basename($this->request['url']);
			$i = strrpos($file_name, '.');
			if($i) {
				$file_extension = substr($file_name, $i+1);
				$file_name = substr($file_name, 0, $i);
			}
		}

		function getHtml() {
			$this->url = preg_replace('?/{2,}?', '/', $this->request['url']);	// remove slashes in succession
			$this->url = preg_replace('?^/?', '', $this->url);					// remove first slash

			$url_array = explode('/', $this->url);
			$this->contents_node = $this->getContentsNode($url_array);

			if(!$this->contents_node) {
				unset($this->breadcrumbs);		// unset bread crumbs
				$url_array = explode('/', 'notfound.html');
				$this->contents_node = $this->getContentsNode($url_array);
				$this->http_status = '404';
			}
			if($this->url == 'notfound.html') {
				$this->http_status = '404';
			}

			if($this->contents_node) {
				$this->html = $this->createHTML();
				$this->setTemplateExternalCss();
				$this->setTemplateExternalScript();
				$this->setTemplateHeaderElement();
				$this->setCss($this->contents['external_css']);
				$this->setScript($this->contents['external_js']);
				$this->setHeaderElement($this->contents['header_element']);
				$this->setTemplateCssLink();

				if($this->contents['css']) {
					$this->html_header->appendProperty('css',
						'<link rel="stylesheet" href="C' . $this->contents_node['contents_id'] . '.css" type="text/css" media="all" />');
				}

				$this->setTitle(htmlspecialchars($this->contents['title'], ENT_QUOTES, B_CHARSET));

				if($this->contents['keywords']) {
					$this->html_header->appendMeta('keywords', $this->contents['keywords']);
				}

				if($this->contents['description']) {
					$this->html_header->appendMeta('description', $this->contents['description']);
				}

				$this->view_file = './view/view_index.php';
			}
			else {
				// Not Found
				$this->notFound();
				exit;
			}
		}

		function getContentsNode($url, $node='', $level=0) {

			// create bread crumbs
			$this->createBreadCrumbs($node, $level);

			if(count($url)) {
				if($node && $node['node_type'] == 'page') {
					// parmalink
					$param = implode('/', $url);
					$param_array = explode('?', $param);
					$_REQUEST['id'] = $param_array[0];
					return $node;
				}
			}
			else {
				if($node['node_type'] != 'folder') {
					// page found
					return $node;
				}
				else {
					// directory and index.html is not exists
					$path = B_CURRENT_ROOT . $this->url . '/';
					header("Location:$path");
					exit;
				}
			}

			$sql = "select * from %VIEW% where parent_node='%PARENT_NODE%' %NODE_NAME% order by node_name";
			$sql = str_replace('%VIEW%', B_DB_PREFIX . $this->node_view, $sql);
			if($node) {
				$sql = str_replace('%PARENT_NODE%', $this->db->real_escape_string($node['node_id']), $sql);
			}
			else {
				$sql = str_replace('%PARENT_NODE%', 'root', $sql);
			}

			$node_name = array_shift($url);

			if($node_name) {
				$sql = str_replace('%NODE_NAME%', "and node_name='" . $this->db->real_escape_string($node_name) . "'", $sql);
			}
			else {
				$sql = str_replace('%NODE_NAME%', "and node_name in ('index.html', 'index.php') ", $sql);
			}
			$rs = $this->db->query($sql);
			$row = $this->db->fetch_assoc($rs);

			if(!$row) {
				return;
			}

			return $this->getContentsNode($url, $row, $level+1);
		}

		function createBreadCrumbs($node, $level) {
			if(is_array($node) && ($node['node_name'] == 'index.html' || $node['node_name'] == 'index.htm')) {
				return;
			}

			$sql = "select b.*
					from %CONTENTS_NODE_VIEW% a
					left join %CONTENTS_VIEW% b
					on a.contents_id = b.contents_id
					where %NODE_CONDITION%
					%PAGE_CONDITION%";

			$sql = str_replace('%CONTENTS_NODE_VIEW%', B_DB_PREFIX . $this->node_view, $sql);
			$sql = str_replace('%CONTENTS_VIEW%', B_DB_PREFIX . $this->contents_view, $sql);

			if($node) {
				if($node['node_type'] == 'folder') {
					$sql = str_replace('%NODE_CONDITION%', "a.parent_node = '" . $this->db->real_escape_string($node['node_id']) . "'", $sql);
					$sql = str_replace('%PAGE_CONDITION%', " and a.node_name in ('index.html', 'index.htm')", $sql);
					$url_suffix = '/';
				}
				else {
					$sql = str_replace('%NODE_CONDITION%', "a.node_id = '" . $this->db->real_escape_string($node['node_id']) . "'", $sql);
					$sql = str_replace('%PAGE_CONDITION%', "", $sql);
				}
			}
			else {
				$sql = str_replace('%NODE_CONDITION%', "a.parent_node = 'root'", $sql);
				$sql = str_replace('%PAGE_CONDITION%', " and a.node_name in ('index.html', 'index.htm')", $sql);
				$url_suffix = '/';
			}

			$rs = $this->db->query($sql);
			$row = $this->db->fetch_assoc($rs);

			$this->breadcrumbs[$level]['value'] = $row['breadcrumbs'];

			if($level == 0) {
				$this->breadcrumbs[$level]['url'] = B_CURRENT_ROOT;
			}
			else {
				$this->breadcrumbs[$level]['url'] = $this->breadcrumbs[$level-1]['url'] . $node['node_name'] . $url_suffix;
			}
		}

		function createHTML() {
			//  get contents from DB
			$this->contents = $this->getContents($this->contents_node['contents_id'], $this->contents_view);
			$this->innerHTML = $this->contents['html1'];

			// get templates array
			$this->getTemplates($this->contents['template_id']);

			for($i=0 ; $i<count($this->templates) ; $i++) {
				$this->start_html = $this->templates[$i]['start_html'] . $this->start_html;
				$this->end_html.= $this->templates[$i]['end_html'];
			}
			return $this->start_html . $this->innerHTML . $this->end_html;
		}

		function getTemplates($template_node_id) {
			$sql_org = "select a.node_id
							  ,a.parent_node
							  ,a.node_type
							  ,a.node_class
							  ,a.contents_id
							  ,b.contents_date
							  ,b.start_html
							  ,b.end_html
							  ,b.css
							  ,b.php
							  ,b.external_css
							  ,b.external_js
							  ,b.header_element
						from %TEMPLATE_NODE_VIEW% a
						left join %TEMPLATE_VIEW%  b
						on a.contents_id = b.contents_id
						where a.node_id='%NODE_ID%'";

			$sql_org = str_replace('%TEMPLATE_NODE_VIEW%', B_DB_PREFIX . $this->template_node_view, $sql_org);
			$sql_org = str_replace('%TEMPLATE_VIEW%', B_DB_PREFIX . $this->template_view, $sql_org);

			for($i=0, $node_id = $template_node_id ; $node_id && $node_id != 'root' ; $node_id = $row['parent_node'], $i++) {
				$sql = str_replace('%NODE_ID%', $node_id, $sql_org);

				$rs = $this->db->query($sql);
				$row = $this->db->fetch_assoc($rs);

				$this->templates[] = $row;
			}
		}

		function setTemplateCssLink() {
			for($i=count($this->templates)-1 ; $i>=0 ; $i--) {
				if($this->templates[$i]['css']) {
					$this->html_header->appendProperty('css',
						'<link rel="stylesheet" href="T' . $this->templates[$i]['contents_id'] . '.css" type="text/css" media="all" />');
				}
			}
		}

		function setTemplateExternalCss() {
			for($i=count($this->templates)-1 ; $i>=0 ; $i--) {
				$this->setCss($this->templates[$i]['external_css']);
			}
		}

		function setCss($css) {
			if(!$css) return;
			$this->html_header->appendProperty('css', $css);
		}

		function setTemplateExternalScript() {
			for($i=count($this->templates)-1 ; $i>=0 ; $i--) {
				$this->setScript($this->templates[$i]['external_js']);
			}
		}

		function setScript($script) {
			if(!$script) return;
			$this->html_header->appendProperty('script', $script);
		}

		function setTemplateHeaderElement() {
			for($i=count($this->templates)-1 ; $i>=0 ; $i--) {
				$this->setHeaderElement($this->templates[$i]['header_element']);
			}
		}

		function setHeaderElement($element) {
			$element_array = explode("\n", $element);
			if(is_array($element_array)) {
				foreach($element_array as $value) {
					$value = rtrim($value);
					if($value != '') {
						$this->html_header->appendProperty('misc', $value);
					}
				}
			}
		}

		function getContents($contents_id, $table) {
			$sql = "select * from " . B_DB_PREFIX . $table . " where contents_id='" . $this->db->real_escape_string($contents_id) . "'";
			$rs = $this->db->query($sql);
			$contents = $this->db->fetch_assoc($rs);

			return $contents;
		}

		function getCSS() {
			$prefix = substr($this->file_name, 0, 1);
			$file_name = substr($this->file_name, 1);

			switch($prefix) {
			case 'C':
				$row = $this->getContents($file_name, $this->contents_view);
				break;

			case 'T':
				$row = $this->getContents($file_name, $this->template_view);
				break;

			case 'W':
				$row = $this->getContents($file_name, $this->widget_view);
				break;

			default:
				header("HTTP/1.0 404 Not Found");
				break;
			}

			if($row['css']) {
				header('Content-Type: text/css; charset=' . B_CHARSET);
				echo $row['css'];
			}
			exit;
		}

		function inline() {
			global $admin_mode;
			if(!$admin_mode) return;

			$this->contents = $this->post;

			$this->getContentsNodeForPreview($this->contents['node_id']);

			for($i=count($this->contents_node), $level=0 ; $i>=0 ; $i--, $level++) {
				$this->createBreadCrumbs($this->contents_node[$i], $level);
			}

			$this->getTemplates($this->contents['template_id']);

			for($i=0 ; $i<count($this->templates) ; $i++) {
				$this->start_html = $this->templates[$i]['start_html'] . $this->start_html;
				$this->end_html.= $this->templates[$i]['end_html'];
			}

			$this->innerHTML = '<div id="inline_editor" class="bframe_inlineeditor" bframe_inlineeditor_param="filebrowser:bs-admin/index.php?terminal_id=' . $this->post['terminal_id'] . '&amp;module=resource&amp;page=popup" style="outline:none">' . $this->contents['html1'] . '</div>';
			$this->setTemplateExternalCss();
			$this->setTemplateCssLink();
			$this->setCss($this->contents['external_css']);

			if($this->contents['css']) {
				$this->html_header->appendProperty('css', '<style>' . $this->contents['css'] . '</style>');
			}

			$serializedString = file_get_contents(B_FILE_INFO_W);
			$info = unserialize($serializedString);
			if($info[B_CONTENTS_INLINE_TEMPLATES]) {
				$this->html_header->appendMeta('visual_editor_templates', B_CONTENTS_INLINE_TEMPLATES);
			}

			$this->html_header->appendProperty('script', '<script src="' . B_ADMIN_ROOT . 'js/bframe.js" type="text/javascript"></script>');
			$this->html_header->appendProperty('script', '<script src="' . B_ADMIN_ROOT . 'js/ckeditor/ckeditor.js" type="text/javascript"></script>');
			$this->html_header->appendProperty('script', '<script src="' . B_ADMIN_ROOT . 'js/bframe_inlineeditor.js" type="text/javascript"></script>');

			$this->setTitle(htmlspecialchars($this->contents['title'], ENT_QUOTES, B_CHARSET));

			if($this->contents['keywords']) {
				$this->html_header->appendMeta('keywords', $this->contents['keywords']);
			}

			if($this->contents['description']) {
				$this->html_header->appendMeta('description', $this->contents['description']);
			}

			$this->view_file = './view/view_inline.php';

			ini_set('display_errors','On');
		}

		function preview() {
			global $admin_mode;
			if(!$admin_mode) return;

			$this->contents = $this->post;

			$this->getContentsNodeForPreview($this->contents['node_id']);

			for($i=count($this->contents_node), $level=0 ; $i>=0 ; $i--, $level++) {
				$this->createBreadCrumbs($this->contents_node[$i], $level);
			}

			$this->getTemplates($this->contents['template_id']);

			for($i=0 ; $i<count($this->templates) ; $i++) {
				$this->start_html = $this->templates[$i]['start_html'] . $this->start_html;
				$this->end_html.= $this->templates[$i]['end_html'];
			}

			$this->innerHTML = $this->contents['html1'];
			$this->setTemplateExternalScript();
			$this->setTemplateExternalCss();
			$this->setTemplateHeaderElement();
			$this->setTemplateCssLink();
			$this->setScript($this->contents['external_js']);
			$this->setCss($this->contents['external_css']);

			if($this->contents['css']) {
				$this->html_header->appendProperty('css', '<style>' . $this->contents['css'] . '</style>');
			}

			$this->setTitle(htmlspecialchars($this->contents['title'], ENT_QUOTES, B_CHARSET));

			if($this->contents['keywords']) {
				$this->html_header->appendMeta('keywords', $this->contents['keywords']);
			}

			if($this->contents['description']) {
				$this->html_header->appendMeta('description', $this->contents['description']);
			}

			$this->html_header->appendProperty('script', '<script src="bs-admin/js/bframe.js" type="text/javascript"></script>');
			$this->html_header->appendProperty('script', '<script src="bs-admin/js/bframe_device_emulator.js" type="text/javascript"></script>');
			$this->view_file = './view/view_index.php';

			ini_set('display_errors','On');
		}

		function getContentsNodeForPreview($contents_node_id) {
			$sql_org = "select a.node_id
							  ,a.parent_node
							  ,a.node_name
							  ,a.node_type
							  ,a.node_class
							  ,a.contents_id
						from %CONETNTS_NODE_VIEW% a
						where a.node_id='%NODE_ID%'";

			$sql_org = str_replace('%CONETNTS_NODE_VIEW%', B_DB_PREFIX . $this->node_view, $sql_org);

			for($i=0, $node_id = $this->db->real_escape_string($contents_node_id) ; $node_id && $node_id != 'root' ; $node_id = $row['parent_node'], $i++) {
				$sql = str_replace('%NODE_ID%', $node_id, $sql_org);

				$rs = $this->db->query($sql);
				$row = $this->db->fetch_assoc($rs);

				$this->contents_node[] = $row;
				if($row['node_type'] == 'folder') {
					$this->path = $row['node_name'] . '/' . $this->path;
				}
				else {
					$this->path = $row['node_name'];
				}
			}
			$this->url = $this->path;
		}

		function template_preview() {
			global $admin_mode;
			if(!$admin_mode) return;

			$this->contents = $this->post;

			$sql = "select parent_node from %TEMPLATE_NODE_VIEW% where node_id = '%NODE_ID%'";
			$sql = str_replace('%TEMPLATE_NODE_VIEW%', B_DB_PREFIX . $this->template_node_view, $sql);
			$sql = str_replace('%NODE_ID%', $this->db->real_escape_string($this->post['node_id']), $sql);
			$rs = $this->db->query($sql);
			$row = $this->db->fetch_assoc($rs);

			$this->getTemplates($row['parent_node']);

			for($i=0 ; $i<count($this->templates) ; $i++) {
				$start_html = $this->templates[$i]['start_html'] . $start_html;
				$end_html.= $this->templates[$i]['end_html'];
			}

			$this->innerHTML = $start_html . $this->post['start_html'] . $this->post['end_html'] . $end_html;
			$this->setTemplateExternalScript();
			$this->setTemplateExternalCss();
			$this->setTemplateCssLink();
			$this->setScript($this->post['external_js']);
			$this->setCss($this->post['external_css']);

			if($this->post['css']) {
				$this->html_header->appendProperty('css', '<style>' . $this->post['css'] . '</style>');
			}

			$this->view_file = './view/view_index.php';

			ini_set('display_errors','On');
		}

		function widget_preview() {
			global $admin_mode;
			if(!$admin_mode) return;

			$this->contents = $this->post;
			$this->innerHTML = $this->post['html'];

			if($this->post['css']) {
				$this->html_header->appendProperty('css', '<style>' . $this->post['css'] . '</style>');
			}

			$this->view_file = './view/view_index.php';

			ini_set('display_errors','On');
		}

		function widget($node_id, $args) {
			$node_id = $this->db->real_escape_string($node_id);

			$sql = "select a.node_id
						  ,a.contents_id
						  ,b.html
						  ,b.css
						  ,b.php
					from %WIDGET_NODE_VIEW% a
						,%WIDGET_VIEW%  b
					where a.contents_id = b.contents_id and a.node_id='$node_id'";

			$sql = str_replace('%WIDGET_NODE_VIEW%', B_DB_PREFIX . $this->widget_node_view, $sql);
			$sql = str_replace('%WIDGET_VIEW%', B_DB_PREFIX . $this->widget_view, $sql);

			$rs = $this->db->query($sql);
			$__row = $this->db->fetch_assoc($rs);

			if($__row['css']) {
				$this->html_header->appendProperty('css',
					'<link rel="stylesheet" href="W' . $__row['contents_id'] . '.css" type="text/css" media="all" />');
			}

			widgetExec($this->view_mode, './view/view_widget.php', $__row, $this->breadcrumbs);
		}

		function view() {
			global $admin_mode;

			// start buffering
			ob_start();

			view($this->view_mode
				,$this->view_file
				,$this->templates
				,$this->contents
				,$this->start_html
				,$this->innerHTML
				,$this->end_html
				,$this->breadcrumbs
				,$this->url
				,$this->html_header);

			// get buffer
			$contents = ob_get_clean();

			// send HTTP header
			$this->sendHttpHeader();
			if($this->http_status == '404') {
				header("HTTP/1.1 404 Not Found");
			}
			else if($admin_mode) {
				header("X-XSS-Protection: 0");
			}

			// send HTML header
			$this->showHtmlHeader();

			// send HTML body
			echo $contents;
		}

		function notFound() {
			$this->sendHttpHeader();
			header("HTTP/1.1 404 Not Found");

			$this->showHtmlHeader();
			include('./view/view_not_found.php');
		}
	}

	function view($__view_mode
				 ,$__view_file
				 ,$__templates
				 ,$__contents
				 ,$__start_html
				 ,$__innerHTML
				 ,$__end_html
				 ,&$bs_breadcrumbs
				 ,&$bs_url
				 ,&$bs_html_header) {
		global $admin_mode;

		$bs_admin_mode = $admin_mode;
		$bs_view_mode = $__view_mode;
		$__archive = new B_Log(B_ARCHIVE_LOG_FILE);
		$bs_db = new B_DBaccess($__archive);
		$bs_db->connect(B_DB_SRV, B_DB_USR, B_DB_PWD, B_DB_CHARSET);
		$bs_db->select_db(B_DB_NME);

		include($__view_file);
		if($bs_view_mode == 'preview' && $bs_db->getErrorNo() != '0') {
			echo $bs_db->getErrorMsg();
		}
	}

	function widgetExec($__view_mode
					   ,$__view_file
					   ,$__row
					   ,&$bs_breadcrumbs) {
		global $admin_mode;

		$bs_admin_mode = $admin_mode;
		$bs_view_mode = $__view_mode;
		$__archive = new B_Log(B_ARCHIVE_LOG_FILE);
		$bs_db = new B_DBaccess($__archive);
		$bs_db->connect(B_DB_SRV, B_DB_USR, B_DB_PWD, B_DB_CHARSET);
		$bs_db->select_db(B_DB_NME);

		include($__view_file);
		if($__view_mode == 'preview' && $bs_db->getErrorNo() != '0') {
			echo $bs_db->getErrorMsg();
		}
	}

	function widget($node_id) {
		$args = func_get_args();
		$obj = $GLOBALS['current_obj'];
		$obj->widget($node_id, $args);
	}

	function notfound() {
		$_REQUEST['url'] = 'notfound.html';
		unset($_POST['method']);
		chdir(B_DOC_ROOT . B_CURRENT_ROOT);
		require('./bs-controller/controller.php');
		exit;
	}
