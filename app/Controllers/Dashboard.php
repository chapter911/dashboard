<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index(): string
    {
        return view('dashboard/index', [
            'user' => [
                'username' => (string) session('username'),
                'nama' => (string) session('nama'),
                'group_id' => (string) session('group_id'),
                'unit_id' => (string) session('unit_id'),
            ],
        ]);
    }
}
