<?php

namespace App\Services;

use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class TenantService
{
    /**
     * Create a new store with its own database
     */
    public function createStore(array $data): Store
    {
        // Generate unique identifiers
        $data['slug'] = Str::slug($data['name']);
        $data['subdomain'] = $data['subdomain'] ?? $data['slug'];
        $data['database_name'] = 'tenant_' . $data['slug'] . '_' . time();

        // Create store in main database
        $store = Store::create($data);

        // Create tenant database
        $this->createTenantDatabase($store->database_name);

        // Run migrations on tenant database
        $this->migrateTenantDatabase($store->database_name);

        return $store;
    }

    /**
     * Create tenant database
     */
    protected function createTenantDatabase(string $databaseName): void
    {
        $connection = Config::get('database.default');
        $charset = Config::get("database.connections.{$connection}.charset", 'utf8mb4');
        $collation = Config::get("database.connections.{$connection}.collation", 'utf8mb4_unicode_ci');

        DB::statement("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET {$charset} COLLATE {$collation}");
    }

    /**
     * Run migrations on tenant database
     */
    protected function migrateTenantDatabase(string $databaseName): void
    {
        // Configure tenant database connection
        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => Config::get('database.connections.mysql.host'),
            'port' => Config::get('database.connections.mysql.port'),
            'database' => $databaseName,
            'username' => Config::get('database.connections.mysql.username'),
            'password' => Config::get('database.connections.mysql.password'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ]);

        // Run migrations
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);
    }

    /**
     * Switch to tenant database
     */
    public function switchToTenant(Store $store): void
    {
        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => Config::get('database.connections.mysql.host'),
            'port' => Config::get('database.connections.mysql.port'),
            'database' => $store->database_name,
            'username' => Config::get('database.connections.mysql.username'),
            'password' => Config::get('database.connections.mysql.password'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');
        
        // Set tenant as default connection for models
        Config::set('database.default', 'tenant');
    }

    /**
     * Get store by domain or subdomain
     */
    public function getStoreByDomain(string $host): ?Store
    {
        return Store::where('domain', $host)
            ->orWhere(function ($query) use ($host) {
                $subdomain = explode('.', $host)[0];
                $query->where('subdomain', $subdomain);
            })
            ->where('is_active', true)
            ->first();
    }

    /**
     * Delete tenant and its database
     */
    public function deleteStore(Store $store): bool
    {
        try {
            // Drop tenant database
            DB::statement("DROP DATABASE IF EXISTS `{$store->database_name}`");
            
            // Delete store record
            $store->delete();
            
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
