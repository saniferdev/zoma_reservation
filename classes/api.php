<?php

Class API{

    public $url;
    public $key;
    public $link;

    public $url_soap;
    public $login;
    public $password;

    public $codeLang;
    public $poolAlias;

    public function getId($id){
        $queryParams    = array();
        $queryOptions   = array("Scrollable" => SQLSRV_CURSOR_KEYSET);
        $query          = "SELECT commande FROM [COMMANDE_ZOMA].[dbo].[commande_en_ligne] WHERE commande = ".$id." ";

        $resultat       = sqlsrv_query($this->link, $query, $queryParams, $queryOptions);
        if ($resultat == FALSE) {
            return false;
        } elseif (sqlsrv_num_rows($resultat) == 0) {
            return 0;
        } else {
            return 1;
        }
    }

    public function getUnite($id){
        $queryParams    = array();
        $queryOptions   = array("Scrollable" => SQLSRV_CURSOR_KEYSET);
        $query          = "SELECT SAU_0 FROM [192.168.130.50\TALYS].[x3v12prod].dbo.[ZARTICLES] WHERE ITMREF_0 = '".$id."'";

        $result = sqlsrv_query($this->link, $query, $queryParams, $queryOptions);
        if ($result == FALSE){
          return false;
        }
        elseif (sqlsrv_num_rows($result) == 0) {
          return false;
        }
        else
        {
          $row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
          $unite = $row['SAU_0'];
          return $unite;
        }
    }

    public function insertId($id){
        $query  = "INSERT INTO [COMMANDE_ZOMA].[dbo].[commande_en_ligne] (commande) VALUES (".$id.") ";
        if(sqlsrv_query($this->link, $query)) return true;
        else return var_dump(sqlsrv_errors());
    }

    public function preg_replace_($xml){
        $xml = preg_replace("/\n/", "", $xml);
        $xml = preg_replace("/>\s*</", "><", $xml);
        return $xml;
    }

    public function soap_API($publicName,$inputXml){
        $soapClient = new SoapClient(
            $this->url_soap,
            array(
                'trace'    => true,
                'login'    => $this->login,
                'password' => $this->password,
            )
        );

        $context    = array('codeLang'=>$this->codeLang, 'poolAlias'=>$this->poolAlias,'poolId'=>'','requestConfig'=>'adxwss.trace.on=on&adxwss.trace.size=16384&adonix.trace.on=on&adonix.trace.level=3&adonix.trace.size=8');

        $inputXml   = $this->preg_replace_($inputXml);

        $result     = $soapClient->__call("run",array($context,$publicName,$inputXml));
        $xml        = simplexml_load_string($result->resultXml);
        //$status     = (int)$result->status;

        if($publicName == 'YPANIER' || $publicName == 'YPANIER2'){
            $article    = $xml->TAB[1];
            $lin        = $article->LIN;
            $arr        = array();
            foreach ($lin as $key => $value) {
                $arr[]  = $value['NUM'] ."-".$value->FLD[0];
            }
            $return     = $arr;
        }
        else{
            $message   = $xml->GRP[1]->FLD[0];
            $return    = trim($message);
        }

        return $return;
    }

    public function deleteCommandeId($id){
        $query  = "DELETE FROM [COMMANDE].[dbo].[commande_en_ligne] WHERE commande = '".$id."' ";
        if(sqlsrv_query($this->link, $query)) return true;
        else return var_dump(sqlsrv_errors());
    }

    public function entete($client,$adresse,$tel,$email,$id,$supplier){
        $mode_paiement  =   $this->getOrderPaiement($id);
        if($supplier == '3'){
            $YBPCORD    = 'P0100001';
            $YSALFCY    = 'SAN01';
            $YSTOFCY    = 'SAN01';
        }
        if($supplier == '4'){
            $YBPCORD    = 'P01K0001';
            $YSALFCY    = 'KIB01';
            $YSTOFCY    = 'KIB01';
        }
        $array          =   array(
                                'Airtelmoney' => 'ESPJN',
                                'Chèque' => 'ESPJN',
                                'en_especes' => 'ESPJN',
                                'MVola' => 'ESPJN',
                                'Orangemoney' => 'ESPJN',
                                'Payer par carte Visa/Mastercard' => 'VIR100CDE',
                                'Paiement en magasin' => 'ESPJN',
                                'Paiement par carte VISA / MASTERCARD' => 'VIR100CDE',
                                'Paiement par carte VISA / MASTER' => 'VIR100CDE',
                                'mise_en_compte' => 'VIR100CDE',
                                'Carte de crédit' => 'VIR100CDE',
                                'Paiement par carte VISA / MASTERCARD : Visa' => 'VIR100CDE',
                                'en_especes' => 'ESPJN',
                                'En especes au livreur' => 'ESPJN',
                                'CybersourceOfficial' => 'ESPJN',
                                'Transfert bancaire' => 'VIR100CDE'
                            );
        $YPTE           =   $array[$mode_paiement];
        $xml            =   '<GRP ID="IN">
                                <FLD NAM="YSALFCY">'.$YSALFCY.'</FLD>
                                <FLD NAM="YSTOFCY">'.$YSTOFCY.'</FLD>
                                <FLD NAM="YBPCORD">'.$YBPCORD.'</FLD>
                                <FLD NAM="YPTE">'.$YPTE.'</FLD>
                                <FLD NAM="YBPCNAM1">ZOMA - '.$client.'</FLD>
                                <FLD NAM="YBPCNAM2"></FLD>
                                <FLD NAM="YBPCADDLIG">'.$adresse.'</FLD>
                                <FLD NAM="YBPCADDLIG2">'.$tel.'</FLD>
                                <FLD NAM="YBPCADDLIG3">'.$email.'</FLD>
                                <FLD NAM="YBPCPOSCOD"></FLD>
                                <FLD NAM="YBPCCRY"></FLD>
                                <FLD NAM="YBPCCTY">MG</FLD>
                                <FLD NAM="YBPDNAM">'.$client.'</FLD>
                                <FLD NAM="YBPDNAM2"></FLD>
                                <FLD NAM="YBPDADDLIG">'.$adresse.'</FLD>
                                <FLD NAM="YBPDADDLIG2">'.$tel.'</FLD>
                                <FLD NAM="YBPDADDLIG3">'.$email.'</FLD>
                                <FLD NAM="YBPDPOSCOD"></FLD>
                                <FLD NAM="YBPDCTY"></FLD>
                                <FLD NAM="YBPDCRY">MG</FLD>
                              </GRP>';
        $xml            = $this->preg_replace_($xml);
        return $xml;
    }

    public function ligne($article,$qte,$unite,$nb_ligne){
        $xml    =   '<LIN ID="IND" NUM="'.$nb_ligne.'">
                        <FLD NAM="YITMREF">'.$article.'</FLD>
                        <FLD NAM="YSAU">'.$unite.'</FLD>
                        <FLD NAM="YQTY">'.$qte.'</FLD>
                    </LIN>';
        $xml    = $this->preg_replace_($xml);
        return $xml;
    }

    public function entete_qte($supplier){
        $YSTOFCY = ($supplier == '3') ? 'SAN01' : 'KIB01';
        $xml     =   '<GRP ID="IN">
                        <FLD NAM="YSTOFCY">'.$YSTOFCY.'</FLD>
                     </GRP>';
        $xml     = $this->preg_replace_($xml);
        return $xml;
    }

    public function xml2array($fname){
      $sxi      = new SimpleXmlIterator($fname);
      return $this->sxiToArray($sxi);
    }

    public function sxiToArray($sxi){
      $a = array();
      for( $sxi->rewind(); $sxi->valid(); $sxi->next() ) {
        if(!array_key_exists($sxi->key(), $a)){
          $a[$sxi->key()]   = array();
        }
        if($sxi->hasChildren()){
          $a[$sxi->key()][] = $this->sxiToArray($sxi->current());
        }
        else{
          $a[$sxi->key()][] = strval($sxi->current());
        }
      }
      return $a;
    }

    public function getClientDetail($id){
        $xml        = file_get_contents($this->url."/api/customers/".$id."/?ws_key=".$this->key);
        $getContent = $this->xml2array($xml);
        $iterator   = new RecursiveArrayIterator($getContent);
        $client     = "";
        while ($iterator->valid()) {
            if ($iterator->hasChildren()) {
                foreach ($iterator->getChildren() as $value) {
                    $client = strtoupper($value["lastname"][0]).'  '.strtoupper($value["firstname"][0]).'-'.$value["email"][0];
                }
            }
            $iterator->next();
        }
        return $client;
    }

    public function getClientAdress($id){
        $xml        = file_get_contents($this->url."/api/addresses/".$id."/?ws_key=".$this->key);
        $getContent = $this->xml2array($xml);
        $iterator   = new RecursiveArrayIterator($getContent);
        $client     = "";
        while ($iterator->valid()) {
            if ($iterator->hasChildren()) {
                foreach ($iterator->getChildren() as $value) {
                    $client = strtoupper($value["address1"][0]).'-'.$value["phone"][0];
                }
            }
            $iterator->next();
        }
        return $client;
    }


    public function qteArticle($num,$qte,$num2,$qte2){
        if($num == $num2 && $qte == $qte2) 
            return true;
        else 
            return false;
    }

    public function getOrderDetail($id){
        $xml        = file_get_contents($this->url."/api/orders/".$id."/?ws_key=".$this->key);
        $getContent = $this->xml2array($xml);
        $array      = array();
        $iterator   = new RecursiveArrayIterator($getContent);

        while ($iterator->valid()) {
            if ($iterator->hasChildren()) {
                foreach ($iterator->getChildren() as $value) {
                    $client  = $this->getClientDetail($value["id_customer"][0]);
                    $adresse = $this->getClientAdress($value["id_address_invoice"][0]);
                    foreach(end($value["associations"]) as $val){
                        foreach($val[0]["order_row"] as $key => $valeur){
                            $array[]['article'] = $valeur['product_reference'][0].'-'.$valeur['product_quantity'][0].'-'.$client.'-'.$adresse.'-'.$valeur['product_id'][0];
                        }
                    }
                }
            }
            $iterator->next();
        }
        return $array;
    }

    public function getProduitSuppliers($id){
        $xml        = file_get_contents($this->url."/api/products/".$id."/?ws_key=".$this->key);
        $getContent = $this->xml2array($xml);
        $iterator   = new RecursiveArrayIterator($getContent);
        $supplier   = 0;
        while ($iterator->valid()) {
            if ($iterator->hasChildren()) {
                foreach ($iterator->getChildren() as $value) {
                    $supplier = $value["id_supplier"][0];
                }
            }
            $iterator->next();
        }
        return $supplier;
    }

    public function getOrderPaiement($id){
        $xml        = file_get_contents($this->url."/api/orders/".$id."/?ws_key=".$this->key);
        $getContent = $this->xml2array($xml);
        $array      = array();
        $iterator   = new RecursiveArrayIterator($getContent);
        $payment    = '';

        while ($iterator->valid()) {
            if ($iterator->hasChildren()) {
                foreach ($iterator->getChildren() as $value) {
                    $payment  = $value["payment"][0];
                }
            }
            $iterator->next();
        }
        
        return $payment;
    }

    public function getOrder(){
        $rand        = 0;
        $id_commande = 1;
        $date_       = $xml = $getXml = $orders = $id_commande = $lettre = '';
        $lettre      = substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 5)), 0, 5);
        $date_       = new DateTime('now');
        $date_       = $date_->getTimestamp();
        $rand        = $lettre.''.($date_ * rand());
        $xml         = file_get_contents($this->url."/api/orders?output_format=".$rand."&ws_key=".$this->key);
        $getXml     = simplexml_load_string($xml,'SimpleXMLElement', LIBXML_COMPACT | LIBXML_PARSEHUGE);

        if($getXml){
            $orders = $getXml->orders;
            foreach($orders as $val) {
                foreach ($val as $value) {
                    $id_commande = $this->getId($value["id"]);
                    if( $id_commande == 0 ){
                        if($this->insertId($value["id"]) == TRUE){
                            $array_s  = $array_k = array();
                            $i                = 0;
                            $e  = $e_k = $supplier = $art = "";
                            $detail           = $this->getOrderDetail($value["id"]);

                            $xml_Ref          = '<PARAM>
                                                      <GRP ID="IN">
                                                        <FLD NAM="YSTOFCY">SAN01</FLD>
                                                      </GRP>';
                            $xml_Ref_k        = '<PARAM>
                                                      <GRP ID="IN">
                                                        <FLD NAM="YSTOFCY">KIB01</FLD>
                                                      </GRP>';

                            foreach ($detail as $key => $article) {
                                $art = $article['article'];
                                        if(isset($art) && !empty($art)){
                                            $exp        = explode('-', $art);
                                            $supplier   = $this->getProduitSuppliers($exp[6]);
                                            if( $supplier == '3'){
                                                $array_s[] =  $art;
                                            }
                                            if( $supplier == '4'){
                                                $array_k[] = $art;
                                            }
                                        }
                            }  

                            if (is_array($array_s) || is_object($array_s)) {
                                foreach($array_s as $val){
                                            $exp    = explode('-', $val);
                                            $supplier   = $this->getProduitSuppliers($exp[6]);
                                            $qteXml = TRUE;
                                            if($i == 0){

                                                $entete   = $this->entete($exp[2],$exp[4],$exp[5],$exp[3],$value["id"],$supplier);
                                                $ligne    = '<TAB ID="IND">';
                                                $xml_Ref .= '<TAB ID="IND">';
                                                $xml_Ref .= $this->entete_qte($supplier);
                                            }
                                            $i++;

                                            $unite      = $this->getUnite($exp[0]);

                                            $ligne      .= $this->ligne($exp[0],$exp[1],$unite,$i);

                                            $xml_Ref    .= $this->ligne($exp[0],$exp[1],$unite,$i);

                                            if ($val === end($array_s)){
                                                $ligne   .= '</TAB>';
                                                $xml_Ref .= '</TAB>
                                                            </PARAM>';
                                                $i = 0;
                                            }

                                            $e .= '<strong>Réference :</strong> '.$exp[0].' - <strong>Quantité:</strong> '.$exp[1].'<br>';
                                }
                                if(isset($entete) && isset($ligne)){
                                    $xml_soap    = '<PARAM>';
                                    $xml_soap   .= $entete.$ligne;
                                    $xml_soap   .='</PARAM>';

                                    $commande    = $this->soap_API('YGENSOH',$xml_soap);

                                    if(isset($commande) && !empty($commande)) {
                                        $adresse = "ass2.scecomm@sanifer.mg";
                                        $sujet  = "Commande validée sur ZOMA et crée dans X3";
                                        $objet  = "Bonjour,<br><br>";
                                        $objet .= "Une commande n° ".$value["id"]." a été crée sur le site WEB de ZOMA.<br>";
                                        $objet .= "La commande a été crée dans sage X3.<br><br>";
                                        $objet .= "Ci-après les details de la commande:<br>";
                                        $objet .= "<strong>N° :</strong>".$commande." - <strong>Client Divers :</strong> ".$exp[2]."<br><br>";
                                        $objet .= $e."<br><br>";
                                        $objet .= "   <strong>Cordialement</strong><br>
                                                      <strong>Winny Tsiorintsoa RAZAFINDRAKOTO</strong><br>
                                                      <strong>DEVELOPPEUR</strong><br>
                                                      Lot II I 20 AA Morarano<br>
                                                      Antananarivo – MADAGASCAR<br>
                                                      Tél. : +261 34 07 635 84<br>
                                                      Tél. : +261 20 22 530 81<br>
                                                      Fax : +261 20 22 530 80<br>
                                                      Mail : winny.info@talys.mg<br> 
                                                      Site : www.sanifer.mg<br>";
                                        envoiMail($adresse,$sujet,$objet);
                                    }
                                }
                            }
                            $i = 0;
                            $supplier = "";
                            if (is_array($array_k) || is_object($array_k)){
                                foreach($array_k as $val_k){
                                            $exp_k    = explode('-', $val_k);
                                            $supplier   = $this->getProduitSuppliers($exp_k[6]);
                                            $qteXml = TRUE;
                                            if($i == 0){

                                                $entete_k   = $this->entete($exp_k[2],$exp_k[4],$exp_k[5],$exp_k[3],$value["id"],$supplier);
                                                $ligne_k    = '<TAB ID="IND">';
                                                $xml_Ref_k .= '<TAB ID="IND">';
                                                $xml_Ref_k .= $this->entete_qte($supplier);
                                            }
                                            $i++;

                                            $unite_k      = $this->getUnite($exp_k[0]);

                                            $ligne_k      .= $this->ligne($exp_k[0],$exp_k[1],$unite_k,$i);

                                            $xml_Ref_k    .= $this->ligne($exp_k[0],$exp_k[1],$unite_k,$i);

                                            if ($val_k === end($array_k)){
                                                $ligne_k   .= '</TAB>';
                                                $xml_Ref_k .= '</TAB>
                                                            </PARAM>';
                                                $i = 0;
                                            }

                                            $e_k .= '<strong>Réference :</strong> '.$exp_k[0].' - <strong>Quantité:</strong> '.$exp_k[1].'<br>';
                                }
                                if(isset($entete_k) && isset($ligne_k)){
                                    $xml_soap_k    = '<PARAM>';
                                    $xml_soap_k   .= $entete_k.$ligne_k;
                                    $xml_soap_k   .='</PARAM>';
                                    
                                    $commande_k    = $this->soap_API('YGENSOH',$xml_soap_k);

                                    if(isset($commande_k) && !empty($commande_k)) {
                                        $inn_k = $this->insertId($value["id"]);                                           
                                            $adresse_k = "Commande.Kibo@kibo.mg";
                                            $sujet_k  = "Commande validée sur ZOMA et crée dans X3";
                                            $objet_k  = "Bonjour,<br><br>";
                                            $objet_k .= "Une commande n° ".$value["id"]." a été crée sur le site WEB de ZOMA.<br>";
                                            $objet_k .= "La commande a été crée dans sage X3.<br><br>";
                                            $objet_k .= "Ci-après les details de la commande:<br>";
                                            $objet_k .= "<strong>N° :</strong>".$commande_k." - <strong>Client Divers :</strong> ".$exp_k[2]."<br><br>";
                                            $objet_k .= $e_k."<br><br>";
                                            $objet_k .= "   <strong>Cordialement</strong><br>
                                                          <strong>Winny Tsiorintsoa RAZAFINDRAKOTO</strong><br>
                                                          <strong>DEVELOPPEUR</strong><br>
                                                          Lot II I 20 AA Morarano<br>
                                                          Antananarivo – MADAGASCAR<br>
                                                          Tél. : +261 34 07 635 84<br>
                                                          Tél. : +261 20 22 530 81<br>
                                                          Fax : +261 20 22 530 80<br>
                                                          Mail : winny.info@talys.mg<br> 
                                                          Site : www.kibo.mg<br>";
                                                          
                                            
                                            envoiMailKibo($adresse_k,$sujet_k,$objet_k);
                                    }
                                }    
                            }
                        }
                    }
                }
            }
        }
    }

}
?>