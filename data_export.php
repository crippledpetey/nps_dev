<!DOCTYPE html>
<html class="no-js" lang="en-US">
	<head>
		<meta charset="UTF-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>	Need Plubing Supplies Data Export</title>
	</head>
	<body>
		<h1>Data Export Service</h2>

		<?php
			
			// Connect to MSSQL
			$host = '208.123.219.74';
			$user = 'nps';
			$pass = 'b45912345';
			$link = mssql_connect($host, $user, $pass);

			if (!$link) {
			    die('Something went wrong while connecting to MSSQL');
			}

			$table_names = "SELECT * FROM [affiliatelog]";
			//$sys_info = "SELECT * FROM information_schema.tables";
			$test = mssql_query($table_names);			
			$tables = mssql_fetch_array( $test );
			var_dump( $tables );
			foreach( $tables as $key=>$table ){
				echo $key . ':' . $table . ' <br>';
			}
			 
		?>
		<footer></footer>
	</body>
</html>
