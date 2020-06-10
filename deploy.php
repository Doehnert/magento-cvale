<?php
namespace Deployer;
 
require 'recipe/magento2.php';

// Project name
set('application', 'MarketPlace');
 
// Project repository
set('repository', 'https://github.com/Doehnert/magento-cvale.git');

// Hosts
inventory('hosts.yml');