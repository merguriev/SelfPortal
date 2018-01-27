<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="">
	<meta name="author" content="">

	<title>SELFPORTAL</title>

	<!-- Bootstrap Core CSS -->
	<link href="/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.12/css/jquery.dataTables.min.css">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css" rel="stylesheet">

	<!-- MetisMenu CSS -->
	<link href="/css/metisMenu.min.css" rel="stylesheet">

	<!-- Timeline CSS -->
	<link href="/css/timeline.css" rel="stylesheet">

	<!-- Custom CSS -->
	<link href="/css/startmin.css" rel="stylesheet">

	<!-- Morris Charts CSS -->
	<link href="/css/morris.css" rel="stylesheet">

	<!-- Custom Fonts -->
	<link href="/css/font-awesome.min.css" rel="stylesheet" type="text/css">

	<link href="/css/custom-style.css" rel="stylesheet">

	<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->


	<!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>

    <![endif]-->
	<!-- jQuery -->
	<script src="https://code.jquery.com/jquery-1.12.3.js"></script>
	<script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.js"></script>
	<script src="/js/dataTables/dataTables.bootstrap.min.js"></script>
	<script src="/js/app.js"></script>
	<!-- Bootstrap Core JavaScript -->
	<script src="/js/bootstrap.min.js"></script>
	<script src="https://momentjs.com/downloads/moment-with-locales.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/js/bootstrap-datetimepicker.min.js"></script>

</head>

<body>
	<?php
    ini_set('session.cookie_httponly', '1');
    session_start();
if(!isset($_SESSION['user'])) die(header("Location: /index.php"));
    include_once $_SERVER['DOCUMENT_ROOT'].'/config/config.php';
?>

		<div class="modal fade" id="VMinfomodal" tabindex="-1" role="dialog" aria-labelledby="VMinfoModal" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h3 class="modal-title" id="myModalLabel">Virtual machine detailed info</h3>
					</div>
					<!-- /.modal-header -->

					<div id="VMinfomodalbody" class="modal-body">

						<!-- /.form-group -->
					</div>
					<!-- /.modal-body -->

					<div class="modal-footer form-row">
						<div class="col-sm-12 container" style="padding: 0px">
							<button class="form-control btn btn-danger" type="reset" data-dismiss="modal">Close</button>
						</div>
					</div>
					<!-- /.modal-footer -->
				</div>
				<!-- /.modal-content -->
			</div>
			<!-- /.modal-dialog -->
		</div>
		<!-- /.modal -->

		<div class="modal fade" id="domainsModal" tabindex="-1" role="dialog" aria-labelledby="DomainsModal" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<form role="form" class="form_mod form_error" data-type="edit" id="domains_edit_modal">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
							<h3 class="modal-title" id="myModalLabel">Edit domains entry</h3>
						</div>
						<!-- /.modal-header -->

						<div class="modal-body">
							<div class="form-group row">
								<div class="col-sm-8"><input type="text" required class="form-control" id="domains_edit_modal_input" name="name" data-validator-name="(?:[a-z][a-z0-9_]*)(\.)(?:[a-z][a-z0-9_]{1,})" minlength="4" placeholder="Domain name"></div>
								<div class="form-check col-sm-1">
									<input class="form-check-input big-checkbox" id="domains_edit_modal_checkbox" name="checkbox" type="checkbox">
								</div>
								<div class="col-sm-3">
									<H4><label>Publish</label></H4>
								</div>
							</div>
							<div class="row"><span style="color:red; display:none" id="domains_edit_modal_input_help" class="help-inline">Wrong input type! Make sure you have a dot and at least one letter before and two after it.</span></div>
							<!-- /.form-group -->
						</div>
						<!-- /.modal-body -->

						<div class="modal-footer form-row">
							<div class="col-sm-12 container" style="padding: 0px 0px 10px 0px">
								<button type="submit" class="form-control btn btn-primary disabled">Ok</button>
							</div>
							<div class="col-sm-12 container" style="padding: 0px">
								<button class="form-control btn btn-danger" type="reset" data-dismiss="modal">Close</button>
							</div>
						</div>
						<!-- /.modal-footer -->
					</form>
				</div>
				<!-- /.modal-content -->
			</div>
			<!-- /.modal-dialog -->
		</div>
		<!-- /.modal -->

		<div class="modal fade" id="siteModal" tabindex="-1" role="dialog" aria-labelledby="SiteModal" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<form role="form" class="form_mod form_error" data-type="add" id="site_edit_modal">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
							<h3 class="modal-title" id="myModalLabel">Add proxy site</h3>
						</div>
						<!-- /.modal-header -->

						<div class="modal-body">

							<h4>Name:</h4>
							<div class="form-group row">
								<div class="col-sm-12"><input type="text" required class="form-control form-name-check" name="name" id="site_edit_modal_name" placeholder="Domain name can contain only letters and numbers" data-validator-name="^([A-Za-z0-9])+[A-Za-z0-9]$" placeholder="Domain name"></div>
							</div>
							<div class="container"><span style="color:red; display:none" id="site_edit_modal_name_help" class="help-inline">Wrong input type! Make sure you input correst site name.</span></div>

							<h4>Domain:</h4>
							<div class="form-group row">
								<div class="col-sm-12">
									<select class="form-control" required name="proxy" id="site_edit_modal_proxy">

                        </select>
								</div>
							</div>

							<h4>Internal host:</h4>
							<div class="form-group row">
								<div class="col-sm-12"><input type="text" required class="form-control" name="host" id="site_edit_modal_host" minlength="7" data-validator-name="(^(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,6}$|^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$)" placeholder="192.168.0.205 or DNS name"></div>
							</div>
							<div class="container"><span style="color:red; display:none" id="site_edit_modal_host_help" class="help-inline">Wrong host! Make sure you have a dot and at least one letter before and two after it.</span></div>

							<h4>Internal port:</h4>
							<div class="form-group row">
								<div class="col-sm-12"><input type="number" min="0" required class="form-control" max="65535" name="port" step="1" id="site_edit_modal_port" data-validator-name="(^([0-9]{1,4}|[1-5][0-9]{4}|6[0-4][0-9]{3}|65[0-4][0-9]{2}|655[0-2][0-9]|6553[0-5])$)" placeholder="80"></div>
							</div>
							<div class="container"><span style="color:red; display:none" id="site_edit_modal_port_help" class="help-inline">Wrong input type! Make sure you write a number between 0 and 65535</span></div>

							<h4>Expiration date:</h4>
							<div class="form-group input-group col-sm-12" data-provide="datepicker">
								<input type="text" required class="datepicker form-control" name="date" id="site_edit_modal_date" data-validator-name="^\d{4}\-(0?[1-9]|1[012])\-(0?[1-9]|[12][0-9]|3[01])$">
								<span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
								</span>
							</div>
							<div class="row"><span style="color:red; display:none" id="site_edit_modal_date_help" class="help-inline">Wrong date format</span></div>
						</div>
						<!-- /.modal-body -->

						<div class="modal-footer form-row">
							<div class="col-sm-12 container" style="padding: 0px 0px 10px 0px">
								<button type="submit" class="form-control btn btn-primary disabled">Submit</button>
							</div>
							<div class="col-sm-12 container" style="padding: 0px">
								<button class="form-control btn btn-danger" data-dismiss="modal">Close</button>
							</div>
						</div>
						<!-- /.modal-footer -->
					</form>
				</div>
				<!-- /.modal-content -->
			</div>
			<!-- /.modal-dialog -->
		</div>
		<div id="wrapper">

			<!-- Navigation -->
			<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
				<div class="navbar-header">
					<a class="navbar-brand" href="/">SELFPORTAL</a>
				</div>

				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>

				<!-- Top Navigation: Left Menu -->
				<ul class="nav navbar-nav navbar-left navbar-top-links">
					<li><a href="/user"><i class="fa fa-home fa-fw"></i> USER PANEL</a></li>
				</ul>

				<!-- Top Navigation: Right Menu -->
				<ul class="nav navbar-right navbar-top-links">
					<li class="dropdown navbar-inverse">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">
							<i class="fa fa-bell notifymark redicon fa-fw"></i> <b class="caret"></b>
						</a>
						<ul class="dropdown-menu dropdown-alerts">
							<li>
								<a href="#">
									<div>
										<i class="fa fa-credit-card  fa-fw"></i> Password was set:
										<span class="pull-right text-muted small"><?php echo $_SESSION['pwdlastset']; ?> days ago</span>
									</div>
								</a>
							</li>
							<li>
								<div data-days-before-delete="<?php echo DAYS_BEFORE_DELETE; ?>" data-days-before-disable="<?php echo DAYS_BEFORE_DISABLE; ?>" class="notificationsallgroup list-group">
									<div class="notificationsvisiblegroup panel-collapse top-panel"></div>
									<script>
										show_notifications();

									</script>
									<!-- /.panel-body -->
								</div>
							</li>
							<li class="divider"></li>
							<li>
								<a class="text-center" href="/">
									<strong>See All Alerts</strong>
									<i class="fa fa-angle-right"></i>
								</a>
							</li>

						</ul>
					</li>
					<li class="dropdown">
						<a class="dropdown-toggle" data-toggle="dropdown" href="#">
							<i class="fa fa-user fa-fw"></i>
							<?php echo $_SESSION['displayname']?> <b class="caret"></b>
						</a>
						<ul class="dropdown-menu dropdown-user">
							<?php
                    echo "<li><a href=\"$_SERVER[SCRIPT_NAME]?dashboard=Profile\"><i class=\"fa fa-user fa-fw\"></i> User Profile</a></li>"
                    ?>
								<?php if($_SESSION['access']==2) {
                    echo "<li><a href=\"$_SERVER[SCRIPT_NAME]?dashboard=Portal settings\"><i class=\"fa fa-gear fa-fw\"></i>Admin panel</a></li>";}
                    ?>
								<li class="divider"></li>
								<li><a href="/index.php?out=logout"><i class="fa fa-sign-out fa-fw"></i> Logout</a> </li>

						</ul>
					</li>
				</ul>

				<!-- Sidebar -->
				<div class="navbar-default sidebar" role="navigation">
					<div class="sidebar-nav navbar-collapse">

						<ul class="nav" id="side-menu">
							<li class="sidebar-search hide">
								<div class="input-group custom-search-form">
									<input type="text" class="form-control" placeholder="Search...">
									<span class="input-group-btn">
                                    <button class="btn btn-primary" type="button">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </span>
								</div>
							</li>
							<li>
								<a href="/" class="active"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a>
							</li>
							<li>
								<a href="#"><i class="fa fa-sitemap fa-fw"></i> Resources<span class="fa arrow"></span></a>
								<ul class="nav nav-second-level">
									<li>
										<?php
                                    echo "<a href=\"$_SERVER[SCRIPT_NAME]?dashboard=Sites\">Sites</a>"
                                        ?>
									</li>
									<li class="hide">
										<?php
                                        echo "<a href=\"$_SERVER[SCRIPT_NAME]?dashboard=Projects\">Projects</a>"
                                        ?>
									</li>
									<li>
										<a href="#">Virtual machines <span class="fa arrow"></span></a>
										<ul class="nav nav-third-level">
											<li>
												<?php
                                            echo "<a href=\"$_SERVER[SCRIPT_NAME]?dashboard=Openstack VMs\">Openstack VMs</a>"
                                            ?>
											</li>
											<li>
												<?php
                                            echo "<a href=\"$_SERVER[SCRIPT_NAME]?dashboard=VSphere VMs\">VSphere VMs</a>"
                                            ?>
											</li>
										</ul>
									</li>
								</ul>
							</li>
						</ul>

					</div>
				</div>
			</nav>

			<!-- Page Content -->
			<div id="page-wrapper">
				<div class="container-fluid">

					<div class="row">
						<div class="col-lg-12">
							<h1 class="page-header">
								<?php
                        if (!empty($_GET['dashboard'])) echo $_GET['dashboard'];
                        else echo 'Dashboard';
                        ?>
							</h1>
						</div>
					</div>

					<!-- ... Your content goes here ... -->
					<?php
if (empty($_GET['dashboard'])) echo "
  <div class=\"jumbotron\">
    <h1 align=\"center\">Welcome to SelfPortal!</h1>
    <p align=\"center\">Nice to see you here.</p>
  </div>



<div class=\"row\">
<div class=\"col-lg-4 col-md-6\">
                        <div class=\"panel panel-green\">
                            <div class=\"panel-heading\">
                                <div class=\"row\">
                                    <div class=\"col-xs-3\">
                                        <i class=\"fa fa-sitemap fa-5x\"></i>
                                    </div>
                                    <div class=\"col-xs-9 text-right\">
                                        <div class=\"huge\" id=\"site_online\"></div>
                                    </div>
                                </div>
                                <div class=\"row\"><div class=\"col-xs-12 text-left dashboard_label\">Sites</div></div>
                            </div>
                            <a href=\"/user/index.php?dashboard=Sites\">
                                <div class=\"panel-footer\">
                                    <span class=\"pull-left\">View Details</span>
                                    <span class=\"pull-right\"><i class=\"fa fa-arrow-circle-right\"></i></span>

                                    <div class=\"clearfix\"></div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class=\"col-lg-4 col-md-6\">
                        <div class=\"panel panel-yellow\">
                            <div class=\"panel-heading\">
                                <div class=\"row\">
                                    <div class=\"col-xs-3\">
                                        <i class=\"fa fa-server fa-5x\"></i>
                                    </div>
                                    <div class=\"col-xs-9 text-right\">
                                        <div class=\"huge\" id=\"vm_online\"></div>
                                    </div>
                                </div>
                                <div class=\"row\"><div class=\"col-xs-12 text-left dashboard_label\">VMs</div></div>
                            </div>
                            <a href=\"/user/index.php?dashboard=Openstack%20VMs\">
                                <div class=\"panel-footer\">
                                    <span class=\"pull-left\">View Details</span>
                                    <span class=\"pull-right\"><i class=\"fa fa-arrow-circle-right\"></i></span>

                                    <div class=\"clearfix\"></div>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class=\"col-lg-4 col-md-6\">
                        <div class=\"panel panel-primary collapse\">
                            <div class=\"panel-heading\">
                                <div class=\"row\">
                                    <div class=\"col-xs-3\">
                                        <i class=\"fa fa-support fa-5x\"></i>
                                    </div>
                                 <div class=\"col-xs-9 text-right\">
                                        <div class=\"huge\" id=\"projects_active\"></div>                
                                    </div>
                                </div>
                                <div class=\"row\"><div class=\"col-xs-12 text-left dashboard_label\">Projects</div></div>
                            </div>
                            <a href=\"#\">
                                <div class=\"panel-footer\">
                                    <span class=\"pull-left\">View Details</span>
                                    <span class=\"pull-right\"><i class=\"fa fa-arrow-circle-right\"></i></span>

                                    <div class=\"clearfix\"></div>
                                </div>
                            </a>
                        </div>
                     </div>
                </div>
                <div class=\"row\">
<div class=\"col-lg-4 col-md-6\"><div class=\"panel panel-default\">
                            <div class=\"panel-heading\">
                                <i class=\"fa fa-bell fa-fw\"></i> Notifications Panel <button id=\"refreshnotifications\"><i class=\"fa fa-refresh\" aria-hidden=\"true\"></i></button>
                            </div>
                            <!-- /.panel-heading -->
                            <div class=\"panel-body\">
                                <div data-days-before-delete=\"".DAYS_BEFORE_DELETE."\" data-days-before-disable=\"".DAYS_BEFORE_DISABLE."\" id=\"notificationsdashboard\" class=\"list-group notificationsallgroup\">
                                <div class=\"panel-collapse notificationsvisiblegroup\"></div>
                                <div id=\"notificationshiddendashboardgroup\" class=\"panel-collapse collapse notificationshiddengroup\"></div>
                                </div>
                            </div>
                            <!-- /.panel-body -->
 </div></div></div>";
else switch ($_GET['dashboard']){
    case "Sites":
        echo "<div class=\"row\"><div class=\"col-sm-11\"><button type=\"button\" class=\"btn btn-primary btn-site-add\" data-toggle=\"modal\" data-target=\"#SiteModal\">ADD PROXY SITE</button></div><div class=\"col-sm-1\"><div onclick=\"js_panel_generate('site',".$_SESSION['user_id'].")\"><a href=\"#\"><i class=\"fa fa-refresh fa-2x\"></i></a></div></div></div><hr>";
        echo "<div id=\"sites_table_div\"><script> js_panel_generate('site',".$_SESSION['user_id']."); </script></div>";
        break;
    case "Profile":
        echo "<div class=\"panel-body\">
                                <!-- Nav tabs -->
                                <ul class=\"nav nav-tabs\">
                                    <li class=\"active \"><a href=\"#profile\" data-toggle=\"tab\" aria-expanded=\"true\">Profile</a>
                                    </li>
                                    <li class=\"\"><a href=\"#publickey\" data-toggle=\"tab\" aria-expanded=\"false\">SSH Keys</a>
                                    </li>
                                    <li class=\" hide\"><a href=\"#mailsubscribers\" data-toggle=\"tab\" aria-expanded=\"false\">Mail subscribers </a>
                                    </li>
                                </ul>

                                <!-- Tab panes -->
                                <div class=\"tab-content\">
                                        <div class=\"tab-pane fade active in\" id=\"profile\">
                                        <p></p>
                                        <p><b>User Display: </b>".$_SESSION['displayname']."
                                        <p><b>User Name: </b>".$_SESSION['user']."
                                    </div>
                                    
                                    <div class=\"tab-pane fade\" id=\"publickey\">
                                        <script> js_panel_generate('keys',".$_SESSION['user_id']."); </script>
                                    </div>
                                    <div class=\"tab-pane fade\" id=\"mailsubscribers\">
                                        <h4>mail subscribers </h4>
                                       <p>In develop</p>
                                    </div>
                                </div>
                            </div>";
        break;
    case "Portal settings":
        echo "<div class=\"panel-body\">
                                <!-- Nav tabs -->
                                <ul class=\"nav nav-tabs\">
                                    <li class=\"active\"><a href=\"#blacklist\" data-toggle=\"tab\" aria-expanded=\"true\">IP Black List</a>
                                    </li>
                                    <li class=\"\"><a href=\"#domains\" data-toggle=\"tab\" aria-expanded=\"false\">Public domains</a>
                                    </li>
                                    <li class=\"\"><a href=\"#users\" data-toggle=\"tab\" aria-expanded=\"false\">Users</a>
                                    </li>
                                    <li class=\"\"><a href=\"#sites_table_div\" data-toggle=\"tab\" aria-expanded=\"false\">Sites</a>
                                    </li>
                                    <li class=\"\"><a href=\"#openstack_vm_div\" data-toggle=\"tab\" aria-expanded=\"false\">OpenStack VMs</a>
                                    </li>
									</li>
                                    <li class=\"\"><a href=\"#vsphere_vm_div\" data-toggle=\"tab\" aria-expanded=\"false\">VSphere VMs</a>
                                    </li>
                                </ul>

                                <!-- Tab panes -->
                                <div class=\"tab-content\">
                                    <div class=\"tab-pane fade active in\" id=\"blacklist\">";
                                        echo "<script> js_panel_generate(\"blacklist\"); </script>";
                                        echo "</div>
                                    <div class=\"tab-pane fade\" id=\"domains\">";
                                        echo "<script> js_panel_generate(\"domains\"); </script>";
                                        echo "</div>
                                    <div class=\"tab-pane fade\" id=\"users\">";
                                        echo "<script> js_panel_generate(\"users\"); </script>";
                                        echo "</div>
                                    <div class=\"tab-pane fade\" id=\"sites_table_div\">";
                                        echo "<script> js_panel_generate(\"site\"); </script>";
                                        echo "</div>
                                    <div class=\"tab-pane fade\" id=\"openstack_vm_div\" panel=\"admin\">";
                                        echo "<script> js_panel_generate(\"openstackvms\"); </script>";
                                        echo "</div>
									<div class=\"tab-pane fade\" id=\"vsphere_vm_div\" panel=\"admin\">";
                                        echo "<script> js_panel_generate(\"vspherevms\"); </script>";
                                        echo "</div>
                                </div>
                            </div>";
        break;
    case "Projects":
             echo "<div class='col-lg-6'>
             <form class=\"form-horizontal\">
                <!-- Form Name -->


                <!-- Search input-->
            <div class=\"form-group\">
                     <label class=\"col-md-1 control-label\" for=\"searchinput\">User</label>
                     <div class=\"col-md-4\">
                        <input id=\"searchinput\" name=\"searchinput\" type=\"search\" placeholder=\"Display Name\" class=\"form-control input-md\" required=\"\">
                     </div>
            </div>

<!-- Select Multiple -->
<div class=\"form-group\">
  <label class=\"col-md-1 control-label\" for=\"selectProjects\">Projects</label>
  <div class=\"col-md-4\">
    <select id=\"selectProjects\" name=\"selectProjects\" class=\"form-control\" multiple=\"multiple\">
      <option value=\"1\">Project_one</option>
      <option value=\"2\">Project_two</option>
    </select>
  </div>
</div>

<!-- Select Basic -->
<div class=\"form-group\">
  <label class=\"col-md-1 control-label\" for=\"selectrole\">Role</label>
  <div class=\"col-md-4\">
    <select id=\"selectrole\" name=\"selectrole\" class=\"form-control\">
      <option value=\"Developer\">Developer</option>
      <option value=\"Tester\">Tester</option>
    </select>
  </div>
</div>

<!-- Button -->
<div class=\"form-group\">
  <label class=\"col-md-1 control-label\" for=\"addtoproject\"></label>
  <div class=\"col-md-4\">
    <buttons id=\"addtoproject\" name=\"addtoproject\" class=\"btn btn-primary\">Add</buttons>
  </div>
</div>
</form>
</div>";
             break;
             case "Openstack VMs":
                 echo "<div class=\"row\"><div class=\"col-sm-11\"><button type=\"button\" data-provider=\"openstack\" class=\"btn btn-primary btn-vm-add\">Launch Instance</button></div><div class=\"col-sm-1\"><div onclick=\"js_panel_generate('openstackvms')\"><a href=\"#\"><i class=\"fa fa-refresh fa-2x\"></i></a></div></div></div><hr><div id=\"openstack_vm_div\" panel=\"user\"><script>js_panel_generate(\"openstackvms\"); </script></div>";
				 break;
			 case "VSphere VMs":
		         echo "<div class=\"row\"><div class=\"col-sm-11\"><button type=\"button\" data-provider=\"vsphere\" class=\"btn btn-primary btn-vm-add\">Launch Instance</button></div><div class=\"col-sm-1\"><div onclick=\"js_panel_generate('vspherevms')\"><a href=\"#\"><i class=\"fa fa-refresh fa-2x\"></i></a></div></div></div><hr><div id=\"vsphere_vm_div\" panel=\"user\"><script>js_panel_generate(\"vspherevms\"); </script></div>";
				 break;
}
?>
						<div id="infos" class="container-fluid"></div>
				</div>
			</div>
		</div>
		<div class="panel-footer">
			<div align="center" class="col-md-4"></div>
			<div align="center" class="col-md-4"><a href="mailto:helpdesk_team@altoros.com?Subject=Selfportal" target="_top">Report bug</a></div>
			<div align="right" class="col-md-4"><i>Version: 0.3.0.2 (VMWare async)</i></div>
		</div>

		<!-- Metis Menu Plugin JavaScript -->
		<script src="/js/metisMenu.min.js"></script>

		<!-- Custom Theme JavaScript -->
		<script src="/js/startmin.js"></script>
		<div class="modal fade" id="addsite" role="dialog">
			<div class="modal-dialog">

				<!-- add site form content-->
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Add Site</h4>
					</div>
					<div class="modal-body">
						<div name="modal_alerts"></div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					</div>
				</div>

			</div>
		</div>
		<div id="temp_modals">
		</div>
</body>

</html>
