<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\TenantService;

class TenantMiddleware
{
    protected TenantService $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        
        // Get store by domain or subdomain
        $store = $this->tenantService->getStoreByDomain($host);

        if (!$store) {
            return response()->json([
                'error' => 'Store not found'
            ], 404);
        }

        // Switch to tenant database
        $this->tenantService->switchToTenant($store);

        // Store the current store in the request
        $request->attributes->set('store', $store);

        return $next($request);
    }
}

