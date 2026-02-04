<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('/login')->with('error', 'You must be logged in to access this page.');
        }

        if ($arguments) {
            $user = auth()->user();
            
            foreach ($arguments as $role) {
                if ($user->inGroup($role)) {
                    return;
                }
            }

            return redirect()->back()->with('error', 'You do not have permission to access this resource.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
