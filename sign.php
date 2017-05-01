<?php
if(isset($_POST["number"]) && isset($_POST["username"])){
	//为了安全起见，必须对接收到的数据进行过滤处理
	$number = $_POST["number"];
	$username = $_POST["username"];
	$do = $_POST['do'];
}


if ($do == 'isSign'){
	$time = time();
	$todayBegin = strtotime(date('Y-m-d')." 00:00:00"); 
	$todayEnd = strtotime(date('Y-m-d')." 23:59:59"); 
	$mysqli = new mysqli('localhost','root','131978','sign'); 
	if($mysqli -> connect_error){
		die("Conect Error".$mysqli->connect_error);
	}
	$checkSignSql = "SELECT scores FROM signInfo WHERE number = '$number' AND time < '$todayEnd' AND time > '$todayBegin'";
	$checkSignToday = $mysqli -> query($checkSignSql) ;
	$checkSignData = $checkSignToday -> fetch_row();
	$checkSignToday -> close();
	if($checkSignData == null){ //今天未签到
		$getScoreSql = "SELECT scores FROM signInfo WHERE number = '$number'"; 
		$executeRes = $mysqli -> query($getScoreSql);
		$returnScores = $executeRes -> fetch_row();
		$executeRes -> close();

		if($returnScores == null){//数据库无此人信息，从未签到过
			$returnData = array('isSign'=>0,'scores'=>0);
			echo json_encode($returnData);
		}else{//数据库有此人信息，返回已签到积分
			$returnData = array('isSign'=>0,'scores'=>$returnScores[0]);
			echo json_encode($returnData);
		}
	}else{
		$getScoreSql = "SELECT scores FROM signInfo WHERE number = '$number'"; 
		$executeRes = $mysqli -> query($getScoreSql);
		$returnScores = $executeRes -> fetch_row();
		$executeRes -> close();
		$returnData = array('isSign'=>1,'scores'=>$returnScores[0]);
		echo json_encode($returnData);
	}
}

if ($do =='sign'){
	//检查今天是否签到
	$time = time();
	$todayBegin = strtotime(date('Y-m-d')." 00:00:00"); 
	$todayEnd = strtotime(date('Y-m-d')." 23:59:59"); 
	$mysqli = new mysqli('localhost','root','131978','sign'); 
	//解决插入数据库的中文乱码问题
	$mysqli -> query("SET NAMES UTF8");
	//函数转义 SQL 语句中使用的字符串中的特殊字符，防止SQL注入攻击
	$number = mysql_real_escape_string($number);
	$username = mysql_real_escape_string($username);
	if($mysqli -> connect_error){
		die("Conect Error".$mysqli->connect_error);
	}
	$checkSignSql = "SELECT scores FROM signInfo WHERE number = '$number' AND time < '$todayEnd' AND time > '$todayBegin'";
	$checkSignToday = $mysqli -> query($checkSignSql) ;
	$checkSignData = $checkSignToday -> fetch_row();
	$checkSignToday -> close();
	if($checkSignData == null){ //今天未签到
		$getScoreSql = "SELECT scores FROM signInfo WHERE number = '$number'"; 
		$executeRes = $mysqli -> query($getScoreSql);
		$returnScores = $executeRes -> fetch_row();
		$executeRes -> close();

		//检查曾经是否签到过
		if ($returnScores == null){//从未签到
			$insertSql = "INSERT INTO signInfo (number,username,time,counts,scores) VALUES ('$number','$username','$time',1,1)"; 
			$insert = $mysqli -> query($insertSql);
			$returnData = array('counts'=>1,'scores'=>1);
			echo json_encode($returnData);
 		}else{ // 曾经签到过，检查昨天是否签到过 
			$yesterdayBegin = strtotime(date("Y-m-d",strtotime("-1 day"))." 00:00:00"); 
			$yesterdayEnd = strtotime(date("Y-m-d",strtotime("-1 day"))." 23:59:59"); 
			$checkContinuSql="SELECT scores FROM signInfo WHERE number = '$number' AND time < '$yesterdayEnd' AND time > '$yesterdayBegin'";
			$checkContinuYesterday = $mysqli ->query($checkContinuSql);
			$returnYesterdayData = $checkContinuYesterday -> fetch_row();
			$checkContinuYesterday -> close();
			if ($returnYesterdayData == null){ //昨天未签到　　　　　　　　　　
				$updateSignSql = "UPDATE signInfo SET time = '$time', counts = 1, scores = scores + 1 WHERE number = '$number'";
				$mysqli -> query($updateSignSql);
				$getScoreSql = "SELECT counts,scores FROM signInfo WHERE number = '$number'"; 
				$executeRes = $mysqli -> query($getScoreSql);
				$returnScores = $executeRes -> fetch_row();
				$executeRes -> close();
				$returnData = array('counts'=>$returnScores[0],'scores'=>$returnScores[1]);
				echo json_encode($returnData);
			}else{//昨天已签到
				if(date("w") == 1){ //当周一签到时将本周签到次数恢复为1
					$updateSignSql = "UPDATE signInfo SET time = '$time', counts = 1,scores = scores + 1 WHERE number = '$number'";
				}else{
					$getCountsSql = "SELECT counts FROM signInfo WHERE number = '$number'"; 
					$executeRes = $mysqli -> query($getCountsSql);
					$returnCounts = $executeRes -> fetch_row();
					$executeRes -> close();
					if($returnCounts[0] == 4){//连续第五天签到，获得5个奖励积分
						$updateSignSql = "UPDATE signInfo SET time = '$time', counts = counts + 1,scores = scores + 5 WHERE number = '$number'";
					}else{
						$updateSignSql = "UPDATE signInfo SET time = '$time', counts = counts + 1,scores = scores + 1 WHERE number = '$number'";
					}
				}
				$mysqli -> query($updateSignSql);
				$getScoreSql = "SELECT counts,scores FROM signInfo WHERE number = '$number'"; 
				$executeRes = $mysqli -> query($getScoreSql);
				$returnScores = $executeRes -> fetch_row();
				$executeRes -> close();
				$returnData = array('counts'=>$returnScores[0],'scores'=>$returnScores[1]);
				echo json_encode($returnData);
			}
		}
	}
}
?>