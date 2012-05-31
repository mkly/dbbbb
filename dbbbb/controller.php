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
		Events::extend('on_render_complete', 'Dbbb', 'shutdown', __FILE__);
	}
}

class Dbbb {

	public static function shutdown() {

		$reset = <<<RESET
html,body,div,span,applet,object,iframe,h1,h2,h3,h4,h5,h6,p,blockquote,pre,a,abbr,acronym,address,big,cite,code,del,dfn,em,img,ins,kbd,q,s,samp,small,strike,strong,sub,sup,tt,var,b,u,i,center,dl,dt,dd,ol,ul,li,fieldset,form,label,legend,table,caption,tbody,tfoot,thead,tr,th,td,article,aside,canvas,details,embed,figure,figcaption,footer,header,hgroup,menu,nav,output,ruby,section,summary,time,mark,audio,video{margin:0;padding:0;border:0;font-size:100%;font:inherit;vertical-align:baseline}article,aside,details,figcaption,figure,footer,header,hgroup,menu,nav,section{display:block}body{line-height:1}ol,ul{list-style:none}blockquote,q{quotes:none}blockquote:before,blockquote:after,q:before,q:after{content:'';content:none}table{border-collapse:collapse;border-spacing:0}
RESET;

		$css = <<<CSS
h3 {
	border-top: 1px solid #333;
	font-size: 22px;
	font-weight: bold;
	line-heigh: 1.5;
	padding-top: 4px;
}
b {
	font-weight: bold;
}
font {
	font-size: 12px !important;
}
table {
	margin-top: 10px;
  border-bottom: 2px solid #333;
	margin-bottom: 10px;
}
td {
	padding: 5px;
}
tr:nth-child(odd) {
  background: #f0f0f0;
}
tr:nt-child(odd) td:nth-child(odd) {
	background: #eee;
}
tr:nth-child(even) {
	background: #ccc;
}
tr:nth-child(even) td:nth-child(odd) {
	background: #ddd;
}
tr:first-child {
	color: #eee;
	background: #333;
}
tr:first-child td:nth-child(odd) {
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
