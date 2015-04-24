<?php
require_once 'downloader/Maged/Controller.php';
define('MAGE_VERSION', Maged_Controller::getVersion());

/**
 * @global PDO the database connection used globally
 */
$DB = null;

/**
 * Create and open database connection object
 *
 * @param string $host Database host name
 * @param string $dbname Database name
 * @param string $user Database user
 * @param string $password Database password
 * @param string $port Database port
 * @param array $options extra parameters
 * @throws Exception when occurring error
 */
function open_db($host, $dbname, $user, $password, $port = "", $options = array()) {
	global $DB;
	
	// TODO assign the connection to global varriable $DB
	if($port != "")	{
		$host = $host.":".$port;
	}
		
	if(!$DB = @mysql_connect($host, $user, $password)) {
		throw new Exception("Can't connect to mysql: ".mysql_error());
	}
	if(!mysql_select_db ($dbname, $DB)) {
		throw new Exception("Can't select database: ".mysql_error());
	}
	
	//$DB = mysql_connect($host, $user, $pass) or die("aaaaaaa");
	//mysql_select_db ($dbname, $DB) or die("bbbbbbb");
}


/**
 * Create a static block
 *
 * @param string $title Block title
 * @param string $ident Block identifier
 * @param string $content Block content
 * @param int $store_id Store ID, if NULL, set to global store
 * @param boolean $overwrite indicate whether overwrite the existing page or not
 * @throws Exception 
 * - when block existed but $overwrite = false
 * - or store_id is not exist
 * - or database error
 * @return int Block ID
 */
function create_block($title, $ident, $store_id = null, $content, $overwrite = true) {	
	// TODO: Implementation
	global $prefix;
	if($store_id == null)
		$store_id = 0;
	
	// store_id is not exist
	$query = sprintf("SELECT * FROM %score_store WHERE store_id='%s'",$prefix,$store_id);
	$result = mysql_query($query);
	if(mysql_num_rows($result) == 0) {
		throw new Exception(sprintf("Create static block '%s': store_id not found!",$title));
	}
	else {
		// escape string
		$title = trim($title);
		$ident = trim($ident);
		$content = mysql_real_escape_string($content);
		
		// check block exist
		$query = sprintf("SELECT * FROM %scms_block a, %scms_block_store b WHERE a.block_id=b.block_id AND identifier='%s' AND store_id='%s'",$prefix,$prefix,$ident,$store_id);
		$result = mysql_query($query);
		
		if(mysql_num_rows($result) > 0)
		{
			if($overwrite == true)
			{
				// update block existed
				$query = sprintf("UPDATE %scms_block SET content='%s' WHERE identifier='%s'",$prefix,$content,$ident);
				$update = mysql_query($query);
				if(!$update)
					throw new Exception('Invalid query: ' . mysql_error());
				else
				{
					$row = mysql_fetch_assoc($result); 
					return $row['block_id'];
				}
			}
			else
				throw new Exception("Static block existed but overwrite = false.");
		}
		else
		{
			// insert new block
			$query = sprintf("INSERT INTO %scms_block(title,identifier,content) VALUES('%s','%s','%s')",$prefix,$title,$ident,$content);
			$insert = mysql_query($query);
			if(!$insert)
				throw new Exception('Invalid query: ' . mysql_error());
			$id = mysql_insert_id();
			$query = sprintf("INSERT INTO %scms_block_store(block_id,store_id) VALUES('%s','%s')",$prefix,$id,$store_id);
			$insert = mysql_query($query);
			if(!$insert)
				throw new Exception('Invalid query: ' . mysql_error());
			else
				return $id;
		}
	}
	
}


/**
 * Create a page
 *
 * @param string $title Page title
 * @param string $title Page URL Key
 * @param int $store_id Store ID, if NULL, set to global store
 * @param string $content Page content
 * @param string $layout Page layout: empty, one_column, two_columns_left, two_columns_right, three_columns, etc...
 * @param string $layout_xml Layout Update XML
 * @param boolean $overwrite indicate whether overwrite the existing page or not
 * @throws Exception 
 * - when page existed but $overwrite = false
 * - $layout_xml is invalid XML
 * - or store_id is not exist
 * - or database error
 * @return int Page ID
 */
function create_page($title, $key, $store_id = null, $content_heading = '', $content, $layout = 'one_column', $layout_xml = '', $overwrite = true) {
	// TODO: Implementation
	global $prefix;
	if($store_id == null)
		$store_id = 0;
	
	$flag = true;
	// check layout_xml
	if($layout_xml != '')
	{
		$layout_xml_tmp = "<root>".$layout_xml."</root>";
		if(@simplexml_load_string($layout_xml_tmp)===FALSE) {
			$flag = false;
			throw new Exception(sprintf("Create page '%s': layout_xml is invalid XML.",$title));
		}
	}	
	
	// store_id is not exist
	$query = sprintf("SELECT * FROM %score_store WHERE store_id='%s'",$prefix,$store_id);
	$result = mysql_query($query);
	if(mysql_num_rows($result) == 0) {
		$flag = false;
		throw new Exception(sprintf("Create page '%s': store_id not found!",$title));
	}
	
	if(flag == true)
	{
		// escape string
		$title = trim($title);
		$key = trim($key);
		$content_heading = trim($content_heading);
		$layout = trim($layout);
		$content = mysql_real_escape_string($content);
		
		// check page exist
		$query = sprintf("SELECT * FROM %scms_page a, %scms_page_store b WHERE a.page_id=b.page_id AND identifier='%s' AND store_id='%s'",$prefix,$prefix,$key,$store_id);
		$result = mysql_query($query);
		
		if(mysql_num_rows($result) > 0)
		{
			if($overwrite == true)
			{
				// update page existed
				$query = sprintf("UPDATE %scms_page SET content='%s',layout_update_xml='%s',root_template='%s',content_heading='%s' WHERE identifier='%s'",$prefix,$content, $layout_xml, $layout, $content_heading, $key);
				$update = mysql_query($query);
				if(!$update)
					throw new Exception('Invalid query: ' . mysql_error());
				else
				{
					$row = mysql_fetch_assoc($result); 
					return $row['page_id'];
				}
			}
			else
				throw new Exception("Page existed but overwrite = false.");
		}
		else
		{
			// insert new page
			$query = sprintf("INSERT INTO %scms_page(title,root_template,identifier,content_heading,content,layout_update_xml) VALUES('%s','%s','%s','%s','%s','%s')",$prefix,$title,$layout,$key,$content_heading,$content,$layout_xml);
			$insert = mysql_query($query);
			if(!$insert)
				throw new Exception('Invalid query: ' . mysql_error());
			$id = mysql_insert_id();
			$query = sprintf("INSERT INTO %scms_page_store(page_id,store_id) VALUES('%s','%s')",$prefix,$id,$store_id);
			$insert = mysql_query($query);
			if(!$insert)
				throw new Exception('Invalid query: ' . mysql_error());
			else
				return $id;
		}
	}
	
}


/**
 * Extract (unzip) a package (file) to a directory, recursively create sub-directories if it doesn't exist
 * 
 * @param string $package path to the zip file i.e. /path/to/theme-package.zip, 
 * if full path is not specified, it will look up in the current directory.
 * @param string $destination path to the target directory, if not specified, current directory is used.
 * @param boolean $overwrite whether overwrite existing files or not
 * @throws Exception 
 * - if $package or $destination not found
 * - I/O error
 */
function extract_theme($package, $destination = '.', $overwrite = true) {
	// TODO: Implementation
	
	$z = zip_open($package);
	if (is_resource($z)) { 
		while ($entry = zip_read($z)) {
			$entry_name = zip_entry_name($entry);
			// only proceed if the file is not 0 bytes long
			if (zip_entry_filesize($entry)) {
				$zipdir = dirname($entry_name);
				$zipname = substr($package,0,strrpos($package,"."));
				
				if(stripos($zipdir,$zipname)===0 ) {
					$zipdir = substr($zipdir,strlen($zipname)+1);
				}
				$dir = $destination."/".$zipdir;
				
				if (! is_dir($dir)) { 
					pc_mkdir_parents($dir); 
				}

				$file = basename($entry_name);

				if (zip_entry_open($z,$entry)) {
					if(!file_exists($dir.'/'.$file) || $overwrite == true){
						if ($fh = fopen($dir.'/'.$file,'w')) {
							// write the entire file
							
							fwrite($fh,zip_entry_read($entry,zip_entry_filesize($entry)))
								or error_log("can't write: ".$php_errormsg);
							
							fclose($fh) or error_log("can't close: ".$php_errormsg);
						} else {
							error_log("can't open ".$dir."/".$file.": ".$php_errormsg);
						}
					}
					zip_entry_close($entry);
				} else {
					error_log("can't open entry ".$entry_name.": ".$php_errormsg);
				}
			}
		}
		zip_close($z);
	}
	else {
		throw new Exception("Extract theme: package not found.");
	}
	
}


/**
 * Activate the theme on a store
 *
 * @param string $package the theme name. i.e. em0015
 * @param string $default sub-theme. i.e. 'NewStyle'. if given it will populate in Default field in Design configuration
 * @param int $store_id Store ID, if NULL, set to global store
 * @throws Exception 
 * - if theme does not exist, 
 * - sub-theme does not exist
 * - store does not exist
 * - database error
 */
function set_theme($package, $default = '', $store_id = null) {
	// TODO: Implementation
	global $prefix;
	if($store_id == null)
		$store_id = 0;
	
	// store_id is not exist
	$query = sprintf("SELECT * FROM %score_store WHERE store_id='%s'",$prefix,$store_id);
	$result = mysql_query($query);
	if(mysql_num_rows($result) == 0) {
		throw new Exception("Set theme: store_id not found!");
	}
	else {
		$scope = 'default';
		if($store_id != 0)
			$scope = 'stores';
		// set packege	
		$query = sprintf("SELECT * from %score_config_data WHERE path='design/package/name' and scope_id='%s'",$prefix,$store_id);
		$result = mysql_query($query);
		if(mysql_num_rows($result) > 0){
			$query = sprintf("UPDATE %score_config_data SET value='%s' WHERE path='design/package/name' and scope_id='%s'",$prefix,$package,$store_id);
			$update = mysql_query($query);
			if(!$update)
				throw new Exception('Invalid query: ' . mysql_error());
		}
		else {
			$query = sprintf("INSERT INTO %score_config_data(scope,scope_id,path,value) VALUES('%s','%s','design/package/name','%s')",$prefix,$scope,$store_id,$package);
			$insert = mysql_query($query);
			if(!$insert)
				throw new Exception('Invalid query: ' . mysql_error());
		}
		// set theme default
		$query = sprintf("SELECT * from %score_config_data WHERE path='design/theme/default' and scope_id='%s'",$prefix,$store_id);
		$result = mysql_query($query);
		if(mysql_num_rows($result) > 0){
			$query = sprintf("UPDATE %score_config_data SET value='%s' WHERE path='design/theme/default' and scope_id='%s'",$prefix,$default,$store_id);
			$update = mysql_query($query);
			if(!$update)
				throw new Exception('Invalid query: ' . mysql_error());
		}
		else {
			$query = sprintf("INSERT INTO %score_config_data(scope,scope_id,path,value) VALUES('%s','%s','design/theme/default','%s')",$prefix,$scope,$store_id,$default);
			$insert = mysql_query($query);
			if(!$insert)
				throw new Exception('Invalid query: ' . mysql_error());
		}
		
	}

}

/**
 * Install an extension
 *
 * @param string $extension_key the extension key (ei. magento-community/em_deleteorder)
 * @throws Exception when error occurs
 */
function install_extension($extension_key) {
	// TODO: Implementation
	if(file_exists('downloader/index.php'))	{
	?>
		<script type="text/javascript">
			Event.observe(window, 'load', function() {
				install_extension('<?php echo $extension_key ?>');
			});
		</script>
	<?php
	}
	else {
		throw new Exception("Can't install extension. Please run this file in the root directory of the project Magento.");
	}
}


/**
 * Login magento downloader
 * @param string $user
 * @param string $password
 */
function login($user, $password) {
	?>
	<script type="text/javascript">
		Event.observe(window, 'load', function() {
			document.body.innerHTML += '<form id="login" action="downloader/index.php" method="post" onsubmit="return false"></form>';
			$('login').request({
				parameters: {username:'<?php echo $user ?>',password:'<?php echo $password ?>'},
				onComplete: function() {
					window.mage_logged_in = true;
					$('output').innerHTML += '<div class="msg">Logged in Magento Downloader successfully</div>';
				}
			});
		});
	</script>
	<?php
}
/**
 * Flush all magento cache
 * @throws Exception when error occurs
 */
function flush_cache() {
	// TODO: Implementation
	
	$mage_filename = 'app/Mage.php';
	if(file_exists($mage_filename)) {
		require_once $mage_filename;
		umask(0);
		Mage::app('default');
		Mage::getConfig()->cleanCache();
	}
	else {
		throw new Exception("Can't flush magento cache. Please run this file in the root directory of the project Magento.");
	}
	
}

function install_start() {
	?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	
		<script type="text/javascript" src="downloader/js/prototype.js"></script>
		<script type="text/javascript">
		var mage_logged_in = false;
		var installing = false;
		var form_num = 1;
		
		function install_extension(key) {
			
			var timer = setInterval(function() {
				if (mage_logged_in && !installing) {
					clearInterval(timer);
					installing = true;
					
					var form = create_install_form();
					var namekey = key.substring(key.lastIndexOf('/'));
					var key15 = "http://connect20.magentocommerce.com/community/" + namekey;

					<?php if (MAGE_VERSION >= '1.5'): ?>
						form.elements['install_package_id'].value = key15;
					<?php else: ?>
						form.elements['install_package_id'].value = key;
					<?php endif; ?>

					form.submit();
				}
			}, 2000);
			
		}
		
		function onSuccess() {
			
		}
		
		function create_install_form() {
			var form_name = 'installform'+form_num;
			var frame_name = 'installframe'+form_num;
			form_num++;
			
			var div = document.createElement('div');
			div.setAttribute('class', 'msg');
			<?php if (MAGE_VERSION >= '1.5'): ?>
			div.innerHTML = ''
				+ '<form id="'+form_name+'" target="'+frame_name+'" action="downloader/index.php?A=connectInstallPackagePost" method="post">'
				+ '<input type="hidden" name="install_package_id" value="" />'
				+ '</form>'
				+ '<iframe id="'+frame_name+'" name="'+frame_name+'" width="100%" height="200" onload="installing=false"></iframe>';
			<?php else: ?>
			div.innerHTML = ''
				+ '<form id="'+form_name+'" target="'+frame_name+'" action="downloader/index.php?A=pearInstallPackagePost" method="post">'
				+ '<input type="hidden" name="install_package_id" value="" />'
				+ '</form>'
				+ '<iframe id="'+frame_name+'" name="'+frame_name+'" width="100%" height="200" onload="installing=false"></iframe>';
			<?php endif; ?>
			$('output').appendChild(div);
			return $(form_name);
		}
		</script>
		<style type="text/css">
			body { font:11px/1.35 "Lucida Console","Courier New",serif; /*background:#000; color:#2EC029;*/ }
			div.msg { padding:5px 3px;  }
			.exception { color:red; }
			iframe { border:0; }
			
		</style>
	</head>
	<body>
		<div id="output"></div>
		<input type="hidden" name="connect_iframe_scroll" id="connect_iframe_scroll" />
	<?php
}


function install_end() {
	?>
		</body>
	</html>
	<?php
}

function desc($msg) {
	?>
	<script type="text/javascript">
		Event.observe(window, 'load', function() {
			$('output').innerHTML += '<div class="msg"><?php echo htmlspecialchars($msg) ?></div>';
		});
	</script>
	<?php
}


#######################################################################################
## PRIVATE FUCNTION

function pc_mkdir_parents($d,$umask = 0777) {
    $dirs = array($d);
    $d = dirname($d);
    $last_dirname = '';
    while($last_dirname != $d) { 
        array_unshift($dirs,$d);
        $last_dirname = $d;
        $d = dirname($d);
    }

    foreach ($dirs as $dir) {
        if (! file_exists($dir)) {
            if (! mkdir($dir,$umask)) {
                error_log("Can't make directory: $dir");
                return false;
            }
        } elseif (! is_dir($dir)) {
            error_log("$dir is not a directory");
            return false;
        }
    }
    return true;
}


function exceptionHandler($exception) {

    // these are our templates
    $traceline = "#%s %s(%s): %s(%s)";
    $msg = "PHP Fatal error:  Uncaught exception '%s' with message '%s' in %s:%s\nStack trace:\n%s\n  thrown in %s on line %s\nPlease report this error to support@halothemes.com for further assist";

    // alter your trace as you please, here
    $trace = $exception->getTrace();
    foreach ($trace as $key => $stackPoint) {
        // I'm converting arguments to their type
        // (prevents passwords from ever getting logged as anything other than 'string')
        $trace[$key]['args'] = array_map('gettype', $trace[$key]['args']);
    }

    // build your tracelines
    $result = array();
    foreach ($trace as $key => $stackPoint) {
        $result[] = sprintf(
            $traceline,
            $key,
            $stackPoint['file'],
            $stackPoint['line'],
            $stackPoint['function'],
            implode(', ', $stackPoint['args'])
        );
    }
    // trace always ends with {main}
    $result[] = '#' . ++$key . ' {main}';

    // write tracelines into main template
    $msg = sprintf(
        $msg,
        get_class($exception),
        $exception->getMessage(),
        $exception->getFile(),
        $exception->getLine(),
        implode("\n", $result),
        $exception->getFile(),
        $exception->getLine()
    );

    // log or echo as you please
    echo "<pre class='exception'>".$msg."</pre>";

	install_end();
}



set_exception_handler('exceptionHandler');