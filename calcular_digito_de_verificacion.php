function getDV($nit){
      // reversar al NIT
      $nit = strrev($nit); 
      // array de numeros primos 
      $vpri = [3,7,13,17,19,23,29,37,41,43,47,53,59,67,71];
      // length del nit
      $z = strlen($nit) ;
      $x = 0 ;
      // iterar digitos del NIT
      for ($i=0; $i < $z; $i++) { 
        // valor del digito en iteracion 
        $y =  substr($nit, $i, 1);
        // factor multiplicador: index en array de primos
        $n =  $vpri[$i];
        // ir sumando
        $x += ( $y * $n) ;
      }     
      // residuo de 11 sobre la sumatoria ($x)   
      $r = $x % 11;
      // si el resultado es mayor a 1, restar 11 menos resultado, 
      // de lo contrario $dv = $r
      $dv = ( $r > 1 ) ? 11 - $r : $r; 
      return $dv;
  }
