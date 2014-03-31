<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/html">

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
    <link href="<?php echo base_url()?>resources/css/teamsync.css" rel="stylesheet">

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
    </div>
    <!-- /.container -->
</nav>

<div class="intro-header">

    <div class="container">

        <div class="row">
            <div class="col-lg-12">

                <?php echo validation_errors(); ?>

                <?php echo form_open('site/contact'); ?>

                <h5>Your name</h5>
                <input class="field" style="color:black;" type="text" name="name"  value="<?php echo set_value('name');?>"/>

                <h5>Email address</h5>
                <input class="field" style="color:black;" type="text" name="email_address" value="<?php echo set_value('email_address');?> " />
                <br> <br>

                <h5>Message</h5>
                <textarea class="field" style="color:black;" name="message" rows="10" ><?php echo set_value('message');?></textarea>
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
<script src="js/jquery-1.10.2.js"></script>
<script src="js/bootstrap.js"></script>

</body>

</html>
