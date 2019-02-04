<?php
$str = file_get_contents('sample.json');
$allJsonDate = json_decode($str, true);
$context = stream_context_create(array('http' => array('header' => 'Accept: application/xml')));
$formData = array();

function getXmlData($xmlUrl, $context)
{
    $xmlAsHttp = file_get_contents($xmlUrl, false, $context);
    $xml = simplexml_load_string($xmlAsHttp);
    $json = json_encode($xml);
    return json_decode($json, true);
}

function checkIsset($data)
{
    $dataSet = isset($data) ? $data : '';
    return is_array($dataSet) ? '' : $dataSet;

}

function getDatafromJson($jsonData, $orgDetails)
{
    $data = array();
    $formType = $orgDetails['FormType'];

    $data['ein'] = checkIsset($orgDetails['EIN']);
    $data['name'] = checkIsset($jsonData['ReturnHeader']['Filer']['BusinessName']['BusinessNameLine1Txt']);
    $data['phoneNumber'] = checkIsset($jsonData['ReturnHeader']['Filer']['PhoneNum']);
    $data['streetPoBox'] = checkIsset($jsonData['ReturnHeader']['Filer']['USAddress']['AddressLine1Txt']);
    $data['city'] = checkIsset($jsonData['ReturnHeader']['Filer']['USAddress']['CityNm']);
    $data['state'] = checkIsset($jsonData['ReturnHeader']['Filer']['USAddress']['StateAbbreviationCd']);
    $data['zip'] = checkIsset($jsonData['ReturnData']["IRS$formType"]['CYTotalRevenueAmt']);
    $data['revenue'] = checkIsset($jsonData['ReturnData']["IRS$formType"]['CYTotalRevenueAmt']);
    $data['expenses'] = checkIsset($jsonData['ReturnData']["IRS$formType"]['CYTotalExpensesAmt']);
    $data['netAssets'] = checkIsset($jsonData['ReturnData']["IRS$formType"]['TotalAssetsEOYAmt']);
    $data['numberofEmployees'] = checkIsset($jsonData['ReturnData']["IRS$formType"]['EmployeeCnt']);

    $data['informationTechnologySpend_informationTechnologyGrp'] = checkIsset($jsonData['ReturnData']["IRS$formType"]['InformationTechnologyGrp']);
    $data['informationTechnologySpend_total'] = checkIsset($jsonData['ReturnData']["IRS$formType"]['InformationTechnologyGrp']['TotalAmt']);
    $data['informationTechnologySpend_programServices'] = checkIsset($jsonData['ReturnData']["IRS$formType"]['InformationTechnologyGrp']['ProgramServicesAmt']);
    $data['informationTechnologySpend_mangementandGeneral'] = checkIsset($jsonData['ReturnData']["IRS$formType"]['InformationTechnologyGrp']['ManagementAndGeneralAmt']);
    $data['informationTechnologySpend_fundraising'] = checkIsset($jsonData['ReturnData']["IRS$formType"]['InformationTechnologyGrp']['FundraisingAmt']);

    $data['independentContractors_contractorCompensationGrp'] = checkIsset($jsonData['ReturnData']["IRS$formType"]['ContractorCompensationGrp']);
    $data['independentContractors_name'] = checkIsset($jsonData['ReturnData']["IRS$formType"]['ContractorCompensationGrp']['ContractorName']['BusinessName']['BusinessNameLine1Txt']);
    $data['independentContractors_type'] = checkIsset($jsonData['ReturnData']["IRS$formType"]['ContractorCompensationGrp']['ServicesDesc']);
    $data['independentContractors_amount'] = checkIsset($jsonData['ReturnData']["IRS$formType"]['ContractorCompensationGrp']['CompensationAmt']);
    return $data;
}

foreach ($allJsonDate['Filings2018'] as $x => $orgDetails) {
    $array = getXmlData($orgDetails['URL'], $context);
    $formDetails = getDatafromJson($array, $orgDetails);
    array_push($formData, $formDetails);
}


$csvHeader = array("EIN", "Name", "Phone Number", "Street/PO Box", "City", "Sate", "Zip", "Revenue", "Expenses", "Net Assets", "Number of Employees", "Information Technology Spend", "Total", "Program Services", "Mangement and General", "Fundraising", "Independent Contractors", "Name", "Type", "Amount");
array_unshift($formData, $csvHeader);
// echo json_encode($formData);
  $fichier = 'sample.csv';
 header( "Content-Type: text/csv;charset=utf-8" );
 header( "Content-Disposition: attachment;filename=\"$fichier\"" );
 header("Pragma: no-cache");
 header("Expires: 0");

 $fp= fopen('php://output', 'w');

 foreach ($formData as $fields)
 {
    fputcsv($fp, $fields);
 }
 fclose($fp);
 exit();
