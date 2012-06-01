<?php
defined('C5_EXECUTE') or die('Access Denied.');

class DbbbbPackage extends Package {

	protected $pkgHandle = "dbbbb";
	protected $appVersionRequired = "5.5";
	protected $pkgVersion = "0.1";

	public function getPackageName() {
		return t('Dbbbb');
	}

	public function getPackageDescription() {
		return t('Check that sql. To enable set DBBBB_DEBUG_ENABLED to true in /config/site.php');
	}
	
	public function install() {
		$pkg = parent::install();

		$sql = <<<SQL
CREATE TABLE IF NOT EXISTS adodb_logsql (
	created datetime NOT NULL,
	sql0 varchar(250) NOT NULL,
	sql1 text NOT NULL,
	params text NOT NULL,
  tracer text NOT NULL,
	timer decimal(16,6) NOT NULL
);
SQL;

		$db = Loader::db();
		$db->Execute($sql);
	}

	public function uninstall() {
		$pkg = parent::uninstall();

		$db = Loader::db();
		$sql = 'DROP TABLE IF EXISTS adodb_logsql';
		$db->Execute($sql);
	}

	public function on_start() {
		if(
			!defined('DBBBB_DEBUG_ENABLED')
			||
			!DBBBB_DEBUG_ENABLED
		) {
			return true;
		}

		Database::setLogging(true);
		Events::extend('on_render_complete', 'DbbbbPackage', 'shutdown', __FILE__);
	}

	public static function shutdown() {

		$reset = <<<RESET
#dbbbb-sql-log html,#dbbbb-sql-log body,#dbbbb-sql-log div,#dbbbb-sql-log span,#dbbbb-sql-log applet,#dbbbb-sql-log object,#dbbbb-sql-log iframe,#dbbbb-sql-log h1,#dbbbb-sql-log h2,#dbbbb-sql-log h3,#dbbbb-sql-log h4,#dbbbb-sql-log h5,#dbbbb-sql-log h6,#dbbbb-sql-log p,#dbbbb-sql-log blockquote,#dbbbb-sql-log pre,#dbbbb-sql-log a,#dbbbb-sql-log abbr,#dbbbb-sql-log acronym,#dbbbb-sql-log address,#dbbbb-sql-log big,#dbbbb-sql-log cite,#dbbbb-sql-log code,#dbbbb-sql-log del,#dbbbb-sql-log dfn,#dbbbb-sql-log em,#dbbbb-sql-log img,#dbbbb-sql-log ins,#dbbbb-sql-log kbd,#dbbbb-sql-log q,#dbbbb-sql-log s,#dbbbb-sql-log samp,#dbbbb-sql-log small,#dbbbb-sql-log strike,#dbbbb-sql-log strong,#dbbbb-sql-log sub,#dbbbb-sql-log sup,#dbbbb-sql-log tt,#dbbbb-sql-log var,#dbbbb-sql-log b,#dbbbb-sql-log u,#dbbbb-sql-log i,#dbbbb-sql-log center,#dbbbb-sql-log dl,#dbbbb-sql-log dt,#dbbbb-sql-log dd,#dbbbb-sql-log ol,#dbbbb-sql-log ul,#dbbbb-sql-log li,#dbbbb-sql-log fieldset,#dbbbb-sql-log form,#dbbbb-sql-log label,#dbbbb-sql-log legend,#dbbbb-sql-log table,#dbbbb-sql-log caption,#dbbbb-sql-log tbody,#dbbbb-sql-log tfoot,#dbbbb-sql-log thead,#dbbbb-sql-log tr,#dbbbb-sql-log th,#dbbbb-sql-log td,#dbbbb-sql-log article,#dbbbb-sql-log aside,#dbbbb-sql-log canvas,#dbbbb-sql-log details,#dbbbb-sql-log embed,#dbbbb-sql-log figure,#dbbbb-sql-log figcaption,#dbbbb-sql-log footer,#dbbbb-sql-log header,#dbbbb-sql-log hgroup,#dbbbb-sql-log menu,#dbbbb-sql-log nav,#dbbbb-sql-log output,#dbbbb-sql-log ruby,#dbbbb-sql-log section,#dbbbb-sql-log summary,#dbbbb-sql-log time,#dbbbb-sql-log mark,#dbbbb-sql-log audio,#dbbbb-sql-log video{margin:0;padding:0;border:0;font-size:100%;font:inherit;vertical-align:baseline}#dbbbb-sql-log article,#dbbbb-sql-log aside,#dbbbb-sql-log details,#dbbbb-sql-log figcaption,#dbbbb-sql-log figure,#dbbbb-sql-log footer,#dbbbb-sql-log header,#dbbbb-sql-log hgroup,#dbbbb-sql-log menu,#dbbbb-sql-log nav,#dbbbb-sql-log section{display:block}body{line-height:1}#dbbbb-sql-log ol,#dbbbb-sql-log ul{list-style:none}#dbbbb-sql-log blockquote,#dbbbb-sql-log q{quotes:none}#dbbbb-sql-log blockquote:before,#dbbbb-sql-log blockquote:after,#dbbbb-sql-log q:before,#dbbbb-sql-log q:after{content:'';content:none}#dbbbb-sql-log table{border-collapse:collapse;#dbbbb-sql-log border-spacing:0}
RESET;

		$css = <<<CSS
#dbbbb-sql-log h3 {
	border-top: 1px solid #333;
	font-size: 22px;
	font-weight: bold;
	line-heigh: 1.5;
	padding-top: 4px;
}
#dbbbb-sql-log b {
	font-weight: bold;
}
#dbbbb-sql-log font {
	font-size: 12px !important;
}
#dbbbb-sql-log table {
	margin-top: 10px;
  border-bottom: 2px solid #333;
	margin-bottom: 10px;
}
#dbbbb-sql-log td {
	padding: 5px;
}
#dbbbb-sql-log tr:nth-child(odd) {
  background: #f0f0f0;
}
#dbbbb-sql-log tr:nt-child(odd) td:nth-child(odd) {
	background: #eee;
}
#dbbbb-sql-log tr:nth-child(even) {
	background: #ccc;
}
#dbbbb-sql-log tr:nth-child(even) td:nth-child(odd) {
	background: #ddd;
}
#dbbbb-sql-log tr:first-child {
	color: #eee;
	background: #333;
}
#dbbbb-sql-log tr:first-child td:nth-child(odd) {
  background: #444;
}
#dbbbb-sql-log {
	background: #fff;
	border-top: 2px solid #333;
	padding: 10px 15px 10px 15px;

}
CSS;
		echo '<style>'.$reset.$css.'</style>';
		Database::setLogging(false);
		$db = Loader::db();
		$perf = NewPerfMonitor($db);
		echo '<div id="dbbbb-sql-log">';
		echo $perf->SuspiciousSQL();
		echo $perf->ExpensiveSQL();
		echo '</div>';
		$db->Execute('TRUNCATE TABLE adodb_logsql');
	}

}
