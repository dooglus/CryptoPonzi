<?php
	include('config.php');
	require_once 'jsonRPCClient.php';
	$debug_rpc = false;
	$client = new jsonRPCClient('http://' . $rpc['login'] . ':' . $rpc['password'] . '@' . $rpc['ip'] . ':' . $rpc['port'] . '/', $debug_rpc) or die('Error: could not connect to RPC server.');

	$lastPayout = 0;
	$adresses = array();

	function getAddress($trans)
	{
		global $client;
		$address = "";

		$details = $client->getrawtransaction($trans["txid"], 1);

		$vintxid = $details['vin'][0]['txid'];
		$vinvout = $details['vin'][0]['vout'];

		try {
			$transactionin = $client->getrawtransaction($vintxid, 1);
		}
		catch (Exception $e) {
			die("Error with getting transaction details.\nYou should add 'txindex=1' to your .conf file and then run the daemon with the -reindex parameter.");
		}
		
		if ($vinvout == 1)
			$vinvout = 0;
		else
			$vinvout = 1;
		
		$address = $transactionin['vout'][!$vinvout]['scriptPubKey']['addresses'][0];
		return $address;
	}
	
	while(true)
	{
		// Parsing and adding new transactions to database
		print("Parsing transactions...\n");
		$transactions = $client->listtransactions($config['ponziacc'], 100);
		$i = 0;
		print("Parsing: ");
		foreach ($transactions as $trans)
		{
			echo(++$i . ", ");
			
			if ($trans['category'] != "receive" || $trans["confirmations"] < $config['confirmations'])
				continue;
			
			if ($trans['amount'] > $config['max'] || $trans['amount'] < $config['min'])
			{
				$query = mysql_query('SELECT * FROM `transactions` WHERE `tx` = "'.$trans['txid'].'";');
				if (!mysql_fetch_assoc($query))
				{
					if ($trans['amount'] < 0)
						continue;

					if ($config['sendback'])
						$client->sendtoaddress(getAddress($trans), $trans['amount'] - ($trans['amount'] * $config['fee']));
					else
						$client->sendtoaddress($config['ownaddress'], $trans['amount'] - ($trans['amount'] * $config['fee']));
						
// this is setting the 'date' column to be the current time() when the transaction is first seen. you said something about wiping the database and recreating it, so it will have seen all the transactions at the same time I guess.
// sorry... go on... actually, I'm wrong. this is the codee for transactions which are >max or <min... so I'll look for other mentions of 'date'
					mysql_query("INSERT INTO `transactions` (`id`, `amount`, `topay`, `address`, `state`, `tx`, `date`) VALUES (NULL, '" . $trans['amount'] . "', '0', '0', '3', '" . $trans['txid'] . "', " . (time()) . ");");
					print("\n" . $trans['amount'] + " - Payment has been sent to you!\n");
					continue;
				}
			}
		
			$query = mysql_query('SELECT * FROM `transactions` WHERE `tx` = "'.$trans['txid'].'";');
			if (!mysql_fetch_assoc($query)) // Transaction not found in DB
			{
				$amount = $trans['amount'];
				$topay = $amount * (1.0 + $config['income']);
				print("\nTransaction added! [" . $amount . "]\n");
				$address = getAddress($trans);

// this is the one - it's setting the date the first time it sees a transaction
// would be better to use the date field from the clamd output, but I don't think it matters really - the 'id' field is automatically set to increase for each transaction, and that's what decides which order to pay people out in
				mysql_query("INSERT INTO `transactions` (`id`, `amount`, `topay`, `address`, `state`, `tx`, `date`) VALUES (NULL, '" . $amount . "', '" . $topay . "', '" . $address . "', '0', '" . $trans['txid'] . "', " . (time()) . ");");
			}
		}
		print("\n");		

// trying to see how it works out what it can afford to pay...
// this is summing all the 'amount' fields - all the amounts it has ever received - hopefully this includes incoming stake rewards too
		$query = mysql_query("SELECT SUM(amount) FROM `transactions`;");
		$query = mysql_fetch_row($query);
		$money = $query[0];
		
// from that it subtracts all the amounts it has already paid (2), refunded (3), or earmarked for paying (1):
		$query = mysql_query("SELECT SUM(topay) FROM `transactions` WHERE `state` > 0;");
		$query = mysql_fetch_row($query);
		$money -= $query[0];
// so now $money should be what's left

// when working out which tx it can afford to pay out, it goes through them in order of the 'id' column:
		$query = mysql_query("SELECT * FROM `transactions` WHERE `state` = 0 AND `topay` > 0 ORDER BY `id` ASC;");
		while($row = mysql_fetch_assoc($query))
		{
// so we go through the transactions which aren't yet paids or marked for paying (state = 0)
			print("We have " . $money . " and need " . $row['topay'] . " to pay out tx " . $row['id'] . "\n");
// if we can't afford to pay what we need to pay for this tx, we stop trying
			if ($money < $row['topay']) {
				print("We can't afford it\n");	
				break;
			}
				
// otherwise we mark this one for paying
			mysql_query("UPDATE `transactions` SET `state` = 1 WHERE `id` = " . $row['id'] . ";");
			$money -= $row['topay'];
		}
		
		// Paying out
		if (time() - $lastPayout > $config['payout-check'])
		{
			$lastPayout = time();
// and then when actually paying them, it goes by date order:
// seems odd to use two different orderings, but whatever
			$query = mysql_query('SELECT * FROM `transactions` WHERE `state` = 1 ORDER BY `date` ASC;');
			while($row = mysql_fetch_assoc($query))
			{
				// print_r($row);
				try {
					$txout = $client->sendfrom($config['ponziacc'], $row['address'], round((float)$row['topay'], 4) - ($row['amount'] * $config['fee']));
					mysql_query("UPDATE `transactions` SET `state` = 2, `out` = '" . $txout . "' WHERE `id` = " . $row['id'] . ";");
					print($row['topay'] . " " . $config['val'] ." sent to " . $row['address'] . ".\n");
				} catch (Exception $e) {
					print("error: " . $e->getMessage() . "\n");
					break;
				}
			}
		}

		echo ("Waiting...\n");
		sleep(60);
	}
?>
