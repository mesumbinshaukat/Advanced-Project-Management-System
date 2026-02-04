<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!auth()->loggedIn()) {
            if ($request->isAJAX()) {
                return service('response')->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
            }
            return redirect()->to('/login')->with('error', 'You must be logged in to access this page.');
        }

        if ($arguments) {
            $user = auth()->user();
            
            foreach ($arguments as $permission) {
                if ($user->can($permission)) {
                    return;
                }
            }

            if ($request->isAJAX()) {
                return service('response')->setJSON(['error' => 'Forbidden'])->setStatusCode(403);
            }
            
            return redirect()->back()->with('error', 'You do not have permission to perform this action.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
