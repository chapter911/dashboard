<?php
namespace App\Controllers;

use CodeIgniter\Controller;

class LogViewer extends Controller
{
    public function index()
    {
        $logDir = WRITEPATH . 'logs';
        $files = array_diff(scandir($logDir), ['.', '..', 'index.html']);
        return view('log_viewer/index', ['files' => $files]);
    }

    public function show($filename)
    {
        $logDir = WRITEPATH . 'logs';
        $filePath = $logDir . DIRECTORY_SEPARATOR . $filename;
        if (!file_exists($filePath)) {
            return 'File not found.';
        }
        $content = file_get_contents($filePath);
        return view('log_viewer/show', ['filename' => $filename, 'content' => $content]);
    }
}
