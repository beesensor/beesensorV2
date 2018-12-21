<?php if(!class_exists('Rain\Tpl')){exit;}?><?php require $this->checkTemplate("includes/top");?>

<style>
.login-page {
    background-color: #222d32;
}
</style>
<script type="text/javascript">

</script>
</head>
<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo">
            <img src="<?php echo htmlspecialchars( $publicPath, ENT_COMPAT, 'UTF-8', FALSE ); ?>images/logo.png" class="img-responsive" alt="Beesensor" />
        </div>
        <form class="login-box-body" method="post" enctype="multipart/form-data">
            <?php if( $alert!='' ){ ?>

            <div class="row">
                <div class="col-xs-12">
                    <?php echo $alert; ?>

                </div>
            </div>
            <?php } ?>

            <p class="login-box-msg">Introduzca sus credenciales para iniciar sesi&oacute;n</p>
            <div class="row">
                <div class="form-group col-xs-12">
                    <label for="mail">Email:</label>
                    <input type="text" class="form-control" id="mail" name="mail" placeholder="Email del usuario" />
                </div>
            </div>
            <div class="row">
                <div class="form-group col-xs-12">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password del usuario" />
                    <input type="hidden" id="pass" name="pass" />
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 clearfix">
                    <button type="submit" class="btn btn-success pull-right">
                        <i class="fa fa-unlock-alt"> </i> Acceder
                    </button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
