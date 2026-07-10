<?
include_once("_common.php");
$basename = basename($_SERVER["PHP_SELF"]);
$sql = "select * from l_menu where lm_url like \"%{$basename}%\" ";
$row = sql_fetch($sql);

// 유료회원 기간 만료시 무료회원으로 전환
if($member["mb_id"]){
$sql = "select * 
from g5_member a, g5_member_etc b
where 1=1
and a.mb_id = b.mb_id
AND b.end_date < \"".date("Y-m-d")."\" 
AND a.mb_type != \"무료회원\"
and b.left_day = \"0\"
";
$result = sql_query($sql);
$inQ = "";
for($i=0; $row_expire = sql_fetch_array($result); $i++){
if($inQ){$inQ.=",";}
$inQ.= $row_expire["mb_id"];
$sql = "update g5_member set
mb_type = \"무료회원\"
, free_change = free_change+1
, free_pre_type = \"{$row_expire["mb_type"]}\"
where 1=1 
and mb_id = \"{$row_expire["mb_id"]}\"
";
sql_query($sql);
$sql = "update g5_member_etc set
start_date = \"\"
, end_date = \"\"
, num_mon = \"0\"
, num_tue = \"0\"
, num_wed = \"0\"
, num_thur = \"0\"
, num_fri = \"0\"
, num_sat = \"0\"
where 1=1 
and mb_id = \"{$row_expire["mb_id"]}\"
";
sql_query($sql);
}
}

if($member["mb_id"] == "admin"){
$sql = "select count(*) as cnt from g5_member where mb_id != \"admin\" and mb_hp != \"\" and mb_sms = \"발송\" ";
$row_sms = sql_fetch($sql);
$sms_cnt = $row_sms["cnt"];
}

include_once(G5_LADMIN_PATH."/head.sub.php");
?>
<body class="hold-transition sidebar-mini layout-fixed text-sm">
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-light navbar-primary">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
      </li>
    </ul>
<!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Messages Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" href="<?=G5_LADMIN_URL?>/logout.php">
          <i class="fas fa-sign-out-alt"></i>
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?=G5_LADMIN_URL?>" class="brand-link">
      <img src="<?=G5_LADMIN_URL?>/dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
           style="opacity: .8">
      <span class="brand-text font-weight-light"><?=$config["cf_title"]?> ADMIN <?=$if_connect?></span>
    </a>
    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column nav-flat nav-legacy text-sm" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
  <?
$menu1 = array();
$menu2 = array();
$sql_where = "";
if($member["mb_level"] < 10){
$sql_where .= " and lm_level like \"%|{$member["mb_level"]}|%\" ";
}
$sql = "select * from l_menu where 1=1 {$sql_where} and lm_cate2 = \"0\" and lm_use = \"1\" order by lm_order asc";
$result = sql_query($sql);
for($i=0; $row_menu = sql_fetch_array($result);$i++){
$menu1[$i]["lm_cate"] = $row_menu["lm_cate1"];
$menu1[$i]["lm_name"] = $row_menu["lm_name"];
$menu1[$i]["lm_url"] = $row_menu["lm_url"];
$sql2 = "select * from l_menu where 1=1 {$sql_where} and lm_cate1 = \"{$row_menu["lm_cate1"]}\" and lm_cate2 != \"0\" and lm_use = \"1\" order by lm_order asc";
$result2 = sql_query($sql2);
for($j=0; $row2 = sql_fetch_array($result2); $j++){
$menu2[$i][$j]["lm_cate2"] = $row2["lm_cate2"];
$menu2[$i][$j]["lm_name"] = $row2["lm_name"];
$menu2[$i][$j]["lm_url"] = $row2["lm_url"];
// 활성화를 위하여
$menu1[$i]["active"] .= $row2["lm_url"];
}
}
$request_uri = $_SERVER["REQUEST_URI"];
$tmp = explode("?",$request_uri);
$request_uri = $tmp[0];
//print_r($menu2);
  ?>
<?
for($i=0; $i < count($menu1); $i++){ 
$menu1_active = false;
if(strpos($menu1[$i]["active"], $request_uri) !== false) {  
$menu1_active = true;
}
?>
<li class="nav-item has-treeview <?if($menu1_active){?>menu-open<?}?>">
<a href="#" class="nav-link <?if($menu1_active){?>active<?}?>">
<p>
<?=$menu1[$i]["lm_name"]?>
<i class="right fas fa-angle-left"></i>
</p>
</a>
<?if(count($menu2[$i]) > 0) {?>
<ul class="nav nav-treeview">
<?for($j=0; $j < count($menu2[$i]); $j++){?>
<li class="nav-item">
<a href="<?=$menu2[$i][$j]["lm_url"]?>" class="nav-link <?if($request_uri == $menu2[$i][$j]["lm_url"]){?>active<?}?>">
<i class="far fa-circle nav-icon"></i>
<p><?=$menu2[$i][$j]["lm_name"]?></p>
</a>
</li>
<?}?>
</ul>
<?}?>
</li>
<?}?>
</ul>
</nav>
<!-- /.sidebar-menu -->
</div>
<!-- /.sidebar -->
</aside>
<?if($basename != "index.php"){?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
<!-- Content Header (Page header) -->
<section class="content-header">
<div class="container-fluid">
<div class="row mb-2">
<div class="col-sm-6">
<h1><?=$row["lm_name"]?></h1>
</div>
</div>
</div><!-- /.container-fluid -->
</section>
    <!-- Main content -->
<section class="content">
<div class="container-fluid">
<?}?>
