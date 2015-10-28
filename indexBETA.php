
<?php include('header.php'); ?>
<div class="jumbotron" style="text-align: center;">
<img src="/assets/logo.png">
  <h1>Stake-n-Bake Profit!</h1>
  <p>Toss some of your <?php echo $config['val'] ?> into the oven in order to bake it.<br>
  When its done baking it will be sent back <strong><?php echo(100 + ($config['income'] * 100)) ?>%</strong> it's original size.</p>
  <div id="address-wrapper" style="overflow: hidden;">
<a href="<?php echo($config['blockchain-addr'] . $config['address']) ?>"><strong><?php echo $config['address'] ?></strong></a>
</div>
<i>Everything is fixed now! Clams are now baking perfectly!<br>
Deposit and withdraw status: <font color="green"><b>ONLINE</b></font>.</i><br>
<br>
	Send an amount between <span class="label label-info"><?php echo $config['min'] ?> - <?php echo $config['max'] ?> <?php echo $config['val'] ?></span> to the address listed above and get back <?php echo(100 + ($config['income'] * 100)) ?>%!<br>
	The CLAM you bake will be held until enough become available to process your payout.<br><br>

	<i>Due to the amazing staking ability of CLAM, You have no risk of losing your baking ones.
<br>
	Whatever you send will be sent back as soon as others bake <b>OR</b> you stake enough.</i>
<br><br>	

	<font color="red"> <u><b>Warning</b></u>:</font> Please <strong>do not</strong> deposit from an exchange / just-dice or any address you don't own.<br>
If you don't have the Official CLAM Client you can download it from <a href="http://clamclient.com" target="_blank">CLAMclient.com</a>
<br>
<i>(Your CLAM will be returned to the address it was received from)</I><br>
	
	<?php if($config['fee'] > 0): ?>
	<h6 style="text-align: center; color: rgb(200,200,200)"><strong>Note:</strong> We are taking a <?php echo($config['fee'] * 100) ?>% baking fee to pay for server.</h6><?php endif; ?></div>
<div class="jumbotron" style="padding: 30px;">
	<div class="row" style="text-align: center; font-size: 18px;">
		<div class="col-md-3">
			Transactions: <br><span class="label label-info" id="count">0</span>
		</div>
		<div class="col-md-3">
			Paid: <br><span class="label label-success" id="paid">0.00 <?php echo $config['val'] ?></span>
		</div>
		<div class="col-md-3">
			Unpaid: <br><span class="label label-warning" id="unpaid">0.00 <?php echo $config['val'] ?></span>
		</div>
		<div class="col-md-3">
			Received: <br><span class="label label-info" id="received">0.00 <?php echo $config['val'] ?></span>
		</div><br><br><br>
<span class="label label-info" id="collecting"></span>
	</div>
</div>

<div class="jumbotron" style="text-align: center; padding: 20px;">	
	<h2>Latest Transactions:</h2>
	<div class="table-responsive">
		<table class="table table-hover table-striped">
			<thead>
				<tr>
					<td style="width: 6%;"></td>
					<td style="width: 40%">TXID:</td>
					<td style="width: 24%">Returns:</td>
					<td style="width: 30%">Date:</td>
				</tr>
			</thead>
			<tbody id="trans"></tbody>
		</table>
	</div>
	<div class="form-inline" style="text-align: right; margin-bottom: 20px; margin-top: 20px;" role="form">
		<div class="form-group">
			<label class="sr-only" for="search">Transaction ID</label>
			<input type="email" class="form-control" id="tid" placeholder="Enter transaction ID...">
		</div>
		<button type="submit" class="btn btn-info" onclick="search()">Search</button>
		<button type="submit" class="btn btn-default" onclick="showall()">Reset</button>
	</div>
	<h6 style="text-align: center; color: rgb(200,200,200)"><strong>Note:</strong> Fully baked CLAM are sent every <?php echo $config['payout-check'] ?> seconds.</h6>
	<h6 style="text-align: center; color: rgb(200,200,200)"><strong>Note:</strong> Transactions are added above after <?php echo $config['confirmations'] ?> confirmation<?php if($config['confirmations'] > 1) echo 's.'; ?></h6>
</div>

<script>
	var what = "all";
	
	function search()
	{
		what = $('#tid').val();
		update();
	}
	
	function showall()
	{
		what = "all";
		update();
	}
	
	function update()
	{
		$.get(
				"json.php",
				{
					what: what
				},
				function(data){
					data = JSON.parse(data);
					$('#count').html(data['count']);
					$('#paid').html(parseFloat(data['paid']).toFixed(<?php echo $config['precision'] ?>) + ' <?php echo $config['val'] ?>');
					$('#unpaid').html(parseFloat(data['unpaid']).toFixed(<?php echo $config['precision'] ?>) + ' <?php echo $config['val'] ?>');
					$('#received').html(parseFloat(data['received']).toFixed(<?php echo $config['precision'] ?>) + ' <?php echo $config['val'] ?>');
					
					$('#collecting').html("Collecting " + (parseFloat(data['received']) - parseFloat(data['paid'])).toFixed(<?php echo $config['precision'] ?>) + " / " + parseFloat(data['actual']['topay']).toFixed(<?php echo $config['precision'] ?>) + " for " + data['actual']['tx'].substring(0,32) + "...");
					
					$('#trans').html('');
					for(var i in data['transactions'])
					{
						var state = "";
						if (data['transactions'][i]['state'] == "1")
							state = '<span class="label label-info" id="collecting">Ready</span>';
						else if (data['transactions'][i]['state'] == "2")
							state = '<span class="label label-success" id="collecting">Sent</span>';
						else
							state = '<span class="label label-default" id="collecting">Baking</span>';
					
						$tr = $('<tr></tr>');
						$('#trans').append($tr);
					
						$td = $('<td>' + state + '</td>');
						$($tr).append($td);
						
						var out = "";
						if (data['transactions'][i]['out'])
							out = '<br>OUT: <a href="<?php echo $config['blockchain-tx'] ?>' + data['transactions'][i]['out'] + '">' + data['transactions'][i]['out'].substring(0,25) + '...</a>';
						
						$td = $('<td style="text-align: left;">IN: <a href="<?php echo $config['blockchain-tx'] ?>' + data['transactions'][i]['tx'] + '">' + data['transactions'][i]['tx'].substring(0,26) + '...</a>' + out + '</td>');
						$($tr).append($td);
						
						$td = $('<td>' + parseFloat(data['transactions'][i]['topay']).toFixed(<?php echo $config['precision'] ?>) + ' <?php echo $config['val'] ?></td>');
						$($tr).append($td);
					
						$td = $('<td>' + data['transactions'][i]['date'] + '</td>');
						$($tr).append($td);
						
						if (data['transactions'].length == 1)
						{
							$tr = $('<tr></tr>');
							$('#trans').append($tr);
							
							$td = $('<td colspan="4">In queue, actual position: <span class="label label-info">' + data['transactions'][i]['queue'] + '</span></td>');
							$($tr).append($td);
						}
					}
					
					if (data['transactions'].length == 0)
					{
						$tr = $('<tr></tr>');
						$('#trans').append($tr);
						
						$td = $('<td colspan="4">No transactions found.</td>');
						$($tr).append($td);
					}
					
					setTimeout(update, 15 * 1000);
				}
		);
	}
	update();
</script>

<?php include('footer.php'); ?>