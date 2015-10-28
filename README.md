1. Download your cryptocoin daemon, setup JSON RPC values in your config and add:
```
txindex = 1
```
2. Run your daemon with _-reindex_ and _-server_ arguments
3. Generate your address and private key using vanitygen
4. Fill in the **config.php** file
5. Run **setup.php** script to add your address to the daemon
6. Add **transactions.sql** schema to your database
7. Run **script.php** script from CLI, it should be running in background
