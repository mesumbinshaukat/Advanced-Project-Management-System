<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ClientModel;

class ClientsController extends BaseController
{
    public function index()
    {
        $clientModel = new ClientModel();
        $clients = $clientModel->findAll();
        
        $data = [
            'title' => 'Clients',
            'clients' => $clients,
        ];

        return view('clients/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Create Client',
        ];

        return view('clients/create', $data);
    }

    public function edit($id)
    {
        $clientModel = new ClientModel();
        $client = $clientModel->find($id);
        
        if (!$client) {
            return redirect()->to('/clients')->with('error', 'Client not found');
        }
        
        $data = [
            'title' => 'Edit Client',
            'client' => $client,
        ];
        
        return view('clients/edit', $data);
    }

    public function delete($id)
    {
        $clientModel = new ClientModel();
        $client = $clientModel->find($id);

        if (!$client) {
            return redirect()->to('/clients')->with('error', 'Client not found');
        }

        $clientModel->delete($id);

        return redirect()->to('/clients')->with('success', 'Client deleted successfully');
    }
}