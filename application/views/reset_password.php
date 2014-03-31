<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>TeamSync | Password Reset </title>

    <!-- Bootstrap core CSS -->
    <link href="<?php echo base_url()?>resources/bootstrap/css/bootstrap.css" rel="stylesheet">

    <!-- Custom Google Web Font -->
    <link href="<?php echo base_url()?>resources/bootstrap/font-awesome/css/font-awesome.min.css" rel="stylesheet">
    <link href='http://fonts.googleapis.com/css?family=Lato:100,300,400,700,900,100italic,300italic,400italic,700italic,900italic' rel='stylesheet' type='text/css'>

    <!-- Add custom CSS here -->
    <link href="<?php echo base_url()?>resources/bootstrap/css/landing-page.css" rel="stylesheet">

</head>

<body>

<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <p class="navbar-brand">TeamSync</p>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse navbar-right navbar-ex1-collapse">
            <ul class="nav navbar-nav">
                <li><a href="<?php echo base_url() ?>">Home</a>
                </li>
                <li><a href="<?php echo base_url()?>site/contact">Contact</a>
                </li>

            </ul>
        </div>
        <!-- /.navbar-collapse -->

    </div>
    <!-- /.container -->
</nav>

<div class="intro-header">

    <div class="container">

        <div class="row">
            <div class="col-lg-12">

                <?php echo validation_errors(); ?>

                <?php echo form_open('authentication/resetpassword'); ?>

                <h5>Password</h5>
                <input class="field" style="color:black;" type="password" name="password" value="" />

                <h5>Confirm Password</h5>
                <input class="field" style="color:black;" type="password" name="confirm_password" value="" />
                <br> <br>
                <input type="submit" class="btn btn-default btn-lg" value="Submit" size="50"/>

                </form>

            </div>
        </div>

    </div>
    <!-- /.container -->

</div>
<!-- /.intro-header -->


    <!-- /.container -->

</div>
<!-- /.banner -->

<footer>
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <ul class="list-inline">
                    <li><a href="<?php echo base_url() ?>">Home</a>
                    </li>
                    <li class="footer-menu-divider">&sdot;</li>
                    <li><a href="<?php echo base_url()?>/site/contact">Contact</a>
                    </li>
                </ul>
                <p class="copyright text-muted small">Copyright &copy; TeamSync 2013. All Rights Reserved</p>
            </div>
        </div>
    </div>
</footer>

<!-- JavaScript -->
<script src="<?php echo base_url()?>resources/bootstrap/js/jquery-1.10.2.js"></script>
<script src="<?php echo base_url()?>resources/bootstrap/js/bootstrap.js"></script>
</body>

</html>
