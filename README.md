# WHMCS-Auto-Accept-Orders

This WHMCS Hook will auto accept orders. There is no need to manually accept them anymore !
No more “pending” orders…

Hook is highly customizable with a settings section that will let you set the following –

* Activate product provisioning (yes/no)
* Perform domain automation (yes/no)
* Send welcome email to client (yes/no)
* Accept only paid orders (yes/no)

# Installation

Edit it with your favourite code editor (we recommend notepad++).

In the begining of the hook file, you will find our settings section –


	return array( 
		'autosetup' 		=> false,
		'sendregistrar' 	=> false, 
		'sendemail' 		=> false, 
		'ispaid'		=> true, 
	);


* autosetup – determines whether product provisioning is performed
* sendregistrar – determines whether domain automation is performed
* sendemail – sets if welcome emails for products and registration confirmation emails for domains should be sent
* ispaid – set to true if you want to accept only paid orders

Once you finished the editing the settings, upload it to your WHMCS hooks folder (“includes/hooks“).

That’s all !