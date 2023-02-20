<?php
/*
Author: Javed Ur Rehman
Website: https://www.allphptricks.com
*/
$status = "";
$response_type ='danger';
if ($_SERVER['REQUEST_METHOD'] == 'POST'){
	// User card information data received via form submit
	$cc_number = $_POST['cc_number'];
	$cc_exp_month = $_POST['cc_exp_month']; 
	$cc_exp_year = $_POST['cc_exp_year']; 
	$cc_exp_year_month = $cc_exp_year.'-'.$cc_exp_month; 
	$cvc_code = $_POST['cvc_code']; 
	$amount = $_POST['amount']; 
	if(empty($cc_number) || empty($cc_exp_month) || empty($cc_exp_year) || empty($cvc_code) ){
		$status = "<li>Error: Please enter all required fields!</li>";
	}else{
		require_once 'authorize-net-payment.php';
	}	
}
?>
<html>
<head>
<title>Demo Integrate Authorize.Net Payment Gateway using PHP - AllPHPTricks.com</title>
<link rel='stylesheet' href='css/style.css' type='text/css' media='all' />
</head>
<body>

<div style="width:700px; margin:50 auto;">
<h2>Demo Integrate Authorize.Net Payment Gateway using PHP - AllPHPTricks.com</h2>

<?php if(!empty($status)) { ?>
<div class="alert alert-<?php echo $response_type;?>">
	<ul>
		<?php echo $status; ?>
	</ul>
</div>
<?php } ?>

<p><strong>Charge $10.00 with Authorize.Net Demo Payment</strong></p>

<form method='post' action=''>
	<input type='hidden' name='amount' value='10.00'> 
	<h3>Enter Credit Card Information</h3>
	<input name="cc_number" type="text" class="form-control" maxlength="20" placeholder="Card Number*" style="width:80%;">
	
	<select name="cc_exp_month" class="form-select">
		<option value="">Exp Month*</option>
	<?php
	for ($m=1; $m<=12; $m++) {
	if($m<10){
		$new_m='0'.$m;
	}else{
		$new_m=$m;
	}
	$month = date('F', mktime(0,0,0,$m, 1, date('Y')));
	echo "<option value='$new_m'>$new_m - $month</option>";
	}
	?>
	</select>

	<select name="cc_exp_year" class="form-select">
	<option value="">Exp Year*</option>
		<?php for($fy=2022; $fy<=2030; $fy++) { ?>
			<option value="<?php echo $fy; ?>"><?php echo $fy; ?></option>
		<?php } ?>
	</select>

	<input name="cvc_code" type="text" class="form-control" maxlength="3" placeholder="Card CVC*">

	<button type='submit' class='pay'>Pay Now</button>
</form>

<div style="clear:both;"></div>
<p><strong>List of Testing Credit Cards</strong></p>
<p>Use the following Sandbox environment testing credit cards details to test the payment process.</p>
<p>
	<ul style="padding-left: 20px;">
		<li>Mastercard - 5424000000000015</li>	
		<li>VISA - 4111111111111111</li>
		<li>American Express - 370000000000002</li>
		<li>JCB - 3088000000000017</li>
		<li>Discover - 6011000000000012</li>
	</ul>
</p>
<p>Select any future valid expiration date (month/year) & CVC is <strong>123</strong>.</p>

<a href="https://www.allphptricks.com/integrate-authorize-net-payment-gateway-using-php/">Tutorial Link</a> <br /><br />
For More Web Development Tutorials Visit: <a href="https://www.allphptricks.com/">AllPHPTricks.com</a>

</div>    
</body>
</html>