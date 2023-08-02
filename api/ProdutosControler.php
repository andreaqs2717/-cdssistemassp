<?php namespace App\Controllers;

use PDO;

class ProdutosControler extends BaseController {
    function index() {
        $sucesso = $this->inserirCategoriasApi();
        $sucesso = $this->inserirProdutosApi();
        $sucesso = $this->atualizarProdutosApi();
        //$sucesso = $this->getProdutosApi();
    }
    
    function conectar() {
      try 
      {  
        //$servidor = 'DESKTOP-EA31HPF\SQLEXPRESS2014';
        //$banco = 'BDComercialHAMundial';
        //$usuario = 'cdsweb';
        //$senha = 'dificil!@#';  
        $servidor = '74.63.213.142,4502';
        $banco = 'MZKSBDComercialPontoHomeEsc';
        $usuario = 'mzks42405093000184';
        $senha = 'wZNS41W4';  
      
        $pdoConfig  = "sqlsrv:". "Server=" . $servidor . ";";
        $pdoConfig .= "Database=".$banco.";";
        $conn = new PDO($pdoConfig, $usuario, $senha);
      
        //$connectionInfo = array( "Database"=>$banco, "UID"=>$usuario, "PWD"=>$senha);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      
        //$conn = sqlsrv_connect( $servidor, $connectionInfo); 
      
        //$servidor  =  "sqlsrv:Server=74.63.213.142,4502;";
        //$servidor = $servidor . "dbname=MZKSBDComercialPontoHomeEsc";
        //$conn =  new PDO($servidor, $usuario, $senha);
        //$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        //if (!$conn) 
        // die(print_r (sqlsrv_errors (),true));
     return $conn;
     } 
     catch (PDOException $e) 
     {
      echo "Erro na conexÃ£o: " . $e->getMessage();
     }
     
    }  
    
    function getChavesAcesso() { 
      
        //return '205fd439a4de0bc0142f';
        //Ponto Home
        return '26ba28d95a6f128cbe05';

    }   
    
   function getProdutosApi() 
   {
     try 
     {
         
       //$url= "https://api.awsli.com.br/v1/produto";  
       $url= "https://api.awsli.com.br/v1/produto?limit=20&offset=0";
       $ch = curl_init($url);
       curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       $ChaveAcesso = $this->getChavesAcesso();
       curl_setopt($ch,CURLOPT_HTTPHEADER,array('Authorization: chave_api ' . $ChaveAcesso . ' aplicacao 5a432d57-8f89-4cc6-b35d-6bfc90934239'));
       $response = curl_exec($ch);
       
       $listProdutos = json_decode($response);
       //var_dump($listProdutos);
       $numeroProdutos = $listProdutos->meta->total_count;
       $numeroProdutos = intval($numeroProdutos/20) + 1;
       //echo $numeroProdutos;
       $offset = 0;
       for ($i = 1; $i <= $numeroProdutos; $i++) 
       {
         $url= "https://api.awsli.com.br/v1/produto?limit=20&offset=" . $offset;
         $ch = curl_init($url);
         curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         $ChaveAcesso = $this->getChavesAcesso();
         curl_setopt($ch,CURLOPT_HTTPHEADER,array('Authorization: chave_api ' . $ChaveAcesso . ' aplicacao 5a432d57-8f89-4cc6-b35d-6bfc90934239'));
         $response = curl_exec($ch);
         $listProdutos = json_decode($response);
         foreach ($listProdutos->objects as $produto)
         {
           ECHO "Descricao: " . $produto->nome . " SKU " . $produto->sku . "<BR>";   
          }
         $offset  = $offset + 20; 
       }
       //foreach ($listProdutos->objects as $produto) 
      //{
      //   ECHO "Descricao: " . $produto->nome . " SKU " . $produto->sku . "<BR>";
      // }
     } 
     catch (Exception $ex) 
     {
       echo $ex->getMessage();
     }
   }
   
   function getProdutosEspecificoApi($codigo) 
   {
     try 
     {
       $url= "https://api.awsli.com.br/v1/produto/" . $codigo;   
       $ch = curl_init($url);
       curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       $ChaveAcesso = $this->getChavesAcesso();
       curl_setopt($ch,CURLOPT_HTTPHEADER,array('Authorization: chave_api ' . $ChaveAcesso . ' aplicacao 5a432d57-8f89-4cc6-b35d-6bfc90934239'));
       
       $response = curl_exec($ch);
      
       $listProdutos = json_decode($response);
       //ECHO "Descricao: " . $listProdutos->nome . "<BR>";
       return $listProdutos;
       //var_dump($listProdutos);

     } 
     catch (Exception $ex) 
     {
       echo $ex->getMessage();
     }
   }
   
  function inserirCategoriasApi() 
  {
    $url= "https://api.awsli.com.br/v1/categoria/";  
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_ENCODING, '');
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    $ChaveAcesso = $this->getChavesAcesso();
    curl_setopt($ch,CURLOPT_HTTPHEADER,array('Authorization: chave_api ' . $ChaveAcesso . ' aplicacao 5a432d57-8f89-4cc6-b35d-6bfc90934239','Content-Type: application/json'));
    
   //pega os produtos que ainda nao estão no e-commerce

   $ConnConsulta = $this->conectar();
   $sql          = "select grupo.grupo as grupo from grupo";
   $sql = $sql . " inner join produto on produto.grupo=grupo.grupo";
   $sql = $sql . " where lojaVirtual='1' and (idtray is null or idtray='') and (grupo.idCategoria is null or grupo.idCategoria='') ";
   $sql = $sql . " group by grupo.grupo";
   //$stmt = sqlsrv_query( $ConnConsulta, $sql );  
   $stmt = $ConnConsulta->prepare($sql);
   $stmt->execute();
   
   if ($stmt===false) {
     echo 'Erro';
   }
   $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
   foreach ($results as $row)
   //while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) 

   {
 
     //$dados['id_externo']    = "";
     $dados['nome']          = trim($row['grupo']);
     //$dados['descricao']     = "dea";
     //$dados['categoria_pai'] = "";

     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
     
     $response = curl_exec($ch);
     if ($err = curl_error($ch)) 
     {
       ECHO 'Erro ' . $err;
     } 
     else 
     {
       $resposta = json_decode($response);

        //insere o id da categoria na tabela grupo
        $sql  = "UPDATE grupo SET idCategoria='" . $resposta->id . "'";
        $sql = $sql . " WHERE grupo='" . trim($row['grupo']) . "'";
        
        $stmt1 = $ConnConsulta->prepare($sql);
        $stmt1->execute();
        //$stmt1 = sqlsrv_query( $ConnConsulta, $sql ); 
        if ($stmt1===false) {
          echo 'Erro';
        }
        //insere o id da categoria na tabela de produto
        $sql  = "UPDATE produto SET idCategoria='" . $resposta->id . "'";
        $sql = $sql . " WHERE grupo='" . trim($row['grupo']) . "'";
        //echo $sql;
        $stmt2 = $ConnConsulta->prepare($sql);
        $stmt2->execute();
        //$stmt2 = sqlsrv_query( $ConnConsulta, $sql ); 
        if ($stmt2===false) {
          echo 'Erro';
        }
       //var_dump($resposta);
 
     }  
     
   }
   $ConnConsulta = null;     
   //sqlsrv_free_stmt($stmt);
   //sqlsrv_close($ConnConsulta);  
    
  }
         
   function alterarPrecoApi($id,$row1){
        
        $url1= "https://api.awsli.com.br/v1/produto_preco/" . $id;  
        $ch1 = curl_init($url1);

        curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch1, CURLOPT_ENCODING, '');
        curl_setopt($ch1, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch1, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch1, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, 'PUT');
        $ChaveAcesso = $this->getChavesAcesso();
        curl_setopt($ch1,CURLOPT_HTTPHEADER,array('Authorization: chave_api ' . $ChaveAcesso . ' aplicacao 5a432d57-8f89-4cc6-b35d-6bfc90934239','Content-Type: application/json'));
 
        $dados2['cheio']      = trim($row1['vendaPrc']);
        $dados2['custo']      = trim($row1['compraPrc']); 
        
        curl_setopt($ch1, CURLOPT_POSTFIELDS, json_encode($dados2));
     
        $response1 = curl_exec($ch1);
        
        if ($err1 = curl_error($ch1)) 
        {
          return false;   
          ECHO 'Erro ' . $err1;
        } 
         else 
        {
        $resposta = json_decode($response1);
        //var_dump($resposta);
        return true;

        }
                
   } 
   
     function alterarEstoqueApi($id,$row1){
        
        $url2= "https://api.awsli.com.br/v1/produto_estoque/" . $id;  
        $ch2 = curl_init($url2);

        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch2, CURLOPT_ENCODING, '');
        curl_setopt($ch2, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch2, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, 'PUT');
        $ChaveAcesso = $this->getChavesAcesso();
        curl_setopt($ch2,CURLOPT_HTTPHEADER,array('Authorization: chave_api ' . $ChaveAcesso . ' aplicacao 5a432d57-8f89-4cc6-b35d-6bfc90934239','Content-Type: application/json'));

        $dados3['gerenciado'] = true;
        $dados3['quantidade'] = intval($row1['qtdDisp']);
        //$dados3['situacao_em_estoque'] = 0; 
        //$dados3['situacao_sem_estoque'] = 0; 
       // $dados3['quantidade'] = trim($row1['qtdDisp']); 
        
        curl_setopt($ch2, CURLOPT_POSTFIELDS, json_encode($dados3));
     
        $response2 = curl_exec($ch2);
        
        if ($err2 = curl_error($ch2)) 
        {
          return false;    
          ECHO 'Erro ' . $err2;
        } 
         else 
        {
          $resposta = json_decode($response2);
          var_dump($resposta);
          return true; 
        }
                
   } 

   
  function inserirProdutosApi() 
   {
       
     $url= "https://api.awsli.com.br/v1/produto/";  
     $ch = curl_init($url);

     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
     curl_setopt($ch, CURLOPT_ENCODING, '');
     curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
     curl_setopt($ch, CURLOPT_TIMEOUT, 0);
     curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
     $ChaveAcesso = $this->getChavesAcesso();
     curl_setopt($ch,CURLOPT_HTTPHEADER,array('Authorization: chave_api ' . $ChaveAcesso . ' aplicacao 5a432d57-8f89-4cc6-b35d-6bfc90934239','Content-Type: application/json'));
 
       
     //pega os produtos que ainda nao estão no e-commerce
     $sql  = "select codigoGrade,descricaoGrade,vendaPrc,compraPrc,grupo,qtdDisp,idCategoria,ncm";
     $sql = $sql . " from produto inner join gradeProd on produto.codigo=gradeProd.codigoGrade";
     $sql = $sql . " inner join filialEstoque on gradeProd.codigoGrade=filialEstoque.prodCodigo";
     $sql = $sql . " where lojaVirtual='1' and idfilial=(select idEmpresa from empresa) and (idtray is null or idtray='')";
     //$sql = $sql . " where codigoGrade='1204' and idfilial=(select idEmpresa from empresa) and (idtray is null or idtray='')"; 
     $ConnConsulta = $this->conectar();
     
     $stmt = $ConnConsulta->prepare($sql);
     $stmt->execute();
   
     //$stmt = sqlsrv_query( $ConnConsulta, $sql );
       
     if ($stmt===false) {
       echo 'Erro';
      }
     $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
     foreach ($results as $row)    
     //while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) 

     {
       $dados['sku']        = trim($row['codigoGrade']);
       $dados['nome']       = trim($row['descricaoGrade']);
       $dados['ncm']        = trim($row['ncm']);
       $dados['tipo']       = "normal";
       $dados['ativo']      = "true";
       $dados['categorias'] = ['/api/v1/categoria/' . trim($row['idCategoria']) . '/'];        
       
     
       curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
     
       $response = curl_exec($ch);
       if ($err = curl_error($ch)) 
       {
         ECHO 'Erro ' . $err;
       } 
       else 
       {
         $resposta = json_decode($response);
         var_dump($resposta);
         $sucesso = $this->alterarPrecoApi($resposta->id,$row);
        
         if ($sucesso)
         {
           $sucesso = $this->alterarEstoqueApi($resposta->id,$row); 
           if ($sucesso)
           {
             //insere o id do produto produto
             $sql  = "UPDATE produto SET idTray='" . $resposta->id . "'";
             $sql = $sql . " WHERE codigo='" . trim($row['codigoGrade']) . "'";
             //$stmt1 = sqlsrv_query( $ConnConsulta, $sql );
             $stmt1 = $ConnConsulta->prepare($sql);
             $stmt1->execute();
             if ($stmt1===false) {
               echo 'Erro';
             } 
             else 
             {
               //insere uma entrada em comparaEstoqueSinc
               $sql  = "insert into comparaEstoqueSinc (prodCodigo,idTray,idFilial,qtdDisp,name,brand,price,dataEnvio,prccompra)";
               $sql = $sql . " select codigoGrade,idTray,idfilial,qtdDisp,descricaoGrade,grupo,vendaPrc,GETDATE(),compraPrc";
               $sql = $sql . " from produto inner join gradeProd on produto.codigo=gradeProd.codigoGrade";             
               $sql = $sql . " inner join filialEstoque on gradeProd.codigoGrade=filialEstoque.prodCodigo";                    
               $sql = $sql . " where idFilial=(select idEmpresa from empresa)";   
               $sql = $sql . " and codigoGrade='" . trim($row['codigoGrade']) . "'";
               //$stmt2 = sqlsrv_query( $ConnConsulta, $sql );
               $stmt2 = $ConnConsulta->prepare($sql);
               $stmt2->execute();               
               if ($stmt2===false) {
                  echo 'Erro';
               } 
             }            
           }
         }
       } 
     }
        //sqlsrv_free_stmt($stmt);
        //sqlsrv_close($ConnConsulta);
        $ConnConsulta = null;
   }
   
    function atualizarProdutosApi() 
   {
              
     //pega os produtos que ainda nao estão no e-commerce
     $sql  = "select codigoGrade,descricaoGrade,vendaPrc,compraPrc,grupo,filialEstoque.qtdDisp as qtdDisp,idCategoria,ncm,produto.idTray as idTray";
     $sql = $sql . " from produto inner join gradeProd on produto.codigo=gradeProd.codigoGrade";
     $sql = $sql . " inner join filialEstoque on gradeProd.codigoGrade=filialEstoque.prodCodigo";
     $sql = $sql . " inner join comparaEstoqueSinc on comparaEstoqueSinc.prodCodigo=gradeProd.codigoGrade";
     $sql = $sql . " where ((filialEstoque.qtdDisp <> comparaEstoqueSinc.qtdDisp) OR (gradeProd.descricaoGrade <> comparaEstoqueSinc.name)";
     $sql = $sql . " OR (produto.grupo <> comparaEstoqueSinc.brand) OR (produto.vendaPrc <> comparaEstoqueSinc.price) OR (produto.compraPrc <> comparaEstoqueSinc.prccompra))";
     $sql = $sql . " and filialEstoque.idfilial=(select idEmpresa from empresa) ";

     $ConnConsulta = $this->conectar();
     
     $stmt = $ConnConsulta->prepare($sql);
     $stmt->execute();
     //$stmt = sqlsrv_query( $ConnConsulta, $sql );
       
     if ($stmt===false) {
       echo 'Erro';
      }

     $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
     foreach ($results as $row)    
     //while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) 

     {

       $produto = $this->getProdutosEspecificoApi($row['idTray']);
       //ECHO "Descricao: " . $produto->nome . "<BR>";

       $url= "https://api.awsli.com.br/v1/produto/" . $row['idTray'];  
       $ch = curl_init($url);

       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
       curl_setopt($ch, CURLOPT_ENCODING, '');
       curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
       curl_setopt($ch, CURLOPT_TIMEOUT, 0);
       curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
       curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

       $ChaveAcesso = $this->getChavesAcesso();
       curl_setopt($ch,CURLOPT_HTTPHEADER,array('Authorization: chave_api ' . $ChaveAcesso . ' aplicacao 5a432d57-8f89-4cc6-b35d-6bfc90934239','Content-Type: application/json'));
         
       $dados['altura']             = $produto->altura;
       $dados['apelido']            = $produto->apelido;   
       $dados['ativo']              = $produto->ativo;
       $dados['bloqueado']          = $produto->bloqueado; 
       $dados['categorias']         = ['/api/v1/categoria/' . trim($row['idCategoria']) . '/'];  
       $dados['data_criacao']       = $produto->data_criacao;
       $dados['data_modificacao']   = date("Y-m-d H:i:s");       
       $dados['destaque']           = $produto->destaque;
       $dados['imagem_principal']   = $produto->imagem_principal;                
       $dados['imagens']            = $produto->imagens;
       $dados['largura']            = $produto->largura; 
       $dados['marca']              = $produto->marca;               
       $dados['nome']               = $row['descricaoGrade'];
       $dados['pai']                = $produto->pai;              
       $dados['peso']               = $produto->peso;               
       $dados['profundidade']       = $produto->profundidade;
       $dados['ncm']                = $produto->ncm;
       $dados['gtin']               = $produto->gtin;                
       $dados['mpn']                = $produto->mpn;
       $dados['removido']           = $produto->removido;    
       $dados['sku']                = $produto->sku;  
       $dados['tipo']               = $produto->tipo;
       $dados['url_video_youtube']  = $produto->url_video_youtube;
       $dados['usado']              = $produto->usado;


       curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
     
       $response = curl_exec($ch);
       if ($err = curl_error($ch)) 
       {
         ECHO 'Erro ' . $err;
       } 
       else 
       {
         $resposta = json_decode($response);
         //var_dump($resposta);
 
         $sucesso = $this->alterarPrecoApi($resposta->id,$row);
        
         if ($sucesso)
        {
           $sucesso = $this->alterarEstoqueApi($resposta->id,$row); 
           if ($sucesso)
           {
             //atualiza comparaEstoqueSinc
            date_default_timezone_set('America/Sao_Paulo');
             $sql  = "update comparaEstoqueSinc set name='" . trim($row['descricaoGrade']). "',";
             $sql  = $sql . " brand='" . trim($row['grupo']) . "',";
             $sql  = $sql . " price=" . trim($row['vendaPrc']) . ",";
             $sql  = $sql . " prccompra=" . trim($row['compraPrc']) . ","; 
             $sql  = $sql . " qtdDisp=" . trim($row['qtdDisp']) . ",";  
             $sql  = $sql . " dataEnvio='" . date("Y-m-d H:i:s") . "'";  
             $sql  = $sql . " where prodCodigo='" . trim($row['codigoGrade']) . "'";
             $stmt1 = $ConnConsulta->prepare($sql);
             $stmt1->execute();
             //$stmt1 = sqlsrv_query( $ConnConsulta, $sql );
             if ($stmt1===false) {
               echo 'Erro';
             } 
           }
         }
      } 
     }
       $ConnConsulta = null;
        //sqlsrv_free_stmt($stmt);
        //sqlsrv_close($ConnConsulta);  
   }     

}

