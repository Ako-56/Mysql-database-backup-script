<?php session_start();
	
    //ENTER THE RELEVANT INFO BELOW
    $mysqlUserName      = "root";
    $mysqlPassword      = "*********";
    $mysqlHostName      = "127.0.0.1:3306";
    $DbName             = "youth_portals";
    $backup_name        = "mybackup.sql";
    $tables             = array();

   //or add 5th parameter(array) of specific tables:    array("mytable1","mytable2","mytable3") for multiple tables

    Export_Database($mysqlHostName,$mysqlUserName,$mysqlPassword,$DbName,  $tables=false, $backup_name=false );

    function Export_Database($host,$user,$pass,$name,  $tables=false, $backup_name=false )
    {
        $mysqli = new mysqli($host,$user,$pass,$name);
        $mysqli->select_db($name);
        $mysqli->query("SET NAMES 'utf8'");
$head="-- MySQL Administrator dump 1.4
--
-- ------------------------------------------------------
-- Server version	5.1.73-community


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;


--
-- Create schema ".$name."
--

CREATE DATABASE IF NOT EXISTS ".$name.";
USE ".$name.";\n";

        $queryTables    = $mysqli->query('SHOW TABLES');
        while($row = $queryTables->fetch_row())
        {
            $target_tables[] = $row[0];
        }
        if($tables !== false)
        {
            $target_tables = array_intersect( $target_tables, $tables);
        }
        foreach($target_tables as $table)
        {
			$pitch = '';
            $result         =   $mysqli->query('SELECT * FROM '.$table);
            $fields_amount  =   $result->field_count;
            $rows_num=$mysqli->affected_rows;
            $res            =   $mysqli->query('SHOW CREATE TABLE '.$table);
            $TableMLine     =   $res->fetch_row();
            $content        = (!isset($content) ?  '' : $content) . "\n--\n-- Definition of table `".$table."`\n--
			\nDROP TABLE IF EXISTS `".$table."`;\n".$TableMLine[1].";\n";
			
			$content .= "\n--\n-- Dumping data for table `".$table."\n--
			\n/*!40000 ALTER TABLE `".$table."` DISABLE KEYS */;";
            for ($i = 0, $st_counter = 0; $i < $fields_amount;   $i++, $st_counter=0)
            {
                while($row = $result->fetch_row())
                { //when started (and every after 100 command cycle):
					while ($col = $result->fetch_field()) {
							$pitch .= "`" . $col->name . "`,";
						}
                    if ($st_counter%100 == 0 || $st_counter == 0 )
                    {
						$content .= "\nINSERT INTO `".$table."` (".rtrim($pitch,',').") VALUES "; 
                    }
                    $content .= "\n(";
                    for($j=0; $j<$fields_amount; $j++)
                    {
                        $row[$j] = str_replace("\n","\\n", addslashes($row[$j]) );
                        if (isset($row[$j]))
                        {
                            $content .= '"'.$row[$j].'"' ;
                        }
                        else
                        {
                            $content .= '""';
                        }
                        if ($j<($fields_amount-1))
                        {
                                $content.= ',';
                        }
                    }
                    $content .=")";
                    //every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
                    if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num)
                    {
                        $content .= ";";
                    }
                    else
                    {
                        $content .= ",";
                    }
                    $st_counter=$st_counter+1;
                }
            } $content .="\n/*!40000 ALTER TABLE `".$table."` ENABLE KEYS */;\n\n";
        }
		$tail ="\n\n
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
		";
		//$user = $_SESSION['username'];
		$user = $_GET['a'];
        $date = date("Y-m-d");
        $backup_name = $backup_name ? $backup_name : $name." $date.sql";
		$twrt = $head.$content.$tail;
		$file = fopen($backup_name,"w");
		fwrite($file,$twrt);
		fclose($file);
		$nameFile = $backup_name;
		$download_folder = $user.'/'.$nameFile;
		$zip = new ZipArchive();
			$fileconpress = $download_folder.".zip";

			$conpress = $zip->open($fileconpress, ZIPARCHIVE::CREATE);
			if ($conpress == true)
			{
				$alex = $zip->addFile($nameFile);
				if(!$alex){
					echo "shida";
				}
				$zip->close();
				 $url = 'https://mycloudbackup/ebackup/test.php?a='.$user;

				if (function_exists('curl_file_create')) {
					$fileContent = curl_file_create($fileconpress, 'image/png');
				} else {
					$fileContent = '@' . realpath($fileconpress);
				}

				$data = array('fileParam'=> $fileContent);

				$curl = curl_init($url);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($curl, CURLOPT_POST,true);
				curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				$result=curl_exec ($curl);
				curl_close ($curl);

				//print $result;
				//if running the script from cloud a redirect from localhost back to cloud
				//header('location:https://mycloudbackup/ebackup/home'); 
			}
			else echo " Oh No! Error";
		exit;
    }
?>
