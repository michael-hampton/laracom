<?php

namespace App\Shop\Tools;

use Illuminate\Http\UploadedFile;

trait CsvTrait {

    /**
     * 
     * @param type $filename
     * @param type $delimiter
     * @return boolean
     */
    private function csv_to_array($filename = '', $delimiter = ',') {
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }

        $header = null;
        $data = array();

        if (($handle = fopen($filename, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }
        return $data;
    }

    private function exportToCSV($pathToGenerate, $arrData) {
        
        $createFile = fopen($pathToGenerate, 'w');
        $header = false;

        foreach ($arrData as $row) {

            if (!$header) {
                fputcsv($createFile, array_keys($row));
                $header = true;
            }

            fputcsv($createFile, $row);   // write the data for all rows
        }

        fclose($createFile);
        
        return true;
    }

}
