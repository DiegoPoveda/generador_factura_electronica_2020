<?php
public function index(Request $request){

  $consecutive = DB::table('counter')->first()->invoice;
  $items = $invoice->items;
  $user = $invoice->user;
  $office = $invoice->office;
  $InvoiceAuthorization = $office->invoice_resolution;
  $StartDate = '2020-01-06';
  $EndDate = '2022-01-06';
  $Prefix = $invoice->code;
  $From = $office->invoice_code_from;
  $To = $office->invoice_code_to;
  $companyNIT = '900711544';
  $companyNITDV = '0';
  $SoftwareID = 'feeb4367-88bb-400f-9dbb-0ee742608c3c';
  $ClTec = $office->technical_key;
  $pin = '48151';
  $AuthorizationProviderID = '800197268';

  $CustomizationID = '10';
  $ProfileExecutionID = '1';
  $ID = $Prefix.$invoice->invoice_number;

  $SoftwareSecurityCode = hash('sha384',$SoftwareID.$pin.$ID);
  $IssueDate  = $invoice->created_at->format('Y-m-d');
  $IssueTime = $invoice->created_at->format('h:s:i')."-05:00";
  $InvoiceTypeCode = "01";

  $InvoicePeriodStartDate = $invoice->created_at->startOfMonth()->toDateString(); 
  $InvoicePeriodEndDate =  $invoice->created_at->endOfMonth()->toDateString();
  $IndustryClasificationCode = '4761'; 
  $CompanyName = 'xxxx';
  $CompanyAddress = 'xxxx';
  $CompanyCity = 'Bogotá, D.C.';
  $CompanyDepto = 'Bogotá, D.C.';
  $CompanyDeptoCode = '11';
  $companyEmail = 'xxx';
  $TaxLevelCode = 'O-23';
  $TLClistName = '48';
  $cityCode = '11001';
  $TaxSchemeId = '01';
  $TaxSchemeName = 'IVA';

  $CustomerName = htmlspecialchars($user->name);
  $CustomerCityCode = '05042';
  $CustomerCity = htmlspecialchars($user->city);
  $CustomerDepto = htmlspecialchars($user->depto);
  $CustomerDeptoCode = '05';
  $CustomerAddress = htmlspecialchars($user->address);
  $CustomerNit = $user->personal_id;
  $CustomerEmail = htmlspecialchars($user->email);
  $CustomerIdCode = $user->type_of_id;
  $AdditionalAccountID = '2';

  $CustomerDV = null;
  if ($CustomerIdCode == '31') {
      $CustomerDV = $this->getDV($CustomerNit);
      $AdditionalAccountID = '1';
  };



  $PaymentMeansID = '1';
  $PaymentMeansCode = '10';

  $TaxableAmount = $invoice->taxable_base;
  $TaxAmount = $invoice->iva;
  $Percent = '19';
  $LineExtensionAmount = $invoice->subtotal;
  $TaxExclusiveAmount = $invoice->taxable_base;
  $TaxInclusiveAmount = $invoice->subtotal+$invoice->iva;
  $ChargeTotalAmount = $invoice->other_charges;
  $PayableAmount = $invoice->total;
  $OtherTaxes = '0.00';
  $LineCountNumeric = $items->count();
    
  $ClTec = $technical_key;  

  $UUID = $this->addCufe($invoice,$ClTec);

  $QRCode = "NumFac: $ID FecFac: $IssueDate HorFac: $IssueTime NitFac: $companyNIT DocAdq: $CustomerNit ValFac: $LineExtensionAmount ValIva: $TaxAmount ValOtroIm: $OtherTaxes ValTolFac: $PayableAmount CUFE: $UUID https://catalogo-vpfe.dian.gov.co/Document/FindDocument?documentKey=$UUID";

  $xmlHead = $this->formHeadXMl();

  $xmlExtensions = $this->formExtensionXMl($InvoiceAuthorization,$StartDate,$EndDate,$Prefix,$From,$To,$companyNIT,$SoftwareID,$AuthorizationProviderID,$QRCode,$companyNITDV,$SoftwareSecurityCode);

  $xmlVersion = $this->formVersionXMl($CustomizationID,$ProfileExecutionID,$ID,$UUID,$IssueDate,$IssueTime,$InvoiceTypeCode,$LineCountNumeric,$InvoicePeriodStartDate,$InvoicePeriodEndDate);

  $xmlCompany = $this->formCompanyXMl($CompanyName,$CompanyCity,$CompanyDepto,$CompanyDeptoCode,$CompanyAddress,$companyNIT,$TaxLevelCode,$cityCode,$TaxSchemeId,$TaxSchemeName,$companyNITDV,$TLClistName,$companyEmail,$Prefix);

  $xmlCustomer = $this->formCustomerXMl($AdditionalAccountID,$CustomerName,$CustomerCityCode,$CustomerCity,$CustomerDepto,$CustomerDeptoCode,$CustomerAddress,$CustomerNit,$CustomerIdCode,$CustomerDV,$CustomerEmail);

  $xmlTotal = $this->formTotalsXMl($PaymentMeansID,$PaymentMeansCode,$TaxableAmount,$Percent,$TaxAmount,$LineExtensionAmount,$TaxExclusiveAmount,$TaxInclusiveAmount,$PayableAmount,$ChargeTotalAmount);

  $xmlLines = $this->formLinesXMl($items);

  $xml = $xmlHead.$xmlExtensions.$xmlVersion.$xmlCompany.$xmlCustomer.$xmlTotal.$xmlLines;
}

private function addCufe($invoice,$ClTec){

  $ID = $invoice->code.$invoice->invoice_number;
  $IssueDate  = $invoice->created_at->format('Y-m-d');
  $IssueTime = $invoice->created_at->format('h:s:i')."-05:00";
  $codImp1 = '01';
  $ValImp1 =  $invoice->iva;
  $codImp2 = '04';
  $ValImp2 =  '0.00';
  $codImp3 = '03';
  $ValImp3 =  '0.00';
  $LineExtensionAmount = $invoice->subtotal;
  $PayableAmount = $invoice->total;
  $companyNIT = 'xxxxxxxxxx';
  $CustomerNit = $invoice->user->personal_id;
  $ProfileExecutionID = 1;

  $cufe = $ID.$IssueDate.$IssueTime.$LineExtensionAmount.$codImp1.$ValImp1.$codImp2.$ValImp2.$codImp3.$ValImp3.$PayableAmount.$companyNIT.$CustomerNit.$ClTec.$ProfileExecutionID;
  $cufe = hash('sha384',$cufe);        
  
  if ($cufe != $invoice->cufe) {
    $invoice->save();
  
  }
   
  return $cufe;

}

private function getDV($nit){
    $b = 11;
    $nit = strrev($nit); 
    $vpri = [3,7,13,17,19,23,29,37,41,43,47,53,59,67,71];
    $z = strlen($nit) ;
    $x = 0 ;
    for ($i=0; $i < $z; $i++) { 
      $y =  substr($nit, $i, 1);
      $n =  $vpri[$i];
      $x += ( $y * $n) ;
    }     
    $r = $x % $b;
    $dv = ( $r > 1 ) ? $b - $r : $r; 
    return $dv;
}

private function formLinesXMl($items){

  $string = "";
  
  foreach ($items as $key => $value) {

    $LineID = $key+1;
    $LineQty = $value->qty;
    $AllowanceChargeID = 1;
    $LineBaseAmount = $value->list_price;
    $AllowancePercentage = $value->discount;
    $LineAllowanceAmount = $value->discount_total;
    $LineTotal = $value->net_price_total;
    $LineTax = $value->iva_total;
    $LineTaxPercentage = $value->iva;
    $LineItemName =  htmlspecialchars($value->name);
    if ($LineTax > 0) {
      $TaxableAmount = $LineTotal;
    }else{
      $TaxableAmount = 0;
    }


    $string .= 
    "<cac:InvoiceLine> 
      <cbc:ID>$LineID</cbc:ID> 
      <cbc:InvoicedQuantity unitCode='EA'>$LineQty</cbc:InvoicedQuantity> 
      <cbc:LineExtensionAmount currencyID='COP'>$LineTotal</cbc:LineExtensionAmount>"; 
    
    if ($LineTotal == 0) {
      
      $total_list_price = $LineBaseAmount*$LineQty;
      if ($total_list_price == 0) {
        $total_list_price = 1;
      }
      $string .=  
      "<cac:PricingReference>
        <cac:AlternativeConditionPrice>
          <cbc:PriceAmount currencyID='COP'>$total_list_price</cbc:PriceAmount>
          <cbc:PriceTypeCode>01</cbc:PriceTypeCode>
        </cac:AlternativeConditionPrice>
      </cac:PricingReference>";
    }    

    $string .=  
    "<cac:AllowanceCharge> 
          <cbc:ID>$AllowanceChargeID</cbc:ID> 
          <cbc:ChargeIndicator>false</cbc:ChargeIndicator> 
          <cbc:MultiplierFactorNumeric>$AllowancePercentage</cbc:MultiplierFactorNumeric> 
          <cbc:Amount currencyID='COP'>$LineAllowanceAmount</cbc:Amount> 
          <cbc:BaseAmount currencyID='COP'>$LineBaseAmount</cbc:BaseAmount> 
        </cac:AllowanceCharge>
        <cac:TaxTotal> 
          <cbc:TaxAmount currencyID='COP'>$LineTax</cbc:TaxAmount> 
          <cac:TaxSubtotal> 
            <cbc:TaxableAmount currencyID='COP'>$TaxableAmount</cbc:TaxableAmount> 
            <cbc:TaxAmount currencyID='COP'>$LineTax</cbc:TaxAmount> 
            <cac:TaxCategory> 
              <cbc:Percent>$LineTaxPercentage</cbc:Percent> 
              <cac:TaxScheme> 
                <cbc:ID>01</cbc:ID> 
                <cbc:Name>IVA</cbc:Name> 
              </cac:TaxScheme> 
            </cac:TaxCategory> 
          </cac:TaxSubtotal> 
        </cac:TaxTotal> 
        <cac:Item> 
          <cbc:Description>$LineItemName</cbc:Description> 
          <cac:StandardItemIdentification>
           <cbc:ID schemeID='001' schemeName='UNSPSC' schemeAgencyID='10'>44120000</cbc:ID>
          </cac:StandardItemIdentification>
        </cac:Item> 
        <cac:Price> 
          <cbc:PriceAmount currencyID='COP'>$LineTotal</cbc:PriceAmount> 
          <cbc:BaseQuantity unitCode='EA'>$LineQty</cbc:BaseQuantity> 
        </cac:Price> 
      </cac:InvoiceLine>";

  
  }

  return $string."</Invoice>";

}

private function formTotalsXMl($PaymentMeansID,$PaymentMeansCode,$TaxableAmount,$Percent,$TaxAmount,$LineExtensionAmount,$TaxExclusiveAmount,$TaxInclusiveAmount,$PayableAmount,$ChargeTotalAmount){

  // $Percent = ($TaxAmount*100)/$TaxableAmount;
  $string = 
    "<cac:PaymentMeans> 
      <cbc:ID>$PaymentMeansID</cbc:ID> 
        <cbc:PaymentMeansCode>$PaymentMeansCode</cbc:PaymentMeansCode> 
      </cac:PaymentMeans>"; 
  
  if ($ChargeTotalAmount > 0 ){
   $string .=   
      "<cac:AllowanceCharge>
        <cbc:ID>1</cbc:ID>
        <cbc:ChargeIndicator>true</cbc:ChargeIndicator>  
        <cbc:AllowanceChargeReason>ENVIO - PROCESAMIENTO</cbc:AllowanceChargeReason>  
        <cbc:Amount currencyID='COP'>$ChargeTotalAmount</cbc:Amount> 
      </cac:AllowanceCharge>";
  }

  $string .=
      "<cac:TaxTotal> 
        <cbc:TaxAmount currencyID='COP'>$TaxAmount</cbc:TaxAmount> 
        <cac:TaxSubtotal> 
          <cbc:TaxableAmount currencyID='COP'>$TaxableAmount</cbc:TaxableAmount> 
          <cbc:TaxAmount currencyID='COP'>$TaxAmount</cbc:TaxAmount> 
          <cac:TaxCategory> 
            <cbc:Percent>$Percent</cbc:Percent> 
            <cac:TaxScheme> 
              <cbc:ID>01</cbc:ID> 
              <cbc:Name>IVA</cbc:Name> 
            </cac:TaxScheme> 
          </cac:TaxCategory> 
        </cac:TaxSubtotal> 
      </cac:TaxTotal> 
      <cac:LegalMonetaryTotal> 
        <cbc:LineExtensionAmount currencyID='COP'>$LineExtensionAmount</cbc:LineExtensionAmount> 
        <cbc:TaxExclusiveAmount currencyID='COP'>$TaxExclusiveAmount</cbc:TaxExclusiveAmount> 
        <cbc:TaxInclusiveAmount currencyID='COP'>$TaxInclusiveAmount</cbc:TaxInclusiveAmount> 
        <cbc:ChargeTotalAmount currencyID='COP'>$ChargeTotalAmount</cbc:ChargeTotalAmount> 
        <cbc:PayableAmount currencyID='COP'>$PayableAmount</cbc:PayableAmount> 
      </cac:LegalMonetaryTotal>";

  return $string;
}

private function formCustomerXMl($AdditionalAccountID,$CustomerName,$CustomerCityCode,$CustomerCity,$CustomerDepto,$CustomerDeptoCode,$CustomerAddress,$CustomerNit,$customerIdCode,$CustomerDV,$CustomerEmail){
    return   
    "<cac:AccountingCustomerParty>
      <cbc:AdditionalAccountID>$AdditionalAccountID</cbc:AdditionalAccountID>
          <cac:Party> 
              <cac:PartyIdentification>
                <cbc:ID schemeName='$customerIdCode'>
                  $CustomerNit
                </cbc:ID>
              </cac:PartyIdentification>
              <cac:PartyName> 
                  <cbc:Name>$CustomerName</cbc:Name>
              </cac:PartyName> 
              <cac:PhysicalLocation> 
                  <cac:Address> 
                      <cbc:ID>$CustomerCityCode</cbc:ID> 
                      <cbc:CityName>$CustomerCity</cbc:CityName> 
                      <cbc:CountrySubentity>$CustomerDepto</cbc:CountrySubentity> 
                      <cbc:CountrySubentityCode>$CustomerDeptoCode</cbc:CountrySubentityCode> 
                      <cac:AddressLine> 
                          <cbc:Line>$CustomerAddress</cbc:Line> 
                      </cac:AddressLine> 
                      <cac:Country> 
                          <cbc:IdentificationCode>CO</cbc:IdentificationCode> 
                          <cbc:Name languageID='es'>Colombia</cbc:Name> 
                      </cac:Country> 
                  </cac:Address> 
              </cac:PhysicalLocation> 
              <cac:PartyTaxScheme> 
                  <cbc:RegistrationName>$CustomerName</cbc:RegistrationName> 
                  <cbc:CompanyID schemeAgencyID='195' schemeAgencyName='CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)' schemeName='$customerIdCode' schemeID='$CustomerDV'>$CustomerNit</cbc:CompanyID> 
                  <cac:TaxScheme> 
                      <cbc:ID>ZY</cbc:ID> 
                      <cbc:Name>No Causa</cbc:Name> 
                  </cac:TaxScheme> 
              </cac:PartyTaxScheme> 
              <cac:PartyLegalEntity> 
                  <cbc:RegistrationName>$CustomerName</cbc:RegistrationName> 
                  <cbc:CompanyID schemeAgencyID='195' schemeAgencyName='CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)' schemeID='$CustomerDV' schemeName='$customerIdCode'>$CustomerNit</cbc:CompanyID>
              </cac:PartyLegalEntity> 
              <cac:Contact>
                  <cbc:ElectronicMail>
                      $CustomerEmail
                  </cbc:ElectronicMail>
              </cac:Contact>
          </cac:Party> 
      </cac:AccountingCustomerParty>";

}
private function formCompanyXMl($CompanyName,$CompanyCity,$CompanyDepto,$CompanyDeptoCode,$CompanyAddress,$companyNIT,$TaxLevelCode,$cityCode,$TaxSchemeId,$TaxSchemeName,$companyNITDV,$TLClistName,$companyEmail,$Prefix){

   $string = 
   "<cac:AccountingSupplierParty> 
      <cbc:AdditionalAccountID>1</cbc:AdditionalAccountID> 
      <cac:Party> 
          <cac:PartyName> 
              <cbc:Name>$CompanyName</cbc:Name> 
          </cac:PartyName>
          <cac:PhysicalLocation>
              <cac:Address>
                  <cbc:ID>$cityCode</cbc:ID>
                  <cbc:CityName>$CompanyCity</cbc:CityName>
                  <cbc:CountrySubentity>$CompanyDepto</cbc:CountrySubentity>
                  <cbc:CountrySubentityCode>$CompanyDeptoCode</cbc:CountrySubentityCode>
                  <cac:AddressLine>
                      <cbc:Line>$CompanyAddress</cbc:Line>
                  </cac:AddressLine>
                  <cac:Country>
                      <cbc:IdentificationCode>CO</cbc:IdentificationCode>
                      <cbc:Name languageID='es'>Colombia</cbc:Name>
                  </cac:Country>
              </cac:Address>
          </cac:PhysicalLocation> 
          <cac:PartyTaxScheme> 
              <cbc:RegistrationName>$CompanyName</cbc:RegistrationName> 
              <cbc:CompanyID schemeAgencyID='195' schemeAgencyName='CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)' schemeID='$companyNITDV' schemeName='31'>$companyNIT</cbc:CompanyID> 
              <cbc:TaxLevelCode listName='$TLClistName'>$TaxLevelCode</cbc:TaxLevelCode> 
              <cac:RegistrationAddress> 
                  <cbc:ID>$cityCode</cbc:ID> 
                  <cbc:CityName>$CompanyCity</cbc:CityName> 
                  <cbc:CountrySubentity>$CompanyDepto</cbc:CountrySubentity> 
                  <cbc:CountrySubentityCode>$CompanyDeptoCode</cbc:CountrySubentityCode> 
                  <cac:AddressLine> 
                      <cbc:Line>$CompanyAddress</cbc:Line> 
                  </cac:AddressLine> 
                  <cac:Country> 
                      <cbc:IdentificationCode>CO</cbc:IdentificationCode> 
                      <cbc:Name languageID='es'>Colombia</cbc:Name> 
                  </cac:Country> 
              </cac:RegistrationAddress> 
              <cac:TaxScheme> 
                  <cbc:ID>$TaxSchemeId</cbc:ID> 
                  <cbc:Name>$TaxSchemeName</cbc:Name> 
              </cac:TaxScheme> 
          </cac:PartyTaxScheme> 
          <cac:PartyLegalEntity> 
              <cbc:RegistrationName>$CompanyName</cbc:RegistrationName> 
              <cbc:CompanyID schemeAgencyID='195' schemeAgencyName='CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)' schemeID='$companyNITDV' schemeName='31'>$companyNIT</cbc:CompanyID>
              <cac:CorporateRegistrationScheme>
                <cbc:ID>$Prefix</cbc:ID>
              </cac:CorporateRegistrationScheme> 
          </cac:PartyLegalEntity> 
          <cac:Contact>
              <cbc:ElectronicMail>$companyEmail</cbc:ElectronicMail>
          </cac:Contact>
      </cac:Party> 
  </cac:AccountingSupplierParty>";
   return $string;
}

private function formVersionXMl($CustomizationID,$ProfileExecutionID,$ID,$UUID,$IssueDate,$IssueTime,$InvoiceTypeCode,$LineCountNumeric,$InvoicePeriodStartDate,$InvoicePeriodEndDate){
  $string = "<cbc:UBLVersionID>UBL 2.1</cbc:UBLVersionID> <cbc:CustomizationID>$CustomizationID</cbc:CustomizationID> <cbc:ProfileID>DIAN 2.1</cbc:ProfileID> <cbc:ProfileExecutionID>$ProfileExecutionID</cbc:ProfileExecutionID> <cbc:ID>$ID</cbc:ID> <cbc:UUID schemeID='$ProfileExecutionID' schemeName='CUFE-SHA384'>$UUID</cbc:UUID> <cbc:IssueDate>$IssueDate</cbc:IssueDate> <cbc:IssueTime>$IssueTime</cbc:IssueTime> <cbc:InvoiceTypeCode>$InvoiceTypeCode</cbc:InvoiceTypeCode> <cbc:DocumentCurrencyCode listAgencyID='6' listAgencyName='United Nations Economic Commission for Europe' listID='ISO 4217 Alpha'>COP</cbc:DocumentCurrencyCode> <cbc:LineCountNumeric>$LineCountNumeric</cbc:LineCountNumeric> <cac:InvoicePeriod> <cbc:StartDate>$InvoicePeriodStartDate</cbc:StartDate> <cbc:EndDate>$InvoicePeriodEndDate</cbc:EndDate> </cac:InvoicePeriod>";
  return $string;
}

private function formExtensionXMl($InvoiceAuthorization,$StartDate,$EndDate,$Prefix,$From,$To,$companyNIT,$SoftwareID,$AuthorizationProviderID,$QRCode,$companyNITDV,$SoftwareSecurityCode){
  return 
  "<ext:UBLExtensions> 
      <ext:UBLExtension> 
          <ext:ExtensionContent> 
              <sts:DianExtensions> 
                  <sts:InvoiceControl> 
                      <sts:InvoiceAuthorization>$InvoiceAuthorization</sts:InvoiceAuthorization> 
                      <sts:AuthorizationPeriod> 
                          <cbc:StartDate>$StartDate</cbc:StartDate> 
                          <cbc:EndDate>$EndDate</cbc:EndDate> 
                      </sts:AuthorizationPeriod> 
                      <sts:AuthorizedInvoices> 
                          <sts:Prefix>$Prefix</sts:Prefix> 
                          <sts:From>$From</sts:From> 
                          <sts:To>$To</sts:To> 
                      </sts:AuthorizedInvoices> 
                  </sts:InvoiceControl> 
                  <sts:InvoiceSource> 
                      <cbc:IdentificationCode listAgencyID='6' listAgencyName='United Nations Economic Commission for Europe' listSchemeURI='urn:oasis:names:specification:ubl:codelist:gc:CountryIdentificationCode-2.1'>CO</cbc:IdentificationCode> 
                  </sts:InvoiceSource> 
                  <sts:SoftwareProvider> 
                      <sts:ProviderID schemeAgencyID='195' schemeAgencyName='CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)' schemeID='$companyNITDV' schemeName='31'>$companyNIT</sts:ProviderID> 
                      <sts:SoftwareID schemeAgencyID='195' schemeAgencyName='CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)'>$SoftwareID</sts:SoftwareID> 
                  </sts:SoftwareProvider> 
                  <sts:SoftwareSecurityCode schemeAgencyID='195' schemeAgencyName='CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)'>$SoftwareSecurityCode</sts:SoftwareSecurityCode> 
                  <sts:AuthorizationProvider> 
                      <sts:AuthorizationProviderID schemeAgencyID='195' schemeAgencyName='CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)' schemeID='4' schemeName='31'>$AuthorizationProviderID</sts:AuthorizationProviderID> 
                      </sts:AuthorizationProvider> 
                      <sts:QRCode> $QRCode </sts:QRCode> 
              </sts:DianExtensions> 
          </ext:ExtensionContent> 
      </ext:UBLExtension> 
      <ext:UBLExtension> <ext:ExtensionContent></ext:ExtensionContent> </ext:UBLExtension> </ext:UBLExtensions>";
}

private function formHeadXMl(){
  $string = "<?xml version='1.0' encoding='UTF-8' standalone='no'?><Invoice xmlns='urn:oasis:names:specification:ubl:schema:xsd:Invoice-2' xmlns:cac='urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2' xmlns:cbc='urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2' xmlns:ds='http://www.w3.org/2000/09/xmldsig#' xmlns:ext='urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2' xmlns:sts='http://www.dian.gov.co/contratos/facturaelectronica/v1/Structures' xmlns:xades='http://uri.etsi.org/01903/v1.3.2#' xmlns:xades141='http://uri.etsi.org/01903/v1.4.1#' xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance' xsi:schemaLocation='urn:oasis:names:specification:ubl:schema:xsd:Invoice-2 http://docs.oasis-open.org/ubl/os-UBL-2.1/xsd/maindoc/UBL-Invoice-2.1.xsd'>";
  return $string;
} 