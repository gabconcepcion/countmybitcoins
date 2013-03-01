<?php
if(isset($_REQUEST['address'])):

	$url = 'http://blockchain.info/multiaddr?active=_address_';
	$url = str_replace('_address_', $_REQUEST['address'], $url);

	header('Content-type: application/json');
	echo file_get_contents($url);
else: 

$conversions = file_get_contents('http://blockchain.info/ticker');

?>
<!doctype html>
<html lang="eng">
<body>
	<textarea id="txtAdrs" rows="5" cols="50" placeholder="Separate address with , or new line.."></textarea>
	<br/>
	<button id="btnCount">Count My Coins</button>
	<div>
		<h3 style="margin-bottom:0">Local Storage</h3>
		<a href="#" id="elClearLocalStorage" style="color:blue;">clear local storage</a>
		<h4>Total Bitcoins: <span id="elTotalBit"></span></h4>
		<div id="elLocalStorage"></div>
	</div>
<script src="jquery-1.9.1.min.js"></script>
<script type="text/javascript">
var O = {
	conversions: {},
	totalBtc: 0,
	totalMoney: 0,
	// magic number to get the human readable bitcoin value
	// 100 million
	divisor: 100000000, 
	// local storage handler
	oLocalStorageData: {addresses:[]},
	o: function(e){
		return document.getElementById(e);
	},
	render:function(e){
		document.body.appendChild(e);
	},
	// let the show begin
	init:function(){

		// cleanup
		this.o('elTotalBit').innerHTML = '';
		this.o('elLocalStorage').innerHTML = '';
		this.o('elClearLocalStorage').style.display = 'none';

		this.aLocalAddress = [];

		// render cache bitcoin addresses
		var addresses = this.getLocalAddresses();
		if(addresses!=null)
		{
			this.oLocalStorageData = addresses;
			this.onRequestSuccess(addresses);
		}

		// parse bitcoin current market prices
		this.conversions = <?php echo $conversions?>;

		$('#btnCount').click(this.countCoins);

		$('#elClearLocalStorage').click(function(){
			O.clearLocalStorage();
			O.init();
		});
	},
	// fetch bitcoin data from api
	countCoins:function()
	{
		try
		{
			var as = O.o('txtAdrs').value.split('\n');
			as.concat(O.o('txtAdrs').value.split(','));

			// filter invalid address
			var invalid_addresses = [];
			for(var i in as)
			{
				if(as[i].length!=34)
				{
					invalid_addresses.push(as[i]);
					as.splice(i, 1);
				}
			}
			if(invalid_addresses.length>0)
				alert("Invalid addresses has been removed:\n"+invalid_addresses.join("\n"));

			// filter the address that is already counted
			for(var i in as)
				if($('#'+as[i]).length==1)
					as.splice(i, 1);

			var bchainaddress = 'index.php?address=_address_';
			
			if(as.length>0)
			{
				var adrs = bchainaddress.replace('_address_', as.join('|'));
				$.ajax({
				   url: adrs,
				   type: "GET",
				   async:true,
				   data: {},
				   success: O.onRequestSuccess,
				   error: function(){alert('Error getting wallet balance!')}
				});	
			}
		}
		catch(e)
		{
			alert(e);
		}
	},
	onRequestSuccess:function(oResult){

		var aAddress = oResult.addresses;
		for(var i in aAddress)
		{
			var final_balance = aAddress[i].final_balance;
			var address = aAddress[i].address;

			//local storage data
			O.oLocalStorageData.addresses.push({
				address: address,
				final_balance: final_balance
			});

			// render bitcoin
			if($('#'+address).length==0)
			{
				var _final_balance = final_balance / O.divisor;
				var elDiv = document.createElement('DIV');
				elDiv.id = address;
				elDiv.innerHTML = address+' ('+_final_balance+')';
				O.o('elLocalStorage').appendChild(elDiv);
				O.totalBtc += _final_balance;
			}
		}
		O.o('elTotalBit').innerHTML = O.totalBtc;
		O.setLocalAddresses(O.oLocalStorageData);
		O.o('elClearLocalStorage').style.display = '';
	},
	// local storage function starts here
	clearLocalStorage:function()
	{
		window.localStorage.clear();
	},
	getLocalAddresses:function()
	{
		try{
			return JSON.parse(window.localStorage.getItem('addresses'));
		}catch(e){
			console.log(e);
			return null;
		}
	},
	setLocalAddresses:function(oObj)
	{
		try{
			window.localStorage.setItem('addresses', JSON.stringify(oObj));
		}catch(e){
			console.log(e);
			return false;
		}
		return true;
	},
	// local storage function ends here
};$(document).ready(function(){O.init()});
</script>
</body>
</html>
<?php endif;?>