<?php

require("vendor/autoload.php");

$user = 'Universal API/uAPI3323216300-83d08174';
$pass = 'S7YdFnYdF7fSpB7d8s4r8YS8E';


$TARGETBRANCH = 'P7009867';
$Provider = '1G';//1G/1V/1P/ACH
$PreferredDate = date('Y-m-d', strtotime("+75 day"));

$message = <<<EOM
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
   <soapenv:Header/>
   <soapenv:Body>
      <air:LowFareSearchReq TraceId="trace" AuthorizedBy="user" SolutionResult="true" TargetBranch="$TARGETBRANCH" xmlns:air="http://www.travelport.com/schema/air_v33_0" xmlns:com="http://www.travelport.com/schema/common_v33_0">
         <com:BillingPointOfSaleInfo OriginApplication="UAPI"/>
         <air:SearchAirLeg>
            <air:SearchOrigin>
               <com:Airport Code="DEN"/>
            </air:SearchOrigin>
            <air:SearchDestination>
               <com:Airport Code="SFO"/>
            </air:SearchDestination>
            <air:SearchDepTime PreferredTime="$PreferredDate">
            </air:SearchDepTime>            
         </air:SearchAirLeg>
         <air:AirSearchModifiers>
            <air:PreferredProviders>
               <com:Provider Code="$Provider"/>
            </air:PreferredProviders>
         </air:AirSearchModifiers>
		 <com:SearchPassenger BookingTravelerRef="1" Code="ADT" xmlns:com="http://www.travelport.com/schema/common_v33_0"/>
      </air:LowFareSearchReq>
   </soapenv:Body>
</soapenv:Envelope>
EOM;

function dd($var) {
    echo '<pre>';
    var_dump($var);
    die;
}

$service = new Travelport\Air\Service(
    "https://emea.universal-api.pp.travelport.com/B2BGateway/connect/uAPI/AirService",
    $user,
    $pass
);

$response = new \Travelport\Air\LowFareSearchRsp($service->call($message));

//header('Content-Type: application/json');
//echo json_encode($response->flightDetails()[0]);
dump($response->createResponseXMLFile()->airSegments());
//header('Content-Type: text/xml');
//echo $response->xml();

