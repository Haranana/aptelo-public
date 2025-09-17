<?php
/*
<html>
    <head>
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
        <title>Raty PayU</title>
        <script src="https://secure.payu.com/res/v2/jquery-1.7.2.js"></script>
        <script src="https://secure.payu.com/res/v2/jquery-ui-1.7.2.min.js"></script>
        <script src="https://secure.payu.com/res/v2/jquery.i18n.js"></script>
        <script src="https://secure.payu.com/res/v2/jsrender.js"></script>
        <script src="https://secure.payu.com/res/v2/openpayu-2.0.js"></script>
        <script src="https://secure.payu.com/res/v2/widget-installments-2.0.js"></script>
        <link href="https://secure.payu.com/res/v2/layout/style.css" rel="stylesheet">
    </head>
<body>

<div id="installment-calc"></div>
<script type="text/javascript">
var openpayu = openpayu || {};
var kwota = <?php echo $_GET['kwota']; ?>;
openpayu.options = {
    creditAmount: kwota,
    installmentAmount: 12
};
OpenPayU.Installments.calculator('#installment-calc');
</script>

</body>
</html>
*/
?>