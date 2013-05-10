<?php

if ( isset($_REQUEST['wipe'])) {
  session_destroy();
  header("Location: {$here}");

// already got some credentials stored?
} elseif(isset($_REQUEST['refresh'])){
	$response = $XeroOAuth->refreshToken($oauthSession['oauth_token'], $oauthSession['oauth_session_handle']);
	if ($XeroOAuth->response['code'] == 200) {
		$session = persistSession($response);
		$oauthSession = retrieveSession();
	}else {
    outputError($XeroOAuth);
    if($XeroOAuth->response['helper']=="TokenExpired") $XeroOAuth->refreshToken($oauthSession['oauth_token'], $oauthSession['session_handle']);
  }
	
} elseif ( isset($oauthSession['oauth_token']) && isset($_REQUEST) ) {
	
	$XeroOAuth->config['access_token']  = $oauthSession['oauth_token'];
  	$XeroOAuth->config['access_token_secret'] = $oauthSession['oauth_token_secret'];
  	$XeroOAuth->config['session_handle'] = $oauthSession['session_handle'];
  
  
if($_REQUEST['accounts']){
  $response = $XeroOAuth->request('GET', $XeroOAuth->url('Accounts', 'core'), array('Where' => $_REQUEST['where']));
  if ($XeroOAuth->response['code'] == 200) {
    $accounts = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
    echo "There are " . count($accounts->Accounts[0]). " accounts in this Xero organisation, the first one is: </br>";
    pr($accounts->Accounts[0]->Account);
  } else {
    outputError($XeroOAuth); 
  }
  }
  
if($_REQUEST['accountsfilter']){
  $response = $XeroOAuth->request('GET', $XeroOAuth->url('Accounts', 'core'), array('Where' => 'Type=="BANK"'));
  if ($XeroOAuth->response['code'] == 200) {
    $accounts = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
    echo "There are " . count($accounts->Accounts[0]). " accounts in this Xero organisation, the first one is: </br>";
    pr($accounts->Accounts[0]->Account);
  } else {
    outputError($XeroOAuth); 
  }
  }
if($_REQUEST['payrollemployees']){
  $response = $XeroOAuth->request('GET', $XeroOAuth->url('Employees', 'payroll'), array());
  if ($XeroOAuth->response['code'] == 200) {
    $employees = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
    echo "There are " . count($employees->Employees[0]). " employees in this Xero organisation, the first one is: </br>";
    pr($employees->Employees[0]->Employee);
  } else {
    outputError($XeroOAuth); 
  }
  }
if($_REQUEST['invoice']){
  $response = $XeroOAuth->request('GET', $XeroOAuth->url('Invoices', 'core'), array());
  if ($XeroOAuth->response['code'] == 200) {
    $invoices = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
    echo "There are " . count($invoices->Invoices[0]). " invoices in this Xero organisation, the first one is: </br>";
    pr($invoices->Invoices[0]->Invoice);
    	if($_REQUEST['invoice']=="pdf"){
    		$response = $XeroOAuth->request('GET', $XeroOAuth->url('Invoice/'.$invoices->Invoices[0]->Invoice->InvoiceID, 'core'), array(), "", 'pdf');
    		$myFile = $invoices->Invoices[0]->Invoice->InvoiceID.".pdf";
			$fh = fopen($myFile, 'w') or die("can't open file");
			fwrite($fh, $XeroOAuth->response['response']);
			fclose($fh);							
    		echo "PDF copy downloaded, check your the directory of this script.</br>";
    	}
    	
  } else {
    outputError($XeroOAuth); 
  }
  }
if($_REQUEST['banktransactions']){
	if(!isset($_REQUEST['method'])){
		  $response = $XeroOAuth->request('GET', $XeroOAuth->url('BankTransactions', 'core'), array(), "", "xml");
		  if ($XeroOAuth->response['code'] == 200) {
		    $banktransactions = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
		    echo "There are " . count($banktransactions->BankTransactions[0]). " bank transactions in this Xero organisation.";
		    if(count($banktransactions->BankTransactions[0])>0){
		    	echo "The first one is: </br>";
		    	pr($banktransactions->BankTransactions[0]->BankTransaction);
		    }
		  } else {
		    outputError($XeroOAuth); 
		  }
	}elseif(isset($_REQUEST['method']) && $_REQUEST['method'] == "put" ){
		$xml = "<BankTransactions>
			    <BankTransaction>
			      <Type>SPEND</Type>
			      <Contact>
			        <Name>Westpac</Name>
			      </Contact>
			      <Date>2013-04-16T00:00:00</Date>
			      <LineItems>
			        <LineItem>
			          <Description>Yearly Bank Account Fee</Description>
			          <Quantity>1.0000</Quantity>
			          <UnitAmount>20.00</UnitAmount>
			          <AccountCode>400</AccountCode>
			        </LineItem>
			      </LineItems>
			      <BankAccount>
			        <Code>090</Code>
			      </BankAccount>
			    </BankTransaction>
			</BankTransactions>";
		$response = $XeroOAuth->request('PUT', $XeroOAuth->url('BankTransactions', 'core'), array(), $xml);
		  if ($XeroOAuth->response['code'] == 200) {
		    $banktransactions = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
		    echo "There are " . count($banktransactions->BankTransactions[0]). " successful bank transaction(s) created in this Xero organisation.";
		    if(count($banktransactions->BankTransactions[0])>0){
		    	echo "The first one is: </br>";
		    	pr($banktransactions->BankTransactions[0]->BankTransaction);
		    }
		  } else {
		    outputError($XeroOAuth); 
		  }
	}
  }
  
  
  
  if($_REQUEST['organisation']){
  $response = $XeroOAuth->request('GET', $XeroOAuth->url('Organisation', 'core'), array('page' => 0));
  if ($XeroOAuth->response['code'] == 200) {
    $organisation = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
    echo "Organisation name: " . $organisation->Organisations[0]->Organisation->Name;
  } else {
    outputError($XeroOAuth); 
  }
  }
  
 if($_REQUEST['trialbalance']){
  $response = $XeroOAuth->request('GET', $XeroOAuth->url('Reports/TrialBalance', 'core'), array('page' => 0));
  if ($XeroOAuth->response['code'] == 200) {
    $report = $XeroOAuth->parseResponse($XeroOAuth->response['response'], $XeroOAuth->response['format']);
    echo "Organisation name: " . $report->Organisations[0]->Organisation->Name;
  } else {
    outputError($XeroOAuth); 
  }
  }
  
}