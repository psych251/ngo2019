<?php
/** 
 * Shawn T. Schwartz, 2021
 * <stschwartz@stanford.edu>
 *
 * batch download and merge csv files from $directory in `/data/` dir
 * built with jsPsych in mind, but could be used for any data dir with csv files
 */

ob_start();  

/** Inspiration and code adapted from:
 * 7design.studio
 * Merge multiple CSV files into one master CSV file 
 * Remove header line from individual file
 */

$directory = "ngo-data/*"; // CSV Files Directory Path

// Open and Write Master CSV File
$file_name='AllData_'.date('m-d-Y_hia').'.csv';
$masterCSVFile = fopen($file_name, "w+");

// Process each CSV file inside root directory
$counter = 1;
foreach(glob($directory) as $file) {

    $data = []; // Empty Data

    // Allow only CSV files
    if (strpos($file, '.csv') !== false) {

        // Open and Read individual CSV file
        if (($handle = fopen($file, 'r')) !== false) {
            // Collect CSV each row records
            while (($dataValue = fgetcsv($handle, 1000)) !== false) {
                $data[] = $dataValue;
            }
        }

        fclose($handle); // Close individual CSV file 
        
        if($counter > 1) { // Only remove subsequent header rows (but not the first top-level header row)
            unset($data[0]); // Remove first row of CSV, commonly tends to CSV header    
        }

        // Check whether record present or not
        if(count($data) > 0) {

            foreach ($data as $value) {
                try {
                   // Insert record into master CSV file
                   fputcsv($masterCSVFile, $value, ", ", "'");
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            
            }

        } else {
            echo "[$file] file contains no record to process.";
        }

    } else {
        echo "[$file] is not a CSV file.";
    }
    $counter++;
}

// Close master CSV file 
fclose($masterCSVFile);
header("Content-Type: application/force-download");
        header("Content-type: application/csv");
        header('Content-Description: File Download');
        header('Content-Disposition: attachment; filename=' . $file_name);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-length: ' . filesize($file_name));
        ob_clean();
        flush();
        readfile($file_name);
        unlink($file_name);

?>