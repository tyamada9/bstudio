<?php
/*
 * B-frame : php web application framework
 * Copyright (c) BigBeat Inc. all rights reserved. (http://www.bigbeat.co.jp)
 *
 * Licensed under the GPL, LGPL and MPL Open Source licenses.
*/
	// -------------------------------------------------------------------------
	// class B_DBaccess
	// 
	// -------------------------------------------------------------------------
	class B_DBaccess {
		function __construct($log) {
			$this->log = $log;
			$this->log_switch = false;
		}

		function connect($db_server, $db_user, $db_password, $charset) {
			$this->db_server = $db_server;
			$this->db_user = $db_user;
			$this->db_password = $db_password;
			$this->charset = $charset;

			$this->db = mysql_connect($db_server, $db_user, $db_password);
			if(!$this->db) {
				$this->log->write("DB CONNECT ERROR:[$db_server $db_user $db_password]");
				$this->is_connect = false;
				return false;
			}
			$ret = $this->set_charset($charset);
			if(!$ret) {
				$this->log->write("CHARSET ERROR:[$charset]");
				$this->is_connect = false;
				return false;
			}
			$this->is_connect = true;
			return true;
		}

		function select_db($db_name) {
			$this->db_name = $db_name;

			if(!$this->is_connect) {
				return false;
			}
			$db_selected = @mysql_select_db($db_name, $this->db);
			if(!$db_selected) {
				$this->log->write('DB ERROR no:' . mysql_errno($this->db) . ' message:' . mysql_error($this->db));
				return false;
			}
			return true;
		}

		function query($sql) {
			if($this->log_switch || B_ARCHIVE_LOG_MODE == 'DEBUG') {
				$this->log->write_archive_log($sql);
			}
			$rs = @mysql_query($sql);
			if(!$rs) {
				$this->log->write($this->getErrorMsg());
			}
			return $rs;
		}

		function iquery($sql) {
			if($this->log_switch || B_ARCHIVE_LOG_MODE == 'DEBUG') {
				$this->log->write_archive_log($sql);
			}
			$rs = @mysql_query($sql);
			if(!$rs) {
				$this->log->write($this->getErrorMsg());
				throw new Exception($this->getErrorMsg());
			}
			return $rs;
		}

		function getErrorNo() {
			return @mysql_errno($this->db);
		}

		function getErrorMsg() {
			return 'DB ERROR no:'. mysql_errno($this->db) . ' message:' . mysql_error($this->db);
		}

		function fetch_assoc($rs) {
			return @mysql_fetch_assoc($rs);
		}

		function fetch_array($rs) {
			return @mysql_fetch_array($rs);
		}

		function fetch_row($rs) {
			return @mysql_fetch_row($rs);
		}

		function fetch_field($rs, $i) {
			return @mysql_fetch_field($rs, $i);
		}

		function num_fields($rs) {
			return @mysql_num_fields($rs);
		}

		function num_rows($rs) {
			return @mysql_num_rows($rs);
		}

		function field_name($rs, $i) {
			return @mysql_field_name($rs, $i);
		}

		function begin() {
			$this->log_switch = true;
			$rs = $this->query("start transaction");
		}

		function commit() {
			$rs = $this->query("commit");
			$this->log_switch = false;
		}

		function rollback() {
			$rs = $this->query("rollback");
			$this->log_switch = false;
		}

		function set_charset($charset) {
			return $this->query("set names " . $charset); 
		}

		function real_escape_string($string) {
			return @mysql_real_escape_string($string);
		}

		function real_escape_string_for_like($string) {
			$ret = @mysql_real_escape_string($string);
			$ret = str_replace("%", "\%", $ret);
			$ret = str_replace("_", "\_", $ret);

			return $ret;
		}

		function dump($file_name) {
			if(!$this->is_connect) return false;

			$command = "mysqldump --opt -h %DB_SERVER% -u %DB_USER% -p%DB_PASSWORD% --database %DB_NAME% > %FILE_NAME%";
			$command = str_replace('%DB_SERVER%', $this->db_server, $command);
			$command = str_replace('%DB_USER%', $this->db_user, $command);
			$command = str_replace('%DB_PASSWORD%', $this->db_password, $command);
			$command = str_replace('%DB_NAME%', $this->db_name, $command);
			$command = str_replace('%FILE_NAME%', $file_name, $command);

			if(substr(PHP_OS, 0, 3) === 'WIN') {
				$command = 'start /B ' . $command;
			}

			if($this->log_switch || B_ARCHIVE_LOG_MODE == 'DEBUG') {
				$this->log->write_archive_log($command);
			}

			$p = popen($command, 'r');
			$read = fread($p, 2096);
			if(!$p) {
				$this->log->write('dump command pipe open error');
				unlink($status_file_path);
			}
            pclose($p);

			return true;
		}

		function backupTables($file_name, $install=null, $tables=null, $views=null) {
			try {
				if(!$this->is_connect) return false;

				$this->begin();

				$rs = $this->iquery("select version()");
				$row = $this->fetch_row($rs);
				$version = $row[0];

				$result = "-- B-studio dump\n";
				$result.= "--\n";
				$result.= "-- ------------------------------------------------------\n";
				$result.= "-- Server version	$version\n\n";

				$schema = $this->db_name;
				$result.= "--\n";
				$result.= "-- Create schema $schema\n";
				$result.= "--\n\n";

				$result.= "-- CREATE DATABASE IF NOT EXISTS $schema;\n";
				$result.= "-- USE $schema;\n\n";

				// views
				if($views) {
					$views = is_array($views) ? $views : explode(',', $views);
				}
				else {
					$views = array();
					$sql = "show table status
							where name like '%PREFIX%%'
							and comment = 'VIEW'";
					$sql = str_replace('%PREFIX%', B_DB_PREFIX, $sql);
					$rs = $this->iquery($sql);
					while($row = $this->fetch_assoc($rs)) {
						$views[] = $row['Name'];
					}
				}

				// Temporary table structure for view
				foreach($views as $view) {
					$view_with_prefix = $view;
					if($install == 'install') {
						$view_with_prefix = preg_replace('/^' . B_PREFIX . '/', '%PREFIX%', $view);
					}
					$result.= "--\n";
					$result.= "-- Temporary table structure for view `$view_with_prefix`\n";
					$result.= "--\n";
					$result.= "DROP TABLE IF EXISTS `$view_with_prefix`;\n";
					$result.= "DROP VIEW IF EXISTS `$view_with_prefix`;\n";

					$fields = '';
					$rs = $this->iquery("SHOW COLUMNS FROM " . $view);
					while($row = $this->fetch_assoc($rs)) {
						$field = $row['Field'];
						$type = $row['Type'];
						if($fields) $fields.=",\n";
						$fields.= "  `$field` $type";
					}
					$result.= "CREATE TABLE `$view_with_prefix` (\n";
					$result.= $fields;
					$result.= "\n);\n\n";
				}

				// tables
				if($tables) {
					$tables = is_array($tables) ? $tables : explode(',', $tables);
				}
				else {
					$tables = array();
					$sql = "show table status
							where name like '%PREFIX%%'
							and comment <> 'VIEW'";
					$sql = str_replace('%PREFIX%', B_DB_PREFIX, $sql);
					$rs = $this->iquery($sql);
					while($row = $this->fetch_assoc($rs)) {
						$tables[] = $row['Name'];
					}
				}

				foreach($tables as $table) {
					$table_with_prefix = $table;
					if($install == 'install') {
						$table_with_prefix = preg_replace('/^' . B_PREFIX . '/', '%PREFIX%', $table);
					}
					$result.= "--\n";
					$result.= "-- Definition of table `$table_with_prefix`\n";
					$result.= "--\n\n";

					$row = $this->fetch_row($this->iquery('SHOW CREATE TABLE ' . $table));
					$result.= "DROP TABLE IF EXISTS `$table_with_prefix`;\n";
					$result.= $row[1] . ";\n\n";

					$result.= "--\n";
					$result.= "-- Dumping data for table `$table_with_prefix`\n";
					$result.= "--\n\n";

					$rs = $this->iquery("select * from " . $table);
					$fcnt = $this->num_fields($rs);

					$field = array();
					for($i=0, $fields='' ; $i<$fcnt ; $i++) {
						$field[$i] = $this->fetch_field($rs, $i);
						if($fields) $fields.= ',';
						$fields.= "`" . $field[$i]->name . "`";
					}
					$result.= "/*!40000 ALTER TABLE `$table_with_prefix` DISABLE KEYS */;\n";
					if($this->num_rows($rs)) {
						$result.= "INSERT INTO `$table_with_prefix` ($fields) VALUES \n";

						$fields = '';
						while($row = $this->fetch_assoc($rs)) {
							$field_value = '';
							$i=0;
							foreach($row as $key => $value) {
								$value = addslashes($value);
								$value = preg_replace("/\r\n/",'\r\n', $value);
								$value = preg_replace("/\n/",'\n', $value);

								if($field_value) $field_value.= ',';
								if(is_null($row[$key])) {
									$field_value.= 'NULL';
								}
								else if(strtolower($field[$i]->type) == 'int') {
									$field_value.= $value;
								}
								else {
									$field_value.= "'" . $value . "'";
								}
								$i++;
							}
							if($fields) $fields.= ",\n";
							$fields.= " ($field_value)";
						}
						$result.= $fields . ";\n";
					}
					$result.= "/*!40000 ALTER TABLE `$table_with_prefix` ENABLE KEYS */;\n\n\n";
				}

				// views
				foreach($views as $view) {
					$view_with_prefix = $view;
					if($install == 'install') {
						$view_with_prefix = preg_replace('/^' . B_PREFIX . '/', '%PREFIX%', $view);
					}
					$result.= "--\n";
					$result.= "-- Definition of view `$view_with_prefix`\n";
					$result.= "--\n\n";

					$row = $this->fetch_row($this->iquery('SHOW CREATE TABLE ' . $view));
					$result.= "DROP TABLE IF EXISTS `$view_with_prefix`;\n";
					$result.= "DROP VIEW IF EXISTS `$view_with_prefix`;\n";

					$string = preg_replace('/DEFINER=`\w*`@`[A-Za-z0-9_%.]*`/', '', $row[1]);
					$string = preg_replace('/ALGORITHM=\w*/', '', $string);
					$string = preg_replace('/ SQL SECURITY DEFINER/', '', $string);
					if($install == 'install') {
						$string = preg_replace('/' . B_DB_PREFIX . '/',  '%PREFIX%', $string);
					}

					$result.= $string . ";\n\n";
				}

				$fp = fopen($file_name, 'w+');
				fwrite($fp, $result);
				fclose($fp);

				$this->rollback();
			}

			catch(Exception $e) {
				return false;
			}

			return true;
		}

		function import($dump_file) {
			try {
				if(!file_exists($dump_file)) return false;
				if(!$this->is_connect) return false;

				$this->begin();

				$lines = file($dump_file);

				foreach($lines as $line) {
					// skip comment
					if(substr($line, 0, 2) == '--' || $line == '') {
						continue;
					}

					$sql.= str_replace('%PREFIX%', B_DB_PREFIX, $line);

					if(substr(trim($line), -1, 1) == ';') {
						$this->query($sql);
						$sql = '';
					}
				}
			}

			catch(Exception $e) {
				$this->rollback();
				return false;
			}
		}
	}
