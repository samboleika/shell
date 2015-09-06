<?php
namespace app\components; 

class ShellHelper extends \yii\base\Object {
    static public function shortString($str, $lnght, $more = false) {
        $short =  substr($str, 0, $lnght);
        $arr = explode(" ", $short);
        if(count($arr) > 1 && strlen($str) > $lnght){
            array_pop($arr); 
            return ($more)?implode(" ", $arr) . " ...":implode(" ", $arr); 
        }
        else{
            return $short;
        }
    } 
    
    static public function validateText($value){
        $value = htmlspecialchars_decode($value, ENT_QUOTES | ENT_HTML5);
        $value = strip_tags($value);
        $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5);
        return $value;
    }
    
    static public function exportCsv($data) {
        header("content-type:application/csv;charset=UTF-8");
        header("Content-Disposition: attachment; filename=export.csv");
        
        // Disable caching
        header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
        header("Pragma: no-cache"); // HTTP 1.0
        header("Expires: 0"); // Proxies
        print "\xEF\xBB\xBF"; // utf-8 BOM
        $output = fopen("php://output", "w");
        foreach ($data as $row) {
            fputcsv($output, $row, ";");
        }
        fclose($output); 
    }
}