<?php if(!class_exists('Rain\Tpl')){exit;}?><html>
    <head>
        <style>
            html, body {
                margin:0;
                background-color: #ffffff;
                color: #990000;
            }

            pre {
                padding: 5px;
                background-color: #fff999;
            }
            h1 {
                padding: 5px;
            }
        </style>
    </head>
    <body>
        <h1>Voltor error: <?php echo htmlspecialchars( $title, ENT_COMPAT, 'UTF-8', FALSE ); ?></h1>
        <pre>
            <?php echo htmlspecialchars( $exception, ENT_COMPAT, 'UTF-8', FALSE ); ?>

        </pre>
    </body>
</html>
