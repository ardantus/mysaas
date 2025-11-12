-- Create additional databases for tenant stores if needed
-- This script runs on MySQL initialization

-- Grant privileges to mysaas_user
GRANT ALL PRIVILEGES ON *.* TO 'mysaas_user'@'%' WITH GRANT OPTION;
FLUSH PRIVILEGES;

-- Create a sample tenant database (optional, for testing)
-- CREATE DATABASE IF NOT EXISTS tenant_sample_store;
