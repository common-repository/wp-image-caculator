<?php

namespace Wp_Image_Calc;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Wp_Image_Calc_Process {

    private static $instance;

    public static function initialize() {

        if ( ! isset( self::$instance ) ) {
            self::$instance = new Wp_Image_Calc_Process();
            self::$instance->init();
        }

        return self::$instance;
    }

    private function init() {

        add_action( 'wp_ajax_process_image_calc', array( $this, 'initialize_process' ) );
        add_action( 'wp_ajax_nopriv_process_image_calc', array( $this, 'initialize_process' ) );
    }

    public function initialize_process() {

        $upload_dir   	= wp_upload_dir();
        $uploads 		= $upload_dir['basedir'];

        if ( $this->is_writable( $uploads ) ) {

            $process = new Process(['ls', $uploads]);
            $process->run();

            // executes after the command finishes
            if (! $process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            $process_resp = $process->getOutput();
            $separator = "\r\n";
            $line = strtok($process_resp, $separator);
            $collection = array();
            $total_images = 0;

            while ($line !== false) {

                if ( is_dir( $uploads . '/' . $line ) && (int) $line > 0 ) {
                    
                    $collection[ $line ] = array();
                }
                else {
                    if( ! isset($collection['root']) ) {
                        $collection['root'] = array();
                    }

                    if ( $this->isFile( $line ) ) {
                        array_push($collection['root'], $line);
                    }
                }

                $line = strtok( $separator );
            }

            if ( count( $collection ) ) {
                foreach( $collection as $key => $value ) {

                    // Skip in case of root
                    if ( $key == 'root' ) {
                        continue;
                    }

                    if ( is_dir( $uploads . '/' . $key ) ) {

                        $child_process_resp = $this->handle_child_dir( $uploads . '/' . $key );
                        $child_line = strtok($child_process_resp, $separator);
                        $child_collection = array();
                        
                        while ($child_line !== false) {
                            $path = $uploads . '/' . $key . '/' . $child_line;

                            if ( is_dir( $path ) && (int) $child_line > 0 ) {

                                $process = Process::fromShellCommandline('ls ' . $path . ' | wc -l');
                                $process->run();

                                // executes after the command finishes
                                if (! $process->isSuccessful()) {
                                    throw new ProcessFailedException($process);
                                }

                                $total_files = $process->getOutput();
                                $total_files = (int) $total_files;

                                if ( $total_files > 0 ) {
                                    $child_collection[ (int) $child_line ] = $total_files;
                                    $total_images += $total_files;
                                }
                            }

                            $child_line = strtok($separator);
                        }

                        if ( count( $child_collection ) ) {
                            array_push( $collection[$key], $child_collection );
                        }
                    }
                }
            }

            if ( isset( $collection['root'] ) && count($collection['root']) ) {
                $total_images += count($collection['root']);
            }

            wp_send_json( array(
                'response' => $collection,
                'total' => $total_images
            ));
        }
        
        wp_send_json(array(
            'message' => 'You don\'t have permission to check the directory ' . $uploads
        ), 401);
    }

    private function is_writable( $dir ) {

        return is_writable( $dir ) ? true : false;
    }

    private function isFile($file) {
        $f = pathinfo($file, PATHINFO_EXTENSION);
        return (strlen($f) > 0) ? true : false;
    }

    private function handle_child_dir( $path ) {

        $process = new Process(['ls', $path]);
        $process->run();

        // executes after the command finishes
        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }
}

Wp_Image_Calc_Process::initialize();